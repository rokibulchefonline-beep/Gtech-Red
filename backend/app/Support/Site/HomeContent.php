<?php

namespace App\Support\Site;

use App\Models\CoreService;
use App\Models\Page;

/**
 * The home page rewrite (SEO, AEO and GEO): brand-led title and H1, a one-sentence entity statement, updated service
 * cards and eight FAQs answered in 40-60 words. Runs from the migration (existing installs) and the content seeder.
 */
class HomeContent
{
    public const FAQS = [
        ['q' => 'What does GTech Digital do?', 'a' => 'GTech Digital (Global Tech Digital) is a London-based digital marketing agency, working since 2014. We help businesses get found on Google and in AI answers with SEO, AEO and GEO, win customers with Google Ads and social media, and grow with fast websites, branding and custom software, all from one team.'],
        ['q' => 'Where is GTech Digital based?', 'a' => 'GTech Digital is based at 218A Brick Lane, London E1 6SA. We work with businesses across London and the rest of the UK, in person or remotely with regular video calls, so where you are based makes no difference to the service or the results.'],
        ['q' => 'How much does digital marketing cost in the UK?', 'a' => 'It depends on your goals and channels. Most UK businesses spend from about £500 to £5,000+ per month on agency support, plus any ad budget. We give a fixed quote after a free audit, so you know the cost before you start.'],
        ['q' => 'What is the difference between SEO, AEO and GEO?', 'a' => 'SEO helps your pages rank in Google results. AEO (answer engine optimisation) makes your content the direct answer in snippets and voice search. GEO (generative engine optimisation) gets your brand cited in AI tools such as ChatGPT, Gemini and Google AI Overviews.'],
        ['q' => 'How long does it take to see results?', 'a' => 'Google Ads and social ads can bring enquiries within weeks of launch. SEO, AEO and GEO usually show clear progress in three to six months, then keep compounding. We agree targets at the start and report on them every month.'],
        ['q' => 'Do you work with small businesses?', 'a' => 'Yes. Many of our clients are small and growing UK businesses. We scale the plan to your budget and start with the channels most likely to bring customers quickly, then build from there.'],
        ['q' => 'Do you lock clients into long contracts?', 'a' => 'No. We prefer to keep clients by delivering results, not by contracts. Most services run month to month after an initial setup period, and you own your accounts, website and data.'],
        ['q' => 'How do you report results?', 'a' => 'You get a plain-English monthly report showing leads, sales, rankings, AI visibility and spend, with what we did and what comes next. You can also see your live numbers at any time and talk to your account manager directly.'],
    ];

    /** The first published list of questions (before the address was known), so it can be updated. */
    private const FIRST_FAQS = [['q' => 'What does GTech Digital do?'], ['q' => 'Which areas does GTech Digital cover?'], ['q' => 'How much does digital marketing cost in the UK?'],
        ['q' => 'What is the difference between SEO, AEO and GEO?'], ['q' => 'How long does it take to see results?'], ['q' => 'Do you work with small businesses?'],
        ['q' => 'Do you lock clients into long contracts?'], ['q' => 'How do you report results?']];

