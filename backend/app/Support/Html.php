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

    private const BLOCKS = ['p', 'ul', 'ol', 'div', 'blockquote', 'table', 'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'figure', 'hr'];

    /**
     * The article editor needs the text of every list item inside a paragraph (<li><p>…</p></li>); the stored
     * posts have <li>…</li>. Without this, text in bullet points cannot be selected, linked or formatted.
     */
    public static function listsForEditor(?string $html): string
    {
        return self::eachLi((string) $html, function (\DOMElement $li, \DOMDocument $doc) {
            $run = [];
            $flush = function (?\DOMNode $before) use (&$run, $li, $doc) {
                $text = implode('', array_map(fn ($n) => $n->textContent, $run));
                if ($run && (trim($text) !== '' || array_filter($run, fn ($n) => $n instanceof \DOMElement))) {
                    $p = $doc->createElement('p');
                    $li->insertBefore($p, $before);
                    foreach ($run as $n) $p->appendChild($n);
                } else foreach ($run as $n) $li->removeChild($n);
                $run = [];
            };
            foreach (iterator_to_array($li->childNodes) as $n) {
                if ($n instanceof \DOMElement && in_array(strtolower($n->tagName), self::BLOCKS, true)) $flush($n);
                else $run[] = $n;
            }
            $flush(null);
        });
    }

    /** Back to the stored form: a list item holding one paragraph keeps just its text (the site's lists are styled that way). */
    public static function listsForSite(?string $html): string
    {
        return self::eachLi((string) $html, function (\DOMElement $li) {
            $ps = array_values(array_filter(iterator_to_array($li->childNodes), fn ($n) => $n instanceof \DOMElement && strtolower($n->tagName) === 'p'));
            $first = $li->firstChild;
            while ($first && $first->nodeType === XML_TEXT_NODE && trim($first->textContent) === '') $first = $first->nextSibling;
            if (count($ps) !== 1 || $first !== $ps[0]) return;
            $p = $ps[0];
            while ($p->firstChild) $li->insertBefore($p->firstChild, $p);
            $li->removeChild($p);
        });
    }

    private static function eachLi(string $html, callable $fn): string
    {
        if (stripos($html, '<li') === false) return $html;
        $doc = new \DOMDocument();
        $prev = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div id="__root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        $root = $doc->getElementById('__root');
        if (! $root) return $html;
        foreach (iterator_to_array($doc->getElementsByTagName('li')) as $li) $fn($li, $doc);
        $out = '';
        foreach ($root->childNodes as $n) $out .= $doc->saveHTML($n);
        return $out;
    }
}
