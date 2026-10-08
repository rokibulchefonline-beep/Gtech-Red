<?php

namespace App\Support\Site;

use App\Models\SiteIcon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Inline SVG icons for the website and the panel's icon pickers. Built in: Lucide (lucide:<name>, ~2,100 line
 * icons, the site's style) and Simple Icons (simple-icons:<name>, ~3,700 brand logos), from resources/data/icons.
 * Uploaded icons (Website content > Icons) are custom:<slug>.
 */
class Icons
{
    public const SETS = ['lucide' => 'Line icons (Lucide)', 'simple-icons' => 'Brand logos (Simple Icons)', 'custom' => 'Your uploaded icons'];

    /** Most SVG an uploaded icon may be. */
    public const MAX_UPLOAD_KB = 20;

    private static array $sets = [];
    private static ?array $custom = null;

    /** @return array<string, array{0:string,1:int,2:int}> */
    private static function set(string $prefix): array
    {
        if ($prefix === 'custom') {
            if (self::$custom === null) {
                try {
                    self::$custom = Cache::rememberForever('site-icons', fn () => SiteIcon::query()->orderBy('name')->get()
                        ->mapWithKeys(fn (SiteIcon $i) => [$i->slug => [$i->body, $i->viewbox, $i->mono, $i->name]])->all());
                } catch (\Throwable) {
                    self::$custom = [];
                }
            }
            return self::$custom;
        }
        if (! isset(self::SETS[$prefix])) return [];
        return self::$sets[$prefix] ??= (is_file($f = resource_path("data/icons/$prefix.php")) ? require $f : []);
    }

    public static function flush(): void
    {
        self::$custom = null;
        Cache::forget('site-icons');
    }

    public static function exists(?string $name): bool
    {
        if (! $name || ! str_contains($name, ':')) return false;
        [$p, $n] = explode(':', $name, 2);
        return isset(self::set($p)[$n]);
    }

    public static function svg(?string $name, int $size = 20, string $class = ''): string
    {
        if (! $name || ! str_contains($name, ':')) return '';
        [$p, $n] = explode(':', $name, 2);
        $d = self::set($p)[$n] ?? null;
        if (! $d) return '';
        $cls = $class !== '' ? ' class="'.e($class).'"' : '';
        $vb = $p === 'custom' ? $d[1] : "0 0 {$d[1]} {$d[2]}";
        $fill = $p === 'custom' && ! $d[2] ? '' : ' fill="currentColor"';
        return "<svg{$cls} width=\"{$size}\" height=\"{$size}\" viewBox=\"{$vb}\"{$fill} aria-hidden=\"true\">{$d[0]}</svg>";
    }

    public static function name(string $key): string
    {
        [$p, $n] = array_pad(explode(':', $key, 2), 2, '');
        if ($p === 'custom') return (string) (self::set('custom')[$n][3] ?? $n);
        return Str::of($n)->replace('-', ' ')->ucfirst()->toString();
    }

    /** Option label for the pickers: the icon and its name. */
    public static function label(string $key): string
    {
        $set = [ 'lucide' => '', 'simple-icons' => ' <span style="opacity:.55;font-size:11px">brand</span>', 'custom' => ' <span style="opacity:.55;font-size:11px">uploaded</span>' ][strtok($key, ':')] ?? '';
        return '<span style="display:inline-flex;align-items:center;gap:8px">'.self::svg($key, 18).'<span>'.e(self::name($key)).'</span>'.$set.'</span>';
    }

    /** Icons the site already uses, offered before typing in a picker. */
    public static function popular(): array
    {
        static $keys = null;
        $keys ??= array_keys(json_decode((string) @file_get_contents(resource_path('data/icons.json')), true) ?: []);
        $custom = array_map(fn ($s) => "custom:$s", array_keys(self::set('custom')));
        return array_values(array_unique([...$custom, ...$keys]));
    }

    /** Search every set by name: uploaded icons first, then line icons, then brand logos. @return string[] keys */
    public static function search(string $q, int $limit = 60): array
    {
        $q = Str::of($q)->lower()->trim()->replace(' ', '-')->toString();
        if ($q === '') return array_slice(self::popular(), 0, $limit);
        $flatQ = str_replace('-', '', $q);
        $out = [];
        foreach (['custom', 'lucide', 'simple-icons'] as $p) {
            $exact = $starts = $contains = [];
            foreach (self::set($p) as $n => $d) {
                $hay = $p === 'custom' ? $n.' '.Str::slug((string) $d[3]) : $n;
                $flat = str_replace('-', '', $hay); // "google ads" also finds "googleads"
                if ($n === $q || $n === $flatQ) $exact[] = "$p:$n";
                elseif (str_starts_with($hay, $q) || str_starts_with($flat, $flatQ)) $starts[] = "$p:$n";
                elseif (str_contains($hay, $q) || str_contains($flat, $flatQ)) $contains[] = "$p:$n";
            }
            $out = [...$out, ...$exact, ...$starts, ...$contains];
            if (count($out) >= $limit) break;
        }
        return array_slice($out, 0, $limit);
    }

    public static function count(string $prefix): int
    {
        return count(self::set($prefix));
    }
}