    public static function apply(): void
    {
        // The Google Business Profile address (Site settings > Contact), unless one was already entered there.
        $c = (array) (\App\Models\Setting::query()->find('contact')?->value ?? []);
        if (empty($c['street'])) \App\Models\Setting::put('contact', array_merge(\App\Models\Setting::group('contact'), [
            'street' => '218A Brick Lane', 'city' => 'London', 'postcode' => 'E1 6SA', 'country' => 'GB',
            'mapsUrl' => 'https://www.google.com/maps/place/218A+Brick+Ln,+London+E1+6SA,+UK/@51.5245302,-0.0714161,17z',
            'lat' => 51.5245302, 'lng' => -0.0714161, 'showAddress' => true,
        ]));
        $p = Page::query()->find('page~home');
        if (! $p) return;
        // Each text is replaced only while it is still an earlier version written here, so edits in the panel stay.
        $swap = fn ($cur, array $old, $new) => in_array($cur, [...$old, $new], true) ? $new : $cur;
        $who = 'GTech Digital (Global Tech Digital) is a London-based digital agency that has built websites and grown UK businesses since 2014. One team handles search (SEO, AEO and GEO), paid ads, social media, branding, websites, apps and custom software, so every channel works together and is measured on leads and revenue.';
        $sections = array_map(function (array $s) use ($swap, $who) {
            switch ($s['id'] ?? '') {
                case 'who':
                    $s['heading'] = $swap($s['heading'] ?? '', ['A [[Digital Marketing Agency]] Built for Growth'], 'Who Is [[GTech Digital]]?');
                    $s['paras'] = [$swap($s['paras'][0] ?? '', ['GTech Digital is a full-service digital marketing agency specialising in search marketing, advertising, branding, and high-performing websites and software for growth-focused businesses. We turn strategy into measurable revenue.',
                        'GTech Digital is a full-service UK digital agency. One team handles search (SEO, AEO and GEO), paid ads, social media, branding, websites and custom software, so every channel works together and is measured on leads and revenue, not clicks.'], $who)];
                    if (($s['bullets'] ?? []) === ['Strategy, design and engineering under one roof', 'Every campaign tracked to leads and revenue', 'Plain-English reporting, no jargon'])
                        $s['bullets'] = ['One team for marketing, websites and software', 'Every campaign tracked to leads and revenue', 'Plain-English monthly reports, no lock-in contracts'];
                    break;
                case 'services':
                    $s['text'] = $swap($s['text'] ?? '', ['Everything you need to grow online, from one team. Strategy, creative and engineering that work together and are measured on real business results.'],
                        'One team for search, AI visibility, ads, social media, branding, websites and software, measured on real business results.');
                    break;
                case 'inquiry':
                    $s['paras'] = [$swap($s['paras'][0] ?? '', ['Now you know about us, we would love to get to know you better. Why not drop us a message today and introduce yourself? It could be the beginning of a beautiful relationship.'],
                        'Tell us about your business and goals. We will send a free audit and a clear plan with fixed pricing.')];
                    break;
            }
            return $s;
        }, (array) $p->sections);
        $hero = (array) $p->hero;
        // The owner kept the original H1.
        $hero['h1'] = $swap($hero['h1'] ?? '', ['GTech Digital|UK Digital Marketing|[[Agency for Growth]]'], 'Digital Marketing|Agency [[for Scalable]]|[[Growth]]');
        $hero['lead'] = $swap($hero['lead'] ?? '', ['GTech Digital helps UK businesses grow with smart, conversion-focused marketing.',
            'GTech Digital is a UK digital marketing agency that gets businesses found on Google and in AI answers, and grows them with ads, websites and software.'],
            'GTech Digital is a London-based digital marketing agency that gets UK businesses found on Google and in AI answers, and grows them with ads, websites and software.');
        $faqs = (array) $p->faqs;
        $p->forceFill([
            'meta_title' => $swap($p->meta_title, ['GTech Digital | Digital Marketing Agency UK'], 'GTech Digital | UK Digital Marketing, SEO & Web Agency'),
            'meta_description' => $swap($p->meta_description, ['GTech Digital is a UK digital marketing agency growing businesses with SEO, Google Ads, social media, web design and custom software.'],
                'GTech Digital is a UK digital marketing agency for SEO, AEO & GEO, Google Ads, social media, websites and custom software. Free audit, clear monthly reports.'),
            'focus_keyword' => $p->focus_keyword ?: 'digital marketing agency UK',
            'hero' => $hero, 'sections' => $sections,
            // FAQs are added when the page has none, or still has the first version of these.
            'faqs' => ! $faqs || array_column($faqs, 'q') === array_column(self::FIRST_FAQS, 'q') ? self::FAQS : $faqs,
        ]);
        if ($p->isDirty()) $p->save();

        foreach ([
            'search-engine-optimization' => ['Technical SEO audits and fixes', 'Keyword and content strategy', 'AEO & GEO for AI answers', 'Local and ecommerce SEO'],
            'branding' => ['Identity and brand guidelines', 'Positioning and messaging', 'UI/UX design and print media', 'Conversion rate optimisation'],
        ] as $slug => $points) {
            CoreService::query()->where('slug', $slug)->first()?->forceFill(['points' => $points])->save();
        }
        PageCache::flush();
        Repo::flush();
    }
}
