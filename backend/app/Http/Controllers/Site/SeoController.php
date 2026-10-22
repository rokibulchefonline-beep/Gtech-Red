<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\SeoEntry;
use App\Support\Site\Repo;
use Illuminate\Http\Response;

/** sitemap.xml and robots.txt (app/sitemap.ts and app/robots.ts on the website). */
class SeoController extends Controller
{
    public function robots(): Response
    {
        // Until the Blade pages replace the website, keep crawlers off this copy of it. Then SEO > Search engines.
        $body = config('gtech.blade_live') ? \App\Support\Site\Crawl::robotsTxt() : "User-Agent: *\nDisallow: /\n";
        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /** /llms.txt: a plain summary of the site and its main pages for AI assistants (llmstxt.org). */
    public function llms(): Response
    {
        $base = rtrim((string) config('gtech.public_url'), '/');
        $s = \App\Models\Setting::all_();
        $c = \App\Support\Site\Contact::get();
        $link = fn (string $title, string $path, string $note = '') => "- [$title]($base$path)".($note !== '' ? ": $note" : '');
        $out = ['# '.($s['general']['siteName'] ?? 'GTech Digital'), '',
            '> '.($s['seo']['defaultDescription'] ?: 'GTech Digital is a UK digital agency providing digital marketing, SEO, Google Ads, social media marketing, web design and development, custom software development and branding services.'), '',
            'Contact: '.implode(' · ', array_filter([$c['email'], ...array_column($c['phones'], 'label'), $c['address'] ? implode(', ', $c['address']) : 'Serving businesses across the UK'])), '',
            '## Services', ''];
        foreach (Repo::groups() as $g) {
            $out[] = $link($g->title, "/services/{$g->slug}", (string) $g->intro);
            foreach ($g->items as $it) $out[] = '  '.$link($it->name, "/services/{$it->slug}", (string) $it->blurb);
        }
        $out = [...$out, '', '## Industries', ''];
        foreach (Repo::industries() as $i) $out[] = $link($i->name, "/industries/{$i->slug}");
        $out = [...$out, '', '## Case studies', ''];
        foreach (Repo::caseStudies(20) as $cs) $out[] = $link($cs->title, "/case-studies/{$cs->slug}", (string) $cs->excerpt);
        $out = [...$out, '', '## Latest articles', ''];
        foreach (Repo::posts()->take(20) as $p) if (! $p->noindex) $out[] = $link($p->title, "/blog/{$p->slug}", (string) $p->excerpt);
        $out = [...$out, '', '## Company', '', $link('About us', '/about'), $link('Contact', '/contact'), $link('Privacy policy', '/privacy-policy'), ''];
        return response(preg_replace('/[ \t]+$/m', '', implode("\n", $out)), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(): Response
    {
        $base = config('gtech.public_url');
        $noindex = SeoEntry::query()->where('noindex', true)->pluck('path')->all();
        $pages = Page::query()->get()->keyBy('path');
        $urls = [];
        $C = \App\Support\Site\Crawl::class;
        $s = $C::settings();
        $exclude = array_map(fn ($p) => rtrim(preg_replace('#^https?://[^/]+#', '', $p), '/') ?: '', $C::lines('sitemapExclude'));
        $add = function (string $path, string $freq, string $priority, ?string $lastmod = null, string $section = 'main') use (&$urls, $noindex, $pages, $exclude, $C) {
            if (! $C::inSitemap($section) || in_array($path, $noindex, true)) return;
            // Excluded addresses; a line ending in * excludes everything under it.
            foreach ($exclude as $x) if ($x === $path || ($x === '' && $path === '') || (str_ends_with($x, '*') && str_starts_with($path, rtrim($x, '*')))) return;
            $p = $pages[$path === '' ? '/' : $path] ?? null;
            if ($p && ! $p->published) return;
            $urls[] = [$path, $lastmod ?? $p?->updated_at?->format('Y-m-d'), $freq, $priority];
        };
        foreach (['', '/services', '/about', '/contact', '/free-audit', '/case-studies', '/industries', '/blog', '/privacy-policy', '/terms', '/cookie-policy'] as $p) $add($p, 'monthly', $p === '' ? '1' : '0.7');
        foreach ($pages->where('kind', 'service')->sortBy('sort') as $p) $add($p->path, 'monthly', '0.8', null, 'services');
        foreach ($pages->where('kind', 'industry')->sortBy('sort') as $p) $add($p->path, 'monthly', '0.7', null, 'industries');
        foreach ($pages->where('kind', 'landing')->sortBy('name') as $p) if (empty(((array) $p->data)['noindex'])) $add($p->path, 'monthly', '0.6', null, 'landing');
        foreach (Repo::posts() as $p) if (! $p->noindex) $add("/blog/{$p->slug}", 'yearly', '0.6', ($p->date ?? $p->created_at)?->format('Y-m-d'), 'posts');
        foreach (Repo::authors() as $a) if (Repo::posts()->contains('author', $a->name)) $add($a->path(), 'monthly', '0.4', $a->updated_at?->format('Y-m-d'), 'authors');
        foreach (Repo::caseStudies(200) as $c) $add("/case-studies/{$c->slug}", 'yearly', '0.5', $c->updated_at?->format('Y-m-d'), 'cases');

        // Extra addresses added in the admin (site paths or full addresses).
        foreach ($C::lines('sitemapExtra') as $x) $urls[] = [str_starts_with($x, 'http') ? $x : '/'.ltrim($x, '/'), null, 'monthly', '0.5'];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as [$path, $lastmod, $freq, $priority]) {
            $loc = str_starts_with($path, 'http') ? $path : $base.$path;
            $xml .= "<url>\n<loc>".e($loc)."</loc>\n".($lastmod && ! empty($s['sitemapLastmod']) ? "<lastmod>$lastmod</lastmod>\n" : '')
                .(! empty($s['sitemapPriority']) ? "<changefreq>$freq</changefreq>\n<priority>$priority</priority>\n" : '')."</url>\n";
        }
        return response($xml.'</urlset>'."\n", 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
