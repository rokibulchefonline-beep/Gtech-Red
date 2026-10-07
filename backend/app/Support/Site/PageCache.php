<?php

namespace App\Support\Site;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;

/**
 * Full-page cache for the public site. Pages are stored as finished HTML, keyed by address and a site-wide
 * version number. Saving or deleting any content (pages, menus, posts, case studies, settings, SEO...) bumps
 * the version, so every page is rebuilt on its next visit: edits in the panel show straight away.
 */
class PageCache
{
    /** Models whose changes can alter what a public page shows. */
    public const MODELS = [
        \App\Models\Page::class, \App\Models\ServiceGroup::class, \App\Models\ServiceItem::class, \App\Models\Industry::class,
        \App\Models\CoreService::class, \App\Models\Stat::class, \App\Models\Testimonial::class, \App\Models\SeoKeyword::class,
        \App\Models\Post::class, \App\Models\Category::class, \App\Models\CaseStudy::class, \App\Models\Partner::class,
        \App\Models\Client::class, \App\Models\SeoEntry::class, \App\Models\Setting::class, \App\Models\Author::class,
    ];

    public static function store(): \Illuminate\Contracts\Cache\Repository
    {
        return Cache::store(config('gtech.page_cache.store') ?: null);
    }

    public static function enabled(): bool
    {
        return (bool) config('gtech.page_cache.enabled');
    }

    public static function version(): int
    {
        // Starts from the clock, so a lost version key can never bring back pages cached under an older one.
        return (int) self::store()->rememberForever('site-page:version', fn () => time());
    }

    /** Called whenever content changes. Old entries are never read again and expire on their own. */
    public static function flush(): void
    {
        $s = self::store();
        $s->forever('site-page:version', self::version() + 1);
    }

    public static function key(string $url): string
    {
        return 'site-page:'.self::version().':'.sha1($url);
    }

    /**
     * How long a page may be kept: the configured time, but never past the moment the next scheduled blog post
     * goes live, or a page scheduled in the panel is published (that changes the blog list, the post page and the sitemap without anyone saving anything).
     */
    public static function ttl(): int
    {
        $ttl = (int) config('gtech.page_cache.ttl');
        $next = self::store()->remember('site-page:next-scheduled:'.self::version(), 300, fn () => collect([
            Post::query()->where('status', 'scheduled')->where('date', '>', now())->min('date'),
            \App\Models\Page::query()->where('publish_at', '>', now())->min('publish_at'),
        ])->filter()->min());
        if ($next) $ttl = min($ttl, max(1, now()->diffInSeconds(\Illuminate\Support\Carbon::parse($next), false)));
        return max(1, (int) $ttl);
    }
}
