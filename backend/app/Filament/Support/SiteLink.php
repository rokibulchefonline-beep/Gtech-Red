<?php

namespace App\Filament\Support;

/** Address of a page on the public website: this app once the Blade site is live, the Next.js site before. */
class SiteLink
{
    public static function to(string $path = '/'): string
    {
        $base = config('gtech.blade_live') ? url('/') : (string) config('gtech.site_url');
        return rtrim($base, '/').'/'.ltrim($path, '/');
    }
}
