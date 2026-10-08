<?php

namespace App\Support\Site;

use App\Models\Page;
use App\Models\SeoKeyword;

/**
 * Digital Marketing category optimisation: the AEO & GEO keyword map entry, internal links to AEO & GEO from the
 * search pages and to the Digital Marketing category from the paid pages, and one more Content Marketing FAQ.
 * Only adds what is missing, so edits made in the panel stay.
 */
class DigitalMarketingContent
{
    public static function apply(): void
    {
        SeoKeyword::query()->firstOrCreate(['slug' => 'aeo-geo'], [
            'kw' => 'aeo and geo services',
            'sec' => ['answer engine optimisation', 'generative engine optimisation', 'AI search optimisation', 'AI Overviews optimisation', 'ChatGPT visibility'],
            'ent' => ['ChatGPT', 'Google AI Overviews', 'Perplexity', 'Gemini', 'schema markup', 'E-E-A-T'],
            'links' => [
                ['target' => 'search-engine-optimization', 'anchor' => 'SEO services', 'why' => 'The foundation AI answers draw on'],
                ['target' => 'content-marketing', 'anchor' => 'answer-first content marketing', 'why' => 'Content AI tools can quote'],
                ['target' => 'local-seo', 'anchor' => 'local SEO', 'why' => 'Be named for "near me" questions'],
                ['target' => 'reputation-management', 'anchor' => 'reviews and reputation management', 'why' => 'Trust signals AI tools check'],
                ['target' => 'seo-backlinks', 'anchor' => 'digital PR and link building', 'why' => 'Mentions on trusted sites'],
            ],
        ]);
        $link = function (string $from, string $target, string $anchor, string $why) {
            $k = SeoKeyword::query()->find($from);
            if (! $k || in_array($target, array_column((array) $k->links, 'target'), true)) return;
            $k->links = [...(array) $k->links, ['target' => $target, 'anchor' => $anchor, 'why' => $why]];
            $k->save();
        };
        $link('search-engine-optimization', 'aeo-geo', 'AEO and GEO services', 'Get cited in AI answers');
        $link('content-marketing', 'aeo-geo', 'AEO and GEO', 'Content that AI tools quote');
        $link('local-seo', 'aeo-geo', 'AI search optimisation', 'Be recommended in AI answers');
        $link('ecommerce-seo', 'aeo-geo', 'AEO and GEO for ecommerce', 'Products cited in AI shopping answers');
        foreach (['google-ads', 'paid-media', 'digital-advertising', 'seo-backlinks', 'reputation-management'] as $from) {
            $link($from, 'digital-marketing', 'full digital marketing services', 'Every channel, one team');
        }
        // Related service cards: AEO & GEO next to the search services.
        foreach (['search-engine-optimization', 'content-marketing', 'local-seo', 'ecommerce-seo', 'digital-marketing'] as $slug) {
            $p = Page::query()->find("service~$slug");
            if ($p && ! in_array('aeo-geo', (array) $p->related, true)) { $p->related = ['aeo-geo', ...(array) $p->related]; $p->save(); }
        }
        $p = Page::query()->find('service~content-marketing');
        if ($p && count((array) $p->faqs) < 8 && ! in_array('How does content marketing help with AI search?', array_column((array) $p->faqs, 'q'), true)) {
            $p->faqs = [...(array) $p->faqs, ['q' => 'How does content marketing help with AI search?', 'a' => 'AI tools such as ChatGPT and Google AI Overviews quote clear, trustworthy content. GTech Digital writes answer-first articles and guides with direct answers, sources and named authors, so your business is more likely to be cited when customers ask questions about your services.']];
            $p->save();
        }
        // Concrete, citable figures (typical UK ranges and timeframes) added once to the pricing intro.
        foreach ([
            'local-seo' => 'Most local SEO campaigns show map pack movement within 8 to 12 weeks, and Google Business Profiles with 4.5+ stars and regular reviews tend to win more calls.',
            'ecommerce-seo' => 'Most ecommerce SEO projects show ranking gains within 3 to 6 months, with category pages usually moving first.',
            'seo-backlinks' => 'Typical campaigns earn 5 to 20 quality links a month, and results usually build over 3 to 6 months.',
            'reputation-management' => 'Most review programmes double monthly review volume within 3 months, and replying within 24 to 48 hours is the standard we work to.',
        ] as $slug => $line) {
            $p = Page::query()->find("service~$slug");
            if (! $p) continue;
            $sections = array_map(function ($s) use ($line) {
                if (($s['id'] ?? '') === 'pricing' && ! str_contains((string) ($s['intro'] ?? ''), $line)) $s['intro'] = trim(($s['intro'] ?? '').' '.$line);
                return $s;
            }, (array) $p->sections);
            if ($sections !== (array) $p->sections) { $p->sections = $sections; $p->save(); }
        }
        PageCache::flush();
    }
}
