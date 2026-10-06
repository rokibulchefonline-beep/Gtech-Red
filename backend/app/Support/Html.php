<?php

namespace App\Support;

/** Keeps only inline formatting (bold, italic, underline, links, line breaks) from the rich-text boxes. */
class Html
{
    public static function inline(?string $html): string
    {
        $h = (string) $html;
        $h = preg_replace('#<(script|style|iframe|object|embed)\b.*?</\1>#is', '', $h);
        $h = preg_replace('#</div>\s*<div>#i', '<br>', $h);
        $h = strip_tags($h, '<strong><b><em><i><u><s><a><br>');
        // Only safe link targets survive, and every other attribute is dropped.
        $h = preg_replace_callback('#<a\b[^>]*>#i', function ($m) {
            if (! preg_match('#href\s*=\s*(["\'])(.*?)\1#i', $m[0], $hm)) return '<a>';
            $href = html_entity_decode($hm[2]);
            return preg_match('#^(https?:|mailto:|tel:|/(?!/)|\#)#i', $href) ? '<a href="'.e($href).'">' : '<a>';
        }, $h);
        $h = preg_replace('#<(strong|b|em|i|u|s|br)\b[^>]*>#i', '<$1>', $h);
        $h = preg_replace('#(<br>\s*)+$#i', '', trim($h));
        return trim($h);
    }
}
