<?php

namespace App\Support\Site;

use App\Models\Media;
use Illuminate\Support\Facades\Cache;

/**
 * Gives every image on a public page its real width and height when the template or the editor left them out, so
 * the browser reserves the space and the layout does not jump while images load (Core Web Vitals: CLS). Works for
 * files in public/, uploads in storage and the media library; images on other websites are left alone. Sizes are
 * read once per file and remembered, and the finished page is cached anyway.
 */
class ImageDims
{
    public static function add(string $html): string
    {
        if (! str_contains($html, '<img')) return $html;
        return preg_replace_callback('/<img\b[^>]*>/i', function ($m) {
            $tag = $m[0];
            if (preg_match('/\swidth\s*=/i', $tag) || ! preg_match('/\ssrc\s*=\s*"([^"]+)"/i', $tag, $src)) return $tag;
            $size = self::size(html_entity_decode($src[1]));
            if (! $size) return $tag;
            return preg_replace('/^<img\b/i', '<img width="'.$size[0].'" height="'.$size[1].'"', $tag);
        }, $html);
    }

    /** [width, height] of a local image, or null. */
    public static function size(string $src): ?array
    {
        $path = self::file($src);
        if (! $path || ! is_file($path)) return null;
        return Cache::rememberForever('img-size:'.sha1($path.'|'.filemtime($path)), function () use ($path) {
            if (str_ends_with(strtolower($path), '.svg')) return self::svgSize((string) file_get_contents($path));
            $s = @getimagesize($path);
            return $s && $s[0] > 0 && $s[1] > 0 ? [$s[0], $s[1]] : null;
        });
    }

    private static function file(string $src): ?string
    {
        $host = parse_url((string) config('gtech.public_url'), PHP_URL_HOST);
        if (preg_match('#^https?://([^/]+)(/[^?\#]*)#i', $src, $u)) {
            if (! $host || preg_replace('/^www\./', '', strtolower($u[1])) !== preg_replace('/^www\./', '', strtolower($host))) {
                if (strtolower($u[1]) !== strtolower((string) request()->getHttpHost())) return null;
            }
            $src = $u[2];
        }
        $src = preg_replace('/[?#].*$/', '', $src);
        if (! str_starts_with($src, '/') || str_contains($src, '..')) return null;
        if (preg_match('#^/api/media/([A-Za-z0-9]+)$#', $src, $id)) {
            $m = Media::query()->where('legacy_id', $id[1])->when(ctype_digit($id[1]), fn ($q) => $q->orWhere('id', (int) $id[1]))->first();
            return $m ? storage_path('app/public/'.$m->path) : null;
        }
        if (str_starts_with($src, '/storage/')) return storage_path('app/public/'.substr(rawurldecode($src), 9));
        return public_path(rawurldecode(ltrim($src, '/')));
    }

    private static function svgSize(string $svg): ?array
    {
        if (preg_match('/<svg\b[^>]*>/i', $svg, $tag)) {
            $w = preg_match('/\swidth="([\d.]+)(px)?"/i', $tag[0], $a) ? (float) $a[1] : 0;
            $h = preg_match('/\sheight="([\d.]+)(px)?"/i', $tag[0], $b) ? (float) $b[1] : 0;
            if ((! $w || ! $h) && preg_match('/viewBox="[\d.\-]+[\s,]+[\d.\-]+[\s,]+([\d.]+)[\s,]+([\d.]+)"/i', $tag[0], $v)) { $w = (float) $v[1]; $h = (float) $v[2]; }
            if ($w > 0 && $h > 0) return [(int) round($w), (int) round($h)];
        }
        return null;
    }
}
