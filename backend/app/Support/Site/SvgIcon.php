<?php

namespace App\Support\Site;

use App\Models\Media;
use RuntimeException;

/**
 * Turns an uploaded SVG file into a safe icon: cleaned of scripts and outside links, reduced to its drawing and
 * viewBox, ids made unique, and (optionally) recoloured to follow the text colour like the built-in icons.
 */
class SvgIcon
{
    /** @return array{body:string, viewbox:string, size:int} */
    public static function parse(string $svg, string $slug, bool $mono): array
    {
        if (strlen($svg) > Icons::MAX_UPLOAD_KB * 1024) throw new RuntimeException('The SVG is larger than '.Icons::MAX_UPLOAD_KB.' KB. Simplify it (for example with SVGOMG) and upload again.');
        if (! preg_match('~<svg\b~i', $svg)) throw new RuntimeException('This is not an SVG file.');
        $clean = Media::cleanSvg($svg);
        if ($clean === null) throw new RuntimeException('This SVG could not be read safely.');
        if (preg_match('~<image\b|<foreignObject\b~i', $clean)) throw new RuntimeException('Icons cannot contain embedded pictures or HTML. Use a plain vector SVG.');

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        if (! $dom->loadXML($clean, LIBXML_NONET)) throw new RuntimeException('This SVG could not be read.');
        libxml_clear_errors();
        $root = $dom->documentElement;
        if (strtolower($root->localName) !== 'svg') throw new RuntimeException('This is not an SVG file.');
        $vb = trim((string) $root->getAttribute('viewBox'));
        if ($vb === '') {
            $w = (float) $root->getAttribute('width');
            $h = (float) $root->getAttribute('height');
            if ($w <= 0 || $h <= 0) throw new RuntimeException('The SVG needs a viewBox (or a width and height) so it can be resized.');
            $vb = "0 0 $w $h";
        }
        if (! preg_match('~^-?[\d.]+[ ,]+-?[\d.]+[ ,]+[\d.]+[ ,]+[\d.]+$~', $vb)) throw new RuntimeException('The SVG viewBox is not valid.');
        $vb = preg_replace('~[ ,]+~', ' ', $vb);

        $body = '';
        foreach ($root->childNodes as $n) {
            if ($n instanceof \DOMElement && in_array(strtolower($n->localName), ['title', 'desc', 'metadata'], true)) continue;
            $body .= $dom->saveXML($n);
        }
        $body = trim(preg_replace(['~<!--[\s\S]*?-->~', '~\s+xmlns(:\w+)?="[^"]*"~'], '', $body));
        // Unique ids, so two icons on one page cannot clash (gradients, clip paths, masks).
        $p = 'i-'.$slug.'-';
        $body = preg_replace(['~\bid="([^"]+)"~', '~url\(#([^)]+)\)~', '~(xlink:)?href="#([^"]+)"~'], ['id="'.$p.'$1"', 'url(#'.$p.'$1)', 'href="#'.$p.'$2"'], $body);
        if ($mono) {
            // Every colour becomes the text colour (fills and strokes that are "none" stay none).
            $body = preg_replace(['~\b(fill|stroke)="(?!none|currentColor|url\()[^"]*"~i', '~\b(fill|stroke)\s*:\s*(?!none|currentColor|url\()[^;"]+~i'], ['$1="currentColor"', '$1:currentColor'], $body);
        }
        if (trim(strip_tags($body, '<path><circle><rect><line><polyline><polygon><ellipse><g><use><defs><symbol>')) === '' && ! preg_match('~<(path|circle|rect|line|polyline|polygon|ellipse|use)\b~i', $body)) {
            throw new RuntimeException('The SVG has no drawing in it.');
        }
        return ['body' => $body, 'viewbox' => $vb, 'size' => strlen($body)];
    }
}
