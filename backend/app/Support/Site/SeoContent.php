<?php

namespace App\Support\Site;

use App\Models\Page;
use App\Models\SeoKeyword;

/**
 * The SEO service page focused on SEO only. AEO and GEO now have their own page, so this page links to it instead
 * of covering it. Each field changes only while it still holds the earlier text, so edits made in the panel stay.
 */
class SeoContent
{
    private const AEO = '/services/aeo-geo';

    public static function apply(): void
    {
        $p = Page::query()->find('service~search-engine-optimization');
        if (! $p) return;
        $swap = fn ($cur, $old, $new) => $cur === $old ? $new : $cur;

        $p->meta_title = $swap($p->meta_title, 'SEO, AEO & GEO Agency UK | Search & AI Optimisation', 'SEO Services UK | Technical, On-Page & Off-Page SEO Agency');
        $p->meta_description = $swap($p->meta_description,
            'UK SEO agency optimising websites for Google, answer engines and AI search (AEO & GEO). On-page, off-page and technical SEO that turns visibility into leads.',
            'GTech Digital is a UK SEO agency. Technical, on-page and off-page SEO that ranks your website on Google and turns organic traffic into leads. Free audit.');

        $h = (array) $p->hero;
        foreach ([
            'keyword' => ['SEO, AEO and GEO', 'SEO services'],
            'title' => ['SEO, AEO and GEO for Google and', 'SEO Services That Rank You on'],
            'highlight' => ['AI Search', 'Google'],
            'lead' => ['GTech Digital is a UK SEO agency that optimises websites for search engines, answer engines and generative AI, so businesses rank on Google, win featured snippets and are recommended in ChatGPT, Gemini and Google AI Overviews.',
                'GTech Digital is a UK SEO agency that improves the technical health, content and authority of your website, so you rank higher on Google, earn more organic traffic and turn searchers into leads.'],
            'h1' => ['SEO, AEO and GEO Services of [[GTech Digital]]', 'SEO Services UK by [[GTech Digital]]'],
        ] as $k => [$old, $new]) $h[$k] = $swap($h[$k] ?? null, $old, $new);
        $p->hero = $h;

        $sections = [];
        foreach ((array) $p->sections as $s) {
            $id = $s['id'] ?? '';
            if ($id === 'what-is-seo' && ($s['heading'] ?? '') === 'What Are SEO, AEO and GEO?') {
                $s['nav'] = 'What is SEO';
                $s['heading'] = 'What Is SEO?';
                $s['paras'] = [
                    'SEO (search engine optimisation) is the work of improving your website so it ranks higher in Google and Bing for the searches your customers make. Better rankings bring more organic traffic, and more traffic from the right searches brings more enquiries and sales, without paying for each click.',
                    'Want to appear in AI answers as well? See our <a href="'.self::AEO.'">AEO and GEO services</a>.',
                ];
                $s['bullets'] = [
                    'Technical SEO: a fast website that Google can crawl and index',
                    'On-page SEO: content, titles and internal links that match search intent',
                    'Off-page SEO: backlinks and mentions that build authority',
                ];
            }
            if ($id === 'impact') $s['text'] = $swap($s['text'] ?? '', 'The numbers behind our SEO, AEO and GEO work for UK businesses.', 'The numbers behind our SEO work for UK businesses.');
            if ($id === 'on-page') $s['paras'] = $swap($s['paras'] ?? [], ['We optimise every important page so Google and AI tools understand exactly what it offers, and visitors know what to do next.'],
                ['We optimise every important page so Google understands exactly what it offers and which searches it should rank for, and visitors know what to do next.']);
            if ($id === 'off-page') $s['paras'] = $swap($s['paras'] ?? [], ['Links and mentions from respected websites tell Google, and AI models, that your business is credible. We earn them the right way.'],
                ['Links and mentions from respected websites tell Google that your business is credible. We earn them the right way, with no paid link schemes.']);
            if ($id === 'aeo-geo' && ($s['type'] ?? '') === 'media') {
                // The AEO & GEO section becomes a link to the AEO & GEO page.
                $s = ['type' => 'cta', 'id' => 'aeo-geo', 'nav' => 'AI search', 'tone' => 'dark',
                    'heading' => 'Want to Be Cited in [[AI Answers]] Too?',
                    'text' => 'SEO gets you ranking on Google. Our AEO and GEO services get your brand quoted in Google AI Overviews, ChatGPT and Perplexity.',
                    'button' => 'Explore AEO and GEO services', 'link' => self::AEO];
            }
            if ($id === 'services') {
                $s['cards'] = array_map(fn ($c) => ($c['title'] ?? '') === 'AEO & GEO'
                    ? ['icon' => 'lucide:code-xml', 'title' => 'Schema Markup', 'text' => 'Structured data that wins rich results such as stars, FAQs and breadcrumbs.']
                    : (($c['title'] ?? '') === 'Reporting' && ($c['text'] ?? '') === 'Rankings, AI citations, traffic and leads in one monthly report.'
                        ? [...$c, 'text' => 'Rankings, traffic, conversions and leads in one monthly report.'] : $c), $s['cards'] ?? []);
            }
            if ($id === 'process') {
                $s['steps'] = array_map(fn ($st) => match ([$st['title'] ?? '', $st['text'] ?? '']) {
                    ['Audit', 'We review your site, rankings, AI visibility and competitors.'] => [...$st, 'text' => 'We review your site, rankings, backlinks and competitors.'],
                    ['Strategy', 'A keyword and question map with clear targets for leads.'] => [...$st, 'text' => 'A keyword map with clear targets for traffic and leads.'],
                    ['Optimise', 'Pages and content rewritten for SEO, AEO and GEO.'] => [...$st, 'text' => 'Pages and content improved for search intent.'],
                    ['Build authority', 'Links, PR and citations that grow trust.'] => [...$st, 'text' => 'Links and digital PR that grow trust.'],
                    default => $st,
                }, $s['steps'] ?? []);
            }
            if ($id === 'seo-aeo-geo' && ($s['heading'] ?? '') === 'SEO vs AEO vs GEO') {
                $s = ['type' => 'table', 'id' => 'seo-types', 'nav' => 'Types of SEO', 'heading' => 'Technical vs On-Page vs Off-Page SEO',
                    'columns' => ['', 'Technical SEO', 'On-page SEO', 'Off-page SEO'],
                    'rows' => [
                        ['Goal', 'Google can crawl and index every page', 'Each page matches what people search for', 'Google trusts your website'],
                        ['What we work on', 'Speed, Core Web Vitals, sitemaps, redirects', 'Titles, headings, copy, internal links, schema', 'Backlinks, digital PR, brand mentions'],
                        ['Typical timeframe', '2 to 6 weeks to fix', 'Ongoing, page by page', 'Builds over 3 to 6 months'],
                        ['How we measure', 'Indexed pages, Core Web Vitals scores', 'Rankings and click-through rate', 'Referring domains and authority'],
                    ],
                    'note' => 'Strong SEO needs all three working together.'];
            }
            if ($id === 'reviews') {
                $s['reviews'] = array_map(fn ($r) => ($r['text'] ?? '') === 'Our guides are now quoted in Google AI Overviews, and organic enquiries have more than doubled.'
                    ? [...$r, 'text' => 'Our guides now rank on page one for our main services, and organic enquiries have more than doubled.'] : $r, $s['reviews'] ?? []);
            }
            $sections[] = $s;
        }
        $p->sections = $sections;

        $p->faqs = array_map(fn ($f) => match ($f['q'] ?? '') {
            'What is the difference between SEO, AEO and GEO?' => ['q' => 'What is SEO and how does it work?', 'a' => 'SEO (search engine optimisation) improves your website so it ranks higher in Google for the searches your customers make. It covers technical health, on-page content and off-page authority. For visibility in AI answers, GTech Digital also offers separate AEO and GEO services.'],
            'How do you get a business cited in ChatGPT or AI Overviews?' => ['q' => 'What is the difference between local SEO and national SEO?', 'a' => 'Local SEO helps you appear in the Google map pack and for "near me" searches in your area, using your Google Business Profile, citations and reviews. National SEO targets searches across the UK and usually needs more content and stronger backlinks. Many businesses need both.'],
            default => $f,
        }, (array) $p->faqs);
        $p->faqs = array_map(function ($f) {
            $f['a'] = match ($f['a'] ?? '') {
                'No honest SEO agency can guarantee a first-place ranking, because Google controls its algorithm and results change daily. GTech Digital commits to a clear strategy, measurable targets such as traffic, leads and visibility in AI answers, and transparent monthly reporting.'
                    => 'No honest SEO agency can guarantee a first-place ranking, because Google controls its algorithm and results change daily. GTech Digital commits to a clear strategy, measurable targets such as rankings, traffic and leads, and transparent monthly reporting.',
                'A good SEO agency audits your website, fixes technical issues, improves content and internal links, earns quality backlinks and reports on rankings and leads. GTech Digital also provides AI search optimisation, so your brand is cited in ChatGPT, Perplexity and Google AI Overviews.'
                    => 'A good SEO agency audits your website, fixes technical issues, improves content and internal links, earns quality backlinks and reports on rankings, traffic and leads every month. GTech Digital does all of this from one UK team, on rolling monthly terms.',
                default => $f['a'] ?? '',
            };
            return $f;
        }, $p->faqs);
        $p->save();

        $k = SeoKeyword::query()->find('search-engine-optimization');
        if ($k) {
            if ((array) $k->sec === ['seo agency', 'technical seo', 'on-page seo', 'link building', 'AEO', 'GEO', 'AI search optimisation']) $k->sec = ['seo agency', 'technical seo', 'on-page seo', 'off-page seo', 'link building', 'seo company uk'];
            if ((array) $k->ent === ['Google', 'AI Overviews', 'ChatGPT', 'Core Web Vitals', 'schema markup', 'E-E-A-T']) $k->ent = ['Google', 'Bing', 'Core Web Vitals', 'schema markup', 'E-E-A-T', 'Google Business Profile'];
            $k->save();
        }
        PageCache::flush();
    }
}
