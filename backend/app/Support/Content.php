<?php

namespace App\Support;

/** The exported website content (database/data/content.json). Used to seed MySQL. */
class Content
{
    private static ?array $data = null;

    public static function all(): array
    {
        if (self::$data === null) {
            $file = database_path('data/content.json');
            self::$data = is_file($file) ? (json_decode((string) file_get_contents($file), true) ?: []) : [];
        }
        return self::$data;
    }

    public static function get(string $key, mixed $default = []): mixed
    {
        return data_get(self::all(), $key, $default);
    }

    /** Applies an old-style page override (MongoDB page_content) onto full page content, the same way the website did. */
    public static function mergeOverride(array $page, array $o): array
    {
        foreach (['metaTitle' => 'meta_title', 'metaDescription' => 'meta_description', 'focusKeyword' => 'focus_keyword'] as $from => $to) {
            if (! empty($o[$from])) $page[$to] = $o[$from];
        }
        foreach (['h1', 'keyword', 'lead'] as $f) if (! empty($o['hero'][$f])) $page['hero'][$f] = $o['hero'][$f];
        if (! empty($o['hero']['points'])) $page['hero']['points'] = array_values($o['hero']['points']);
        foreach ($page['sections'] as $i => $s) {
            $e = $o['sections'][$s['id'] ?? ''] ?? null;
            if (! is_array($e)) continue;
            foreach (['heading', 'intro', 'text'] as $f) if (! empty($e[$f]) && ($f === 'heading' || array_key_exists($f, $s))) $page['sections'][$i][$f] = $e[$f];
            foreach (['paras', 'bullets'] as $f) if (! empty($e[$f]) && array_key_exists($f, $s)) $page['sections'][$i][$f] = array_values($e[$f]);
            foreach (['cards', 'steps', 'stats', 'reviews', 'items'] as $f) {
                if (empty($e[$f]) || ! is_array($s[$f] ?? null)) continue;
                foreach ($s[$f] as $j => $item) $page['sections'][$i][$f][$j] = array_merge((array) $item, (array) ($e[$f][$j] ?? []));
            }
        }
        if (! empty($o['faqs'])) $page['faqs'] = array_values($o['faqs']);
        return $page;
    }
}
