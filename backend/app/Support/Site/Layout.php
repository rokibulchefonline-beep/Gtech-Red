<?php

namespace App\Support\Site;

use App\Models\Page;

/**
 * Section order for the pages with a designed layout (home, about, contact, the hubs and the legal pages).
 * Their designed parts can be moved or hidden, and builder widgets can be inserted between them. The order is
 * stored in data.layout: ['type' => 'designed', 'key' => 'who'] for a designed part, or a normal builder section.
 */
class Layout
{
    /** Designed parts of each page, in their original order. About lists its own sections as "sec:<id>". */
    public const DESIGNED = [
        'page~home' => ['partners' => 'Partner logos strip', 'who' => 'Who we are (video and text)', 'services' => 'Our services (card stack)', 'how' => 'How we work (3 steps)',
            'brands' => 'Client brands', 'cases' => 'Case studies slider', 'results' => 'Results grid', 'testimonials' => 'Client testimonials', 'faq' => 'FAQs', 'inquiry' => 'Enquiry form'],
        'page~about' => ['partners' => 'Partner logos strip', 'cases' => 'Case studies slider', 'faq' => 'FAQs', 'inquiry' => 'Enquiry form'],
        'page~contact' => ['form' => 'Contact form and details', 'partners' => 'Partner logos strip', 'faq' => 'FAQs'],
        'page~services-hub' => ['groups' => 'Service categories (tabs and sections)', 'why' => 'Why choose us', 'process' => 'Our process', 'faq' => 'FAQs', 'inquiry' => 'Enquiry form'],
        'page~industries-hub' => ['how' => 'How we work with every sector', 'sectors' => 'Industry cards', 'inquiry' => 'Enquiry form'],
        'legal' => ['content' => 'Policy text with contents list'],
    ];

    /** Designed parts of a page: key => label. */
    public static function designed(Page $p): array
    {
        $d = self::DESIGNED[$p->kind === 'legal' ? 'legal' : $p->key] ?? [];
        if ($p->key === 'page~about') {
            $secs = [];
            foreach ((array) $p->sections as $s) {
                if (! empty($s['id'])) $secs['sec:'.$s['id']] = 'Section: '.Hl::plain(strip_tags((string) ($s['heading'] ?? $s['id'])));
            }
            // The original order: about sections up to the reviews, then case studies, then the rest.
            $ids = array_keys($secs);
            $cut = array_search('sec:reviews', $ids, true);
            $cut = $cut === false ? max(0, count($ids) - 1) : $cut;
            $d = ['partners' => $d['partners']] + array_slice($secs, 0, $cut, true) + ['cases' => $d['cases']] + array_slice($secs, $cut, null, true) + ['faq' => $d['faq'], 'inquiry' => $d['inquiry']];
        }
        return $d;
    }

    /** The page's sections in display order. */
    public static function items(Page $p): array
    {
        $designed = self::designed($p);
        $stored = $p->data['layout'] ?? null;
        if (! is_array($stored)) return array_map(fn ($k) => ['type' => 'designed', 'key' => $k], array_keys($designed));
        // Designed parts that no longer exist (e.g. a removed about section) are dropped.
        return array_values(array_filter($stored, fn ($it) => ($it['type'] ?? '') !== 'designed' || isset($designed[$it['key'] ?? ''])));
    }
}
