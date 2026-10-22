<?php

namespace App\Support\Site;

use App\Models\Setting;

/**
 * SEO > Search engines: whether this copy of the site may be indexed, robots.txt, the default robots meta tag and
 * what goes in the sitemap. Values come from the admin (setting "crawl"); SITE_INDEXING in .env wins over the admin.
 */
class Crawl
{
    public const SITEMAP_SECTIONS = ['main' => 'Main pages (home, about, contact, services, blog...)', 'services' => 'Service pages', 'industries' => 'Industry pages',
        'landing' => 'Landing pages', 'posts' => 'Blog posts', 'authors' => 'Author pages', 'cases' => 'Case studies'];

    public static function settings(): array
    {
        // Read once per request (kept on the request, so a long-running worker never reuses old settings).
        $r = request();
        if (! $r->attributes->has('crawl')) $r->attributes->set('crawl', rescue(fn () => Setting::group('crawl'), Setting::DEFAULTS['crawl'], false));

        return $r->attributes->get('crawl');
    }

    /** on, off or auto (only the live domains). */
    public static function mode(): string
    {
        $env = strtolower((string) config('gtech.indexing.mode'));
        if (in_array($env, ['on', 'true', '1'], true)) return 'on';
        if (in_array($env, ['off', 'false', '0'], true)) return 'off';
        $m = (string) (self::settings()['indexing'] ?? 'auto');

        return in_array($m, ['on', 'off'], true) ? $m : 'auto';
    }

    /** @return string[] */
    public static function hosts(): array
    {
        $admin = array_filter(array_map(fn ($h) => strtolower(trim(preg_replace('#^https?://|/.*$#', '', trim($h)))), preg_split('/[\s,]+/', (string) (self::settings()['hosts'] ?? ''))));

        return array_values(array_unique([...$admin, ...(array) config('gtech.indexing.hosts')]));
    }

    public static function indexable(string $host): bool
    {
        return match (self::mode()) { 'on' => true, 'off' => false, default => in_array(strtolower($host), self::hosts(), true) };
    }

    public static function defaultRobotsTxt(): string
    {
        $base = rtrim((string) config('gtech.public_url'), '/');

        return "User-agent: *\nAllow: /\nAllow: /api/media/\nDisallow: /api/\nDisallow: /admin/\n\nSitemap: $base/sitemap.xml\n";
    }

    /** The admin's robots.txt ({sitemap} becomes the sitemap address), else the default. */
    public static function robotsTxt(): string
    {
        $custom = trim(str_replace("\r", '', (string) (self::settings()['robotsTxt'] ?? '')));
        if ($custom === '') return self::defaultRobotsTxt();

        return str_replace('{sitemap}', rtrim((string) config('gtech.public_url'), '/').'/sitemap.xml', $custom)."\n";
    }

    /** The robots meta tag for a page. Empty when it says nothing beyond the search engines' default. */
    public static function meta(bool $noindex, string $path = ''): string
    {
        $s = self::settings();
        $noindex = $noindex || ! self::indexable(request()->getHost())
            || (! empty($s['noindexAuthors']) && str_starts_with($path, '/blog/author/'))
            || (! empty($s['noindexCaseStudies']) && str_starts_with($path, '/case-studies/'))
            || (! empty($s['noindexBlogPages']) && str_starts_with($path, '/blog') && (int) request()->query('page') > 1);
        if ($noindex) return 'noindex, follow';
        $parts = [(string) ($s['metaIndex'] ?? 'index, follow')];
        if (($s['maxSnippet'] ?? '') !== '') $parts[] = 'max-snippet:'.$s['maxSnippet'];
        if (($s['maxImagePreview'] ?? '') !== '') $parts[] = 'max-image-preview:'.$s['maxImagePreview'];
        if (($s['maxVideoPreview'] ?? '') !== '') $parts[] = 'max-video-preview:'.$s['maxVideoPreview'];
        if (! empty($s['noarchive'])) $parts[] = 'noarchive';
        if (! empty($s['notranslate'])) $parts[] = 'notranslate';
        $tag = implode(', ', array_filter($parts));

        return $tag === 'index, follow' ? '' : $tag;
    }

    public static function inSitemap(string $section): bool
    {
        return in_array($section, (array) (self::settings()['sitemapSections'] ?? []), true);
    }

    /** Lines of a textarea as site paths ("/x"), full addresses kept as they are. @return string[] */
    public static function lines(string $key): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) (self::settings()[$key] ?? '')))));
    }
}
