<?php

namespace App\Support\Site;

/**
 * Allow-list HTML sanitizer: a port of the website's lib/sanitize-html.ts so Blade outputs the same markup.
 * Anything not explicitly allowed is dropped: scripts, event handlers, styles other than text-align and colour,
 * javascript: URLs, iframes and forms. Every H2/H3 gets an anchor id.
 */
class Sanitizer
{
    private const TAGS = [
        'p' => ['style'], 'h1' => ['id'], 'h2' => ['id'], 'h3' => ['id'], 'h4' => ['id'], 'h5' => ['id'], 'h6' => ['id'], 'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [], 'strike' => [], 'del' => [], 'mark' => [], 'sub' => [], 'sup' => [],
        'ul' => [], 'ol' => [], 'li' => [], 'blockquote' => [], 'pre' => [], 'code' => [], 'br' => [], 'hr' => [], 'a' => ['href', 'target', 'rel', 'title'], 'img' => ['src', 'alt', 'title', 'width', 'height'],
        'figure' => [], 'figcaption' => [], 'span' => ['style'], 'div' => ['style'], 'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [], 'th' => [], 'td' => [],
    ];
    private const VOID = ['br', 'hr', 'img'];

    public static function slugify(string $s): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($s)), '-');
    }

    private static function esc(string $s): string
    {
        return str_replace(['"', '<', '>'], ['&quot;', '&lt;', '&gt;'], preg_replace('/&(?!#?\w+;)/', '&amp;', $s));
    }

    private static function decode(string $s): string
    {
        $s = preg_replace_callback('/&#x([0-9a-f]+);?/i', fn ($m) => mb_chr(hexdec($m[1]) & 0xFFFF ?: 32), $s);
        $s = preg_replace_callback('/&#(\d+);?/', fn ($m) => mb_chr(((int) $m[1]) & 0xFFFF ?: 32), $s);
        return preg_replace('/&tab;|&newline;/i', '', preg_replace('/&colon;/i', ':', $s));
    }

    private static function cleanStyle(string $v): string
    {
        $ok = array_filter(array_map('trim', explode(';', $v)), fn ($d) => preg_match('/^text-align\s*:\s*(left|right|center|justify)$/i', $d)
            || preg_match('/^color\s*:\s*(#[0-9a-f]{3,8}|rgb\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*\))$/i', $d));
        return implode(';', $ok);
    }

    private static function cleanUrl(string $v, string $kind): string
    {
        $u = preg_replace('/[\x00-\x20\x7f]+/', '', self::decode($v));
        $ok = $kind === 'href' ? '/^(https?:|mailto:|tel:|\/(?!\/)|#)/i' : '/^(https?:\/\/|\/(?!\/))/i';
        return preg_match($ok, $u) ? trim($v) : '';
    }

    public static function clean(?string $input): string
    {
        $src = preg_replace('/<!--[\s\S]*?-->/', '', (string) $input);
        $src = preg_replace('/<(script|style|iframe|object|embed|form|textarea|noscript|svg|math)\b[\s\S]*?<\/\1\s*>/i', '', $src);
        $text = fn (string $t) => str_replace(['<', '>'], ['&lt;', '&gt;'], $t);
        $out = '';
        $last = 0;
        preg_match_all('/<(\/?)([a-zA-Z][a-zA-Z0-9]*)((?:"[^"]*"|\'[^\']*\'|[^\'">])*)>/', $src, $ms, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        foreach ($ms as $m) {
            $out .= $text(substr($src, $last, $m[0][1] - $last));
            $last = $m[0][1] + strlen($m[0][0]);
            $close = $m[1][0];
            $name = strtolower($m[2][0]);
            $allowed = self::TAGS[$name] ?? null;
            if ($allowed === null) continue;
            if ($close !== '') { if (! in_array($name, self::VOID, true)) $out .= "</$name>"; continue; }
            $attrs = '';
            $target = '';
            preg_match_all('/([a-zA-Z_:][-a-zA-Z0-9_:.]*)\s*(?:=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'=<>`]+)))?/', $m[3][0], $as, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL);
            foreach ($as as $a) {
                $k = strtolower($a[1]);
                $v = $a[2] ?? $a[3] ?? $a[4] ?? '';
                if (! in_array($k, $allowed, true)) continue;
                if ($k === 'href' || $k === 'src') { $u = self::cleanUrl($v, $k); if ($u !== '') $attrs .= " $k=\"".self::esc($u).'"'; }
                elseif ($k === 'style') { $s = self::cleanStyle(self::decode($v)); if ($s !== '') $attrs .= ' style="'.self::esc($s).'"'; }
                elseif ($k === 'target') { if ($v === '_blank') { $target = '_blank'; $attrs .= ' target="_blank"'; } }
                elseif ($k === 'id') { $s = self::slugify($v); if ($s !== '') $attrs .= " id=\"$s\""; }
                elseif ($k === 'width' || $k === 'height') { if (preg_match('/^\d{1,4}$/', $v)) $attrs .= " $k=\"$v\""; }
                elseif ($k !== 'rel') $attrs .= " $k=\"".self::esc($v).'"';
            }
            if ($name === 'a' && $target) $attrs .= ' rel="noopener noreferrer"';
            if ($name === 'img' && ! preg_match('/ alt=/', $attrs)) $attrs .= ' alt=""';
            $out .= "<$name$attrs".(in_array($name, self::VOID, true) ? '/' : '').'>';
        }
        $out .= $text(substr($src, $last));
        $used = [];
        return preg_replace_callback('/<h([23])((?:\s[^>]*)?)>([\s\S]*?)<\/h\1>/', function ($m) use (&$used) {
            $base = self::slugify(preg_replace('/<[^>]+>/', '', $m[3])) ?: 'section';
            $id = preg_match('/id="([^"]+)"/', $m[2], $im) ? $im[1] : $base;
            $n = 2;
            while (isset($used[$id])) $id = $base.'-'.$n++;
            $used[$id] = true;
            return "<h{$m[1]} id=\"$id\">{$m[3]}</h{$m[1]}>";
        }, $out);
    }
}
