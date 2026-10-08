<?php

namespace App\Support\Site\Content;

use App\Models\Page;
use App\Models\SeoKeyword;
use App\Support\Site\PageCache;

/**
 * October 2026 content review: applies the researched copy (IndustryCopy, ServiceCopy) to the pages. A field only
 * changes while it still holds the text it had before the review (resources/data/content-review/*-before.json),
 * so anything edited in the panel stays as it is. Safe to run again.
 */
class ContentReview
{
    public static function apply(): void
    {
        self::industries();
        ServiceCopy::apply();
        self::mainPages();
        PageCache::flush();
    }

    private static function before(string $name): array
    {
        return json_decode((string) @file_get_contents(resource_path("data/content-review/$name-before.json")), true) ?: [];
    }

    /** Field still as it was before the review (or empty)? */
    private static function untouched(mixed $current, mixed $before): bool
    {
        return $current === $before || $current === null || $current === '' || $current === [];
    }

    public static function industries(): void
    {
        $before = self::before('industries');
        foreach (IndustryCopy::all() as $slug => $c) {
            $p = Page::query()->find("industry~$slug");
            $b = $before[$slug] ?? null;
            if (! $p || ! $b) continue;

            if (self::untouched($p->meta_title, $b['meta_title'])) $p->meta_title = $c['title'];
            if (self::untouched($p->meta_description, $b['meta_description'])) $p->meta_description = $c['desc'];
            $hero = (array) $p->hero;
            if (self::untouched($hero['lead'] ?? '', $b['lead'])) $hero['lead'] = $c['lead'];
            $p->hero = $hero;

            $aud = $c['aud'];
            $generic = [
                'impact' => "Results We Deliver for [[$aud]]",
                'services' => "What We Do for $aud",
                'process' => 'How We Work With You, Step by Step',
                'case-studies' => "Recent Work for $aud",
            ];
            $sections = [];
            foreach ((array) $p->sections as $s) {
                $id = $s['id'] ?? '';
                $old = $b['sections'][$id] ?? null;
                $same = fn (string $f) => $old !== null && ($s[$f] ?? null) === ($old[$f] ?? null);
                if ($id === 'overview' && $same('heading') && $same('paras')) {
                    $s['heading'] = $c['overview']['heading'];
                    $s['paras'] = $c['overview']['paras'];
                    $s['bullets'] = $c['overview']['bullets'];
                    $s['nav'] = 'Overview';
                } elseif (isset($c['media'][$id]) && $same('heading')) {
                    [$h, $paras, $bullets] = $c['media'][$id];
                    $s['heading'] = $h;
                    if ($same('paras')) $s['paras'] = $paras;
                    if ($same('bullets')) $s['bullets'] = $bullets;
                } elseif (isset($generic[$id]) && $same('heading')) {
                    $s['heading'] = $generic[$id];
                }
                $sections[] = $s;
            }
            $ids = array_column($sections, 'id');
            $insertAfter = function (array $list, string $after, array $new) {
                $at = array_search($after, array_column($list, 'id'), true);
                array_splice($list, $at === false ? count($list) : $at + 1, 0, [$new]);
                return $list;
            };
            if (! in_array('who', $ids, true)) {
                [$h, $intro, $cards] = $c['who'];
                $sections = $insertAfter($sections, 'services', ['type' => 'cards', 'id' => 'who', 'nav' => 'Who we help', 'heading' => $h, 'intro' => $intro,
                    'cards' => array_map(fn ($x) => ['icon' => $x[0], 'title' => $x[1], 'text' => $x[2]], $cards)]);
            }
            if (! in_array('compare', $ids, true)) {
                [$h, $cols, $rows, $note] = $c['compare'];
                $sections = $insertAfter($sections, 'process', ['type' => 'table', 'id' => 'compare', 'nav' => 'Compare', 'heading' => $h, 'columns' => $cols, 'rows' => $rows, 'note' => $note]);
            }
            if (! in_array('pricing', $ids, true)) {
                [$h, $intro, $cards] = $c['cost'];
                $sections = $insertAfter($sections, 'compare', ['type' => 'cards', 'id' => 'pricing', 'nav' => 'Cost', 'heading' => $h, 'intro' => $intro,
                    'cards' => array_map(fn ($x) => ['icon' => $x[0], 'title' => $x[1], 'text' => $x[2]], $cards)]);
            }
            $p->sections = $sections;

            $faqs = (array) $p->faqs;
            $have = array_map('mb_strtolower', array_column($faqs, 'q'));
            foreach ($c['faqs'] as [$q, $a]) {
                if (count($faqs) >= 10) break;
                if (! in_array(mb_strtolower($q), $have, true)) $faqs[] = ['q' => $q, 'a' => $a];
            }
            $p->faqs = $faqs;
            $p->save();

            if ($k = SeoKeyword::query()->find($slug)) {
                $k->sec = array_values(array_unique([...(array) $k->sec, ...$c['sec']]));
                $k->ent = array_values(array_unique([...(array) $k->ent, ...$c['ent']]));
                $k->save();
            }
        }
    }

