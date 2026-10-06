<?php

namespace App\Support\Site;

/** Inline SVG icons, identical to the website's React <Icon> (data: resources/data/icons.json). */
class Icons
{
    private static ?array $set = null;

    public static function svg(?string $name, int $size = 20, string $class = ''): string
    {
        self::$set ??= json_decode((string) file_get_contents(resource_path('data/icons.json')), true) ?: [];
        $d = $name ? (self::$set[$name] ?? null) : null;
        if (! $d) return '';
        $cls = $class !== '' ? ' class="'.e($class).'"' : '';
        return "<svg{$cls} width=\"{$size}\" height=\"{$size}\" viewBox=\"0 0 {$d['w']} {$d['h']}\" fill=\"currentColor\" aria-hidden=\"true\">{$d['body']}</svg>";
    }
}
