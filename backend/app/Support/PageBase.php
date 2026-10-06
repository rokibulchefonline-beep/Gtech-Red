<?php

namespace App\Support;

/** Built-in page copy exported from the website code (database/data/page-base.json). */
class PageBase
{
    private static ?array $pages = null;

    public static function all(): array
    {
        if (self::$pages === null) {
            $file = database_path('data/page-base.json');
            $list = is_file($file) ? json_decode((string) file_get_contents($file), true) : [];
            self::$pages = collect($list ?: [])->keyBy('key')->all();
        }
        return self::$pages;
    }

    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }
}
