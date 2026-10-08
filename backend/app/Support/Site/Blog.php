<?php

namespace App\Support\Site;

use App\Models\Post;

/** Blog helpers: PHP ports of lib/blog-utils.ts and components/blog/PostBody.tsx. */
class Blog
{
    public const AUTHOR = 'GTech Editorial Team';
    public const BIO = 'The GTech Digital editorial team shares practical advice on SEO, paid media, social media, web design and software, written by the specialists who deliver it for UK businesses every day.';

    private const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'];

    public static function slugify(string $s): string
    {
        return Sanitizer::slugify($s);
    }

    /** "24 Sept 2026", as toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }). */
    public static function date(Post $p): string
    {
        $d = $p->date ?? $p->created_at ?? now();
        return $d->format('j').' '.self::MONTHS[$d->format('n') - 1].' '.$d->format('Y');
    }

    public static function stripHtml(string $h): string
    {
        $t = str_replace(['&nbsp;', '&amp;', '&lt;', '&gt;'], [' ', '&', '<', '>'], preg_replace('/<[^>]+>/', ' ', $h));
        return trim(preg_replace('/\s+/', ' ', $t));
    }

    public static function words(string $body, bool $html = false): int
    {
        return preg_match_all('/\S+/', $html ? self::stripHtml($body) : $body);
    }

    public static function readTime(string $body, bool $html = false): int
    {
        // Math.round rounds halves up; PHP's round() would round them away from zero (same for positives).
        return max(1, (int) floor(self::words($body, $html) / 220 + 0.5));
    }

    public static function isHtml(Post $p): bool
    {
        return $p->format === 'html';
    }

    /** Minimal Markdown parser (parseBody in lib/blog-utils.ts). */
    public static function parse(string $body): array
    {
        $blocks = [];
        $para = [];
        $flush = function () use (&$blocks, &$para) {
            if ($para) $blocks[] = ['type' => 'p', 'text' => implode(' ', $para)];
            $para = [];
        };
        $list = function (string $type, string $item) use (&$blocks, $flush) {
            $flush();
            $n = count($blocks) - 1;
            if ($n >= 0 && $blocks[$n]['type'] === $type) $blocks[$n]['items'][] = $item;
            else $blocks[] = ['type' => $type, 'items' => [$item]];
        };
        foreach (explode("\n", str_replace("\r", '', $body)) as $raw) {
            $l = trim($raw);
            if ($l === '') $flush();
            elseif (str_starts_with($l, '### ')) { $flush(); $blocks[] = ['type' => 'h3', 'text' => substr($l, 4), 'id' => self::slugify(substr($l, 4))]; }
            elseif (str_starts_with($l, '## ')) { $flush(); $blocks[] = ['type' => 'h2', 'text' => substr($l, 3), 'id' => self::slugify(substr($l, 3))]; }
            elseif (preg_match('/^(-{3,}|\*{3,})$/', $l)) { $flush(); $blocks[] = ['type' => 'hr']; }
            elseif (preg_match('/^!\[([^\]]*)\]\(([^)\s]+)\)$/', $l, $m)) { $flush(); $blocks[] = ['type' => 'img', 'alt' => $m[1], 'src' => $m[2]]; }
            elseif (str_starts_with($l, '> ')) {
                $flush();
                $n = count($blocks) - 1;
                if ($n >= 0 && $blocks[$n]['type'] === 'quote') $blocks[$n]['text'] .= ' '.substr($l, 2);
                else $blocks[] = ['type' => 'quote', 'text' => substr($l, 2)];
            }
            elseif (preg_match('/^[-*] /', $l)) $list('ul', substr($l, 2));
            elseif (preg_match('/^\d+\. (.*)$/', $l, $m)) $list('ol', $m[1]);
            else $para[] = $l;
        }
        $flush();
        return $blocks;
    }

