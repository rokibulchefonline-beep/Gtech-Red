<?php

namespace App\Support\Site;

/**
 * Two-tone headings: a port of the website's lib/hl.ts, so Blade highlights exactly the same words.
 *   "Local SEO [[Agency UK]]"  -> "Agency UK" in red;  "Plain [[]]" -> no red;  no markers -> chosen automatically.
 */
class Hl
{
    private const SMALL = ['a', 'an', 'the', 'to', 'of', 'at', 'in', 'on', 'for', 'with', 'by', 'and', 'or', 'your', 'our', 'we', 'you', 'is', 'are', 'that', 'it'];

    /** @return array<int, array{t: string, hl: bool}> */
    public static function parts(?string $input): array
    {
        $text = (string) $input;
        if (str_contains($text, '[[')) {
            $out = [];
            $last = 0;
            preg_match_all('/\[\[(.*?)\]\]/', $text, $ms, PREG_OFFSET_CAPTURE);
            foreach ($ms[0] as $k => [$whole, $at]) {
                if ($at > $last) $out[] = ['t' => substr($text, $last, $at - $last), 'hl' => false];
                if ($ms[1][$k][0] !== '') $out[] = ['t' => $ms[1][$k][0], 'hl' => true];
                $last = $at + strlen($whole);
            }
            if ($last < strlen($text)) $out[] = ['t' => substr($text, $last), 'hl' => false];
            return $out ?: [['t' => str_replace(['[[', ']]'], '', $text), 'hl' => false]];
        }
        [$pre, $hl, $post] = self::pick(trim($text));
        if ($hl === '') return [['t' => $text, 'hl' => false]];
        return array_values(array_filter([['t' => $pre, 'hl' => false], ['t' => $hl, 'hl' => true], ['t' => $post, 'hl' => false]], fn ($p) => $p['t'] !== ''));
    }

    /** HTML for a heading: highlighted parts wrapped in <span class="hl">. */
    public static function html(?string $input): string
    {
        return implode('', array_map(fn ($p) => $p['hl'] ? '<span class="hl">'.e($p['t']).'</span>' : e($p['t']), self::parts($input)));
    }

    public static function plain(?string $s): string
    {
        return str_replace(['[[', ']]'], '', (string) $s);
    }

    private static function wc(string $s): int
    {
        return count(preg_split('/\s+/', trim($s), -1, PREG_SPLIT_NO_EMPTY));
    }

    /** @return array{0: string, 1: string, 2: string} */
    public static function pick(string $h): array
    {
        $cut = function (string $hl) use ($h): array {
            $i = $hl === '' ? false : strpos($h, $hl);
            return $i === false ? ['', '', ''] : [substr($h, 0, $i), $hl, substr($h, $i + strlen($hl))];
        };
        if (self::wc($h) < 2) return ['', '', ''];
        if (preg_match('/^(Frequently Asked Questions)\b/i', $h, $m)) return $cut($m[1]);
        if (preg_match('/^How Much Does (.+) Cost\??$/i', $h, $m)) return $cut(self::trimSmall($m[1]));
        if (preg_match("/^What's Included in Our (.+?)(?: Services)?$/i", $h, $m)) return $cut($m[1]);
        if (preg_match('/^(.+?) (?:Case Studies and Results|Client Reviews and Testimonials)$/i', $h, $m)) return $cut($m[1]);
        if (preg_match('/^(.+?) Compared$/i', $h, $m)) return $cut($m[1]);
        if (preg_match('/^([^:]+):\s*(.+)$/', $h, $m)) return self::wc($m[1]) <= 6 ? $cut($m[1]) : $cut(self::trimSmall($m[2]));
        if (preg_match('/^What (?:Is|Are) (.+?)(?: and How\b.*|\?)$/i', $h, $m)) return $cut(self::trimSmall($m[1]));
        if (preg_match('/^(?:Why|How|When) (?:Choose|Does|Do|Can|Should) (.+?)\??$/i', $h, $m) && self::wc($m[1]) <= 4) return $cut(preg_replace('/\?$/', '', $m[1]));
        if (preg_match('/^Our (.+?) Process, Step by Step$/i', $h, $m)) return $cut($m[1]);
        if (preg_match('/^(.+?) (?:Results and Key Statistics|Services by Industry|Services for Every Industry)$/i', $h, $m)) return $cut($m[1]);
        if (preg_match('/^(.+?) We (?:Serve|Work With|Help)$/i', $h, $m)) return $cut($m[1]);
        if (preg_match('/^Our (\w+)\b/', $h, $m) && self::wc($h) <= 4) return $cut($m[1]);
        if (preg_match('/\b(?:Into|Instead of|Without|Not Just)\s+(.+?)\??$/i', $h, $m)) return $cut(preg_replace('/\?$/', '', self::trimSmall($m[1])) ?: $m[1]);
        if (preg_match('/^([^,]+),\s*(.+)$/', $h, $m)) {
            if (preg_match('/\band\b/', $m[2]) || self::wc($m[2]) > 4) {
                $k = self::keyword($m[1]);
                return self::wc($k) >= 2 ? $cut($k) : $cut(self::lastWords($h));
            }
            return $cut(self::trimSmall($m[2]));
        }
        if (preg_match('/\bvs\b\s+(.+?)\??$/i', $h, $m) && self::wc($h) <= 9) return $cut($m[1]);
        return $cut(self::lastWords($h));
    }

    private static function words(string $s): array
    {
        return preg_split('/\s+/', $s);
    }

    private static function keyword(string $s): string
    {
        $w = self::words($s);
        foreach ($w as $n => $x) {
            if ($n > 0 && preg_match('/^(across|with|for|in|on|of|from|to|by|and|or|vs)$/i', $x)) return implode(' ', array_slice($w, 0, $n));
        }
        return implode(' ', $w);
    }

    private static function trimSmall(string $s): string
    {
        $w = self::words($s);
        while (count($w) > 1 && in_array(strtolower($w[0]), self::SMALL, true)) array_shift($w);
        return implode(' ', $w);
    }

    private static function lastWords(string $h): string
    {
        $w = self::words(preg_replace('/\?$/', '', $h));
        $n = count($w) <= 3 ? 1 : (count($w) <= 6 ? 2 : 3);
        $tail = array_slice($w, -$n);
        while (count($tail) > 1 && in_array(strtolower($tail[0]), self::SMALL, true)) $tail = array_slice($tail, 1);
        return implode(' ', $tail);
    }
}
