<?php

namespace App\Support\Site;

use App\Models\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Preview of unsaved page changes. The editor's form is turned into a page row, kept for two hours under a
 * random token, and rendered by the public templates without touching the stored page.
 */
class PagePreview
{
    public static function store(Page $page, array $row): string
    {
        $token = Str::random(40);
        Cache::put("page-preview:$token", ['key' => $page->key, 'row' => $row, 'user' => auth()->id()], now()->addHours(2));
        return $token;
    }

    public static function url(string $token): string
    {
        return url("/preview/page/$token");
    }

    /** The page as it would look with the previewed changes, or null when the preview has expired. */
    public static function page(string $token): ?Page
    {
        $p = Cache::get("page-preview:$token");
        if (! $p || ! ($page = Page::query()->find($p['key']))) return null;
        $copy = clone $page;
        $copy->forceFill(array_intersect_key($p['row'], array_flip(Page::DRAFTABLE)));
        $copy->updated_at = now();
        return $copy;
    }
}