    /** Inline Markdown: **bold**, *italic*, `code` and [text](url) (Rich in PostBody.tsx). Escaped HTML. */
    public static function rich(string $t): string
    {
        $parts = preg_split('/(\*\*[^*]+\*\*|\*[^*\s][^*]*\*|`[^`]+`|\[[^\]]+\]\([^)\s]+\))/', $t, -1, PREG_SPLIT_DELIM_CAPTURE);
        $out = '';
        foreach ($parts as $s) {
            $len = strlen($s);
            if (str_starts_with($s, '**') && str_ends_with($s, '**') && $len > 4) $out .= '<strong>'.e(substr($s, 2, -2)).'</strong>';
            elseif (str_starts_with($s, '`') && str_ends_with($s, '`') && $len > 2) $out .= '<code>'.e(substr($s, 1, -1)).'</code>';
            elseif (str_starts_with($s, '*') && str_ends_with($s, '*') && $len > 2) $out .= '<em>'.e(substr($s, 1, -1)).'</em>';
            elseif (preg_match('/^\[([^\]]+)\]\(([^)\s]+)\)$/', $s, $m)) {
                $href = preg_match('/^(https?:\/\/|\/|mailto:|#)/i', $m[2]) ? $m[2] : '#';
                $ext = preg_match('/^https?:\/\//i', $href) ? ' target="_blank" rel="noopener noreferrer"' : '';
                $out .= '<a href="'.e($href).'"'.$ext.'>'.e($m[1]).'</a>';
            } else $out .= e($s);
        }
        return $out;
    }

    /** Table of contents entries: H2 headings of the post. */
    public static function toc(Post $p): array
    {
        if (self::isHtml($p)) {
            preg_match_all('/<h2 id="([^"]+)">([\s\S]*?)<\/h2>/', Sanitizer::clean($p->body), $ms, PREG_SET_ORDER);
            return array_map(fn ($m) => ['id' => $m[1], 'text' => trim(str_replace(['&amp;', '&lt;', '&gt;', '&quot;'], ['&', '<', '>', '"'], preg_replace('/<[^>]+>/', '', $m[2])))], $ms);
        }
        return array_values(array_map(fn ($b) => ['id' => $b['id'], 'text' => $b['text']], array_filter(self::parse((string) $p->body), fn ($b) => $b['type'] === 'h2')));
    }

    /** Inline Markdown as HTML for the editor (inline() in lib/blog-utils.ts). */
    private static function inlineHtml(string $t): string
    {
        $h = htmlspecialchars($t, ENT_NOQUOTES);
        $h = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $h);
        $h = preg_replace('/(^|[^*])\*([^*\s][^*]*)\*/', '$1<em>$2</em>', $h);
        $h = preg_replace('/`([^`]+)`/', '<code>$1</code>', $h);
        return preg_replace('/\[([^\]]+)\]\(([^)\s]+)\)/', '<a href="$2">$1</a>', $h);
    }

    /** Old Markdown post bodies, converted so they open correctly in the visual editor (markdownToHtml). */
    public static function markdownToHtml(string $md): string
    {
        return implode("\n", array_map(function (array $b) {
            return match ($b['type']) {
                'h2', 'h3' => "<{$b['type']}>".self::inlineHtml($b['text'])."</{$b['type']}>",
                'ul', 'ol' => "<{$b['type']}>".implode('', array_map(fn ($i) => '<li>'.self::inlineHtml($i).'</li>', $b['items']))."</{$b['type']}>",
                'quote' => '<blockquote>'.self::inlineHtml($b['text']).'</blockquote>',
                'img' => '<p><img src="'.htmlspecialchars($b['src']).'" alt="'.htmlspecialchars($b['alt']).'"/></p>',
                'hr' => '<hr/>',
                default => '<p>'.self::inlineHtml($b['text']).'</p>',
            };
        }, self::parse($md)));
    }

    /** Article images with a caption (the "Caption" field in the editor's image box) get it shown underneath. */
    public static function captions(string $html): string
    {
        return (string) preg_replace_callback('~(?:<p>\s*)?(<img\b[^>]*\btitle="([^"]+)"[^>]*>)(?:\s*</p>)?~i', function ($m) {
            $img = preg_replace('~\stitle="[^"]*"~i', '', $m[1]);
            return '<figure>'.$img.'<figcaption>'.$m[2].'</figcaption></figure>';
        }, $html);
    }
}
