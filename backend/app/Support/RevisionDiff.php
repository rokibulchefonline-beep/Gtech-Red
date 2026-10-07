<?php

namespace App\Support;

use App\Filament\Support\PageBlocks;

/**
 * What changed between two versions of a page, post or case study, as readable rows: a field name and the old and
 * new text with removed words struck through and added words highlighted.
 */
class RevisionDiff
{
    private const LABELS = [
        'name' => 'Page name', 'title' => 'Title', 'slug' => 'Address', 'path' => 'Address', 'published' => 'Published', 'status' => 'Status',
        'meta_title' => 'SEO title', 'meta_description' => 'Meta description', 'focus_keyword' => 'Focus keyword', 'excerpt' => 'Excerpt', 'body' => 'Article',
        'image' => 'Image', 'image_alt' => 'Image alt text', 'date' => 'Date', 'canonical' => 'Canonical address', 'noindex' => 'Hidden from search engines',
        'hero.h1' => 'Main heading (H1)', 'hero.lead' => 'Opening description', 'hero.points' => 'Highlights', 'hero.motion' => 'Hero image', 'hero.keyword' => 'Main keyword',
        'related' => 'Related services', 'challenge' => 'Challenge', 'solution' => 'Solution', 'results' => 'Results', 'quote' => 'Quote', 'client' => 'Client',
    ];

    /** @return array<array{label:string, old:string, new:string, html:string}> */
    public static function rows(string $model, array $old, array $new): array
    {
        $a = self::flatten($model, $old);
        $b = self::flatten($model, $new);
        $out = [];
        foreach (array_unique([...array_keys($a), ...array_keys($b)]) as $k) {
            $x = $a[$k] ?? '';
            $y = $b[$k] ?? '';
            if ($x === $y) continue;
            $out[] = ['label' => $k, 'old' => $x, 'new' => $y, 'html' => self::words($x, $y)];
        }
        return $out;
    }

    /** Readable "field => text" pairs. Page sections are matched by their id, so moving one shows as a move only. */
    public static function flatten(string $model, array $d): array
    {
        $out = [];
        $sections = $model === 'page' ? (array) ($d['sections'] ?? []) : [];
        unset($d['sections']);
        self::walk($d, '', $out);
        $order = [];
        $seen = [];
        foreach (array_values($sections) as $i => $s) {
            $name = (PageBlocks::LABELS[$s['type'] ?? ''] ?? ucfirst((string) ($s['type'] ?? 'Section'))).' section "'.str_replace(['[[', ']]'], '', self::text((string) ($s['heading'] ?? $s['id'] ?? ''))).'"';
            $order[] = $name;
            // Two sections with the same heading stay apart (matched by their id).
            if (isset($seen[$name])) $name .= ' ('.($s['id'] ?? $i).')';
            $seen[$name] = true;
            $s2 = $s;
            unset($s2['type'], $s2['id']);
            self::walk($s2, $name.' › ', $out);
        }
        if ($sections) $out['Section order'] = implode("\n", array_map(fn ($n, $i) => ($i + 1).'. '.$n, $order, array_keys($order)));
        return $out;
    }

    private static function walk(mixed $v, string $prefix, array &$out): void
    {
        if (is_array($v)) {
            if (array_is_list($v) && ! array_filter($v, 'is_array')) {
                $out[self::label(rtrim($prefix, '.› '))] = implode("\n", array_map(fn ($x) => self::text((string) $x), $v));
                return;
            }
            foreach ($v as $k => $x) {
                $key = is_int($k) ? $prefix.'#'.($k + 1).' ' : $prefix.$k.'.';
                self::walk($x, $key, $out);
            }
            return;
        }
        $out[self::label(rtrim($prefix, '. '))] = is_bool($v) ? ($v ? 'Yes' : 'No') : self::text((string) $v);
    }

    private static function label(string $path): string
    {
        $path = str_replace(['.#', '. '], [' #', ' '], $path);
        return self::LABELS[$path] ?? str_replace(['_', '.'], [' ', ' › '], ucfirst($path));
    }

    /** HTML to plain text, keeping paragraph breaks. */
    public static function text(string $html): string
    {
        $t = preg_replace('#<(?:/p|br\s*/?|/h[1-6]|/li|/blockquote)>#i', "\n", $html);
        return trim(preg_replace("/[ \t]+/", ' ', preg_replace("/\n{3,}/", "\n\n", html_entity_decode(strip_tags((string) $t), ENT_QUOTES | ENT_HTML5))));
    }

    /** Word-level difference as HTML: <del> removed, <ins> added. Long texts are compared paragraph by paragraph. */
    public static function words(string $a, string $b): string
    {
        $pa = preg_split("/\n+/", $a);
        $pb = preg_split("/\n+/", $b);
        if (count($pa) > 1 || count($pb) > 1) {
            $html = [];
            foreach (self::lcs($pa, $pb) as [$op, $x, $y]) {
                $html[] = match ($op) {
                    '=' => '<p>'.e($x).'</p>',
                    '-' => '<p><del>'.e($x).'</del></p>',
                    '+' => '<p><ins>'.e($y).'</ins></p>',
                };
            }
            return self::pairParagraphs(implode('', $html));
        }
        $ta = preg_split('/(\s+)/', $a, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $tb = preg_split('/(\s+)/', $b, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        if (count($ta) * count($tb) > 250000) return '<del>'.e($a).'</del> <ins>'.e($b).'</ins>';
        $html = '';
        foreach (self::lcs($ta, $tb) as [$op, $x, $y]) {
            $html .= match ($op) { '=' => e($x), '-' => '<del>'.e($x).'</del>', '+' => '<ins>'.e($y).'</ins>' };
        }
        return str_replace(['</del><del>', '</ins><ins>'], '', $html);
    }

    /** A removed paragraph followed by an added one is shown as one paragraph with the words that changed. */
    private static function pairParagraphs(string $html): string
    {
        return preg_replace_callback('#<p><del>(.*?)</del></p><p><ins>(.*?)</ins></p>#s', fn ($m) => '<p>'.self::words(html_entity_decode($m[1], ENT_QUOTES), html_entity_decode($m[2], ENT_QUOTES)).'</p>', $html);
    }

    /** Longest common subsequence: a list of ['=', x, y], ['-', x, null] and ['+', null, y]. */
    private static function lcs(array $a, array $b): array
    {
        $n = count($a);
        $m = count($b);
        if ($n * $m > 250000) return [...array_map(fn ($x) => ['-', $x, null], $a), ...array_map(fn ($y) => ['+', null, $y], $b)];
        $L = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));
        for ($i = $n - 1; $i >= 0; $i--) for ($j = $m - 1; $j >= 0; $j--) {
            $L[$i][$j] = $a[$i] === $b[$j] ? $L[$i + 1][$j + 1] + 1 : max($L[$i + 1][$j], $L[$i][$j + 1]);
        }
        $out = [];
        $i = $j = 0;
        while ($i < $n && $j < $m) {
            if ($a[$i] === $b[$j]) { $out[] = ['=', $a[$i], $b[$j]]; $i++; $j++; }
            elseif ($L[$i + 1][$j] >= $L[$i][$j + 1]) $out[] = ['-', $a[$i++], null];
            else $out[] = ['+', null, $b[$j++]];
        }
        while ($i < $n) $out[] = ['-', $a[$i++], null];
        while ($j < $m) $out[] = ['+', null, $b[$j++]];
        return $out;
    }
}