    /** About (entity details: name, founding year, London base, services) and the Industries page (FAQs). */
    public static function mainPages(): void
    {
        $swap = fn ($cur, $old, $new) => $cur === $old ? $new : $cur;
        if ($p = Page::query()->find('page~about')) {
            $hero = (array) $p->hero;
            $hero['lead'] = $swap($hero['lead'] ?? '', 'GTech Digital is a UK digital agency that provides digital marketing, SEO, Google Ads, social media, web design and development, custom software and branding services, helping businesses get found, win customers and grow revenue.',
                'GTech Digital (Global Tech Digital) is a London-based digital agency, building websites and growing UK businesses since 2014. One team provides digital marketing, SEO, AEO and GEO, Google Ads, social media, web design and development, custom software and branding.');
            $p->hero = $hero;
            $p->sections = array_map(function ($s) use ($swap) {
                if (($s['id'] ?? '') === 'who-we-are') {
                    $s['paras'] = $swap($s['paras'] ?? [], ['GTech Digital is a UK agency that helps businesses grow online. We bring SEO, paid media, social, web design and custom software together in one team, so your marketing, website and systems work as one, and every decision is driven by data.'], [
                        'GTech Digital is a London digital agency that helps businesses grow online. We bring SEO, paid media, social, web design and custom software together in one team, so your marketing, website and systems work as one, and every decision is driven by data.',
                        'We work with start-ups, local businesses, growing SMEs and established brands across the UK and abroad, from our studio on Brick Lane in east London.',
                    ]);
                }
                if (($s['id'] ?? '') === 'story') {
                    $s['heading'] = $swap($s['heading'] ?? '', 'Our Story: From SEO Specialists to Full-Service Agency', 'Our Story: From Websites in 2014 to a Full-Service Agency');
                    $s['paras'] = $swap($s['paras'] ?? [], ['We started by helping local businesses rank on Google. As clients grew, we added paid media, social, web and software, so they could keep everything with one trusted team.'],
                        ['We started in 2014 building websites for local businesses, and soon helped them rank on Google too. As clients grew, we added paid media, social, branding and custom software, so they could keep everything with one trusted team.']);
                    $s['bullets'] = $swap($s['bullets'] ?? [], ['Started in search and web', 'Grew with our clients', 'Now 30+ services', 'Same senior-led approach'], ['Founded in 2014, building websites', 'Grew into search and marketing with our clients', 'Now 30+ services under one roof', 'Same senior-led approach']);
                }
                return $s;
            }, (array) $p->sections);
            $answers = [
                'What does GTech Digital do?' => ['GTech Digital is a UK agency providing digital marketing (SEO, Google Ads, social media and content), web design and development, custom software and branding for businesses of all sizes.',
                    'GTech Digital (Global Tech Digital) is a London-based agency that helps businesses get found and grow: SEO, AEO and GEO, Google Ads and social media for leads and sales, websites and online stores, custom software and apps, and branding, all from one joined-up team.'],
                'Where is GTech Digital based?' => ['We are a UK agency working with businesses across the country, with meetings in person or online.',
                    'GTech Digital is based at 218A Brick Lane, London E1 6SA. We work with businesses across the UK and abroad, and meet clients in person in London or online, whichever suits them.'],
                'What size businesses do you work with?' => ['We work with start-ups, local businesses, growing SMEs and larger brands, with plans to suit each budget.',
                    'We work with start-ups, local businesses, growing SMEs and larger brands. Plans are sized to each budget and goal, and most clients start with a free audit that shows where the quickest wins are.'],
                'Why choose GTech Digital over other agencies?' => ['You get marketing, web and software specialists in one team, a dedicated lead, transparent reporting and rolling monthly terms with no lock-in.',
                    'You get marketing, web and software specialists in one team, a dedicated lead, plain-English monthly reporting on leads and revenue, and rolling monthly terms with no lock-in. You keep ownership of every account, website and piece of data.'],
            ];
            $p->faqs = array_map(fn ($f) => isset($answers[$f['q']]) && $f['a'] === $answers[$f['q']][0] ? ['q' => $f['q'], 'a' => $answers[$f['q']][1]] : $f, (array) $p->faqs);
            $qs = array_column((array) $p->faqs, 'q');
            if (! in_array('When was GTech Digital founded?', $qs, true)) {
                $p->faqs = [...(array) $p->faqs, ['q' => 'When was GTech Digital founded?', 'a' => 'GTech Digital started in 2014, building websites for small businesses in London. It has since grown into a full-service agency offering more than 30 services across digital marketing, web design and development, custom software and branding.']];
            }
            $p->save();
        }
        if (($p = Page::query()->find('page~industries-hub')) && empty($p->faqs)) {
            $p->faqs = [
                ['q' => 'Which industries does GTech Digital work with?', 'a' => 'GTech Digital works with ecommerce brands, hotels and restaurants, estate and letting agents, travel companies, car dealers, B2B companies and SaaS businesses, as well as many local service businesses. Each sector page shows how we approach that market.'],
                ['q' => 'Why choose an agency that knows my industry?', 'a' => 'An agency that knows your sector already understands how your customers buy, which platforms matter (such as Rightmove, AutoTrader or Booking.com) and the rules you work under, from FCA finance adverts to ATOL protection. That means less time explaining and faster results.'],
                ['q' => 'Do you work with industries not listed here?', 'a' => 'Yes. The listed industries are where we have the most experience, but the same approach of research, clear targets and tracked results works in most sectors. Tell us about your business and we will say honestly whether we are the right fit.'],
            ];
            $p->save();
        }
    }
}
