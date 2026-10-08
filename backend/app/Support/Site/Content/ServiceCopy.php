<?php

namespace App\Support\Site\Content;

use App\Models\Page;
use App\Models\SeoKeyword;

/**
 * Service page improvements from the October 2026 research (Ahrefs UK volumes and People Also Ask). Each change
 * names the text it replaces and only applies while that text is unchanged; new FAQs are added once; search terms
 * and entities are merged into the keyword map. Headings are not stuffed with keywords: new wording follows how
 * people search ("bespoke software", "Facebook ads agency", "web development company").
 */
class ServiceCopy
{
    /** slug => [field changes [field, old, new], faqs [[q, a]], sec, ent, keyword map entry for pages without one] */
    public static function all(): array
    {
        return [
            'custom-software-development' => [
                'swap' => [
                    ['section:what-is-custom-software:heading', 'What Is Custom Software Development and How Does It Work?', 'What Is Bespoke Software Development?'],
                    ['section:what-is-custom-software:paras', ['Custom software is built for your exact processes, rather than forcing your team to work around an off-the-shelf tool. It replaces spreadsheets and disconnected systems with one platform that automates the work, gives you live data and grows with you.'],
                        ['Bespoke (or custom) software is built for your exact processes, rather than forcing your team to work around an off-the-shelf tool. It replaces spreadsheets and disconnected systems with one platform that automates the work, gives you live data and grows with you.',
                         'Typical examples are customer portals, booking and quoting systems, job management and field service apps, stock and order systems, and internal dashboards. Because you own the code, you are not tied to per-user licence fees or a vendor\'s roadmap.']],
                ],
                'faqs' => [
                    ['What are examples of bespoke software?', 'Common examples are customer and supplier portals, booking and quoting systems, job and field service management apps, stock and order systems, internal dashboards and SaaS products. GTech Digital has built each of these for UK businesses, usually connected to tools they already use such as Xero, HubSpot or Shopify.'],
                    ['What are the advantages of bespoke software development?', 'Bespoke software fits your process instead of the other way round, removes manual re-typing, connects your systems, scales with you and has no per-user licence fees. You own the code and decide what is built next. The trade-off is a higher upfront cost than an off-the-shelf tool.'],
                    ['How much does bespoke software cost in the UK?', 'Small internal tools and portals typically cost £8,000 to £25,000, business systems with integrations £25,000 to £80,000, and larger platforms more. GTech Digital fixes the price for an agreed first release after a paid discovery, so the cost is clear before development starts.'],
                ],
                'sec' => ['bespoke software development', 'bespoke software company', 'custom software developers'],
                'ent' => ['bespoke software', 'Laravel', 'React', 'REST API', 'agile', 'Xero'],
            ],
            'facebook-marketing' => [
                'swap' => [
                    ['meta_title', 'Facebook Marketing Agency UK | GTech Digital', 'Facebook Ads Agency UK | Facebook Marketing Services'],
                    ['meta_description', 'UK Facebook marketing agency: Facebook ads, lead generation, content and community management with accurate Meta Pixel and Conversions API tracking.',
                        'Facebook ads agency and Facebook marketing for UK businesses: lead and sales campaigns, content and community, with Meta Pixel and Conversions API tracking.'],
                ],
                'faqs' => [
                    ['What does a Facebook ads agency do?', 'A Facebook ads agency plans, builds and optimises campaigns in Meta Ads Manager: audiences, creative, budgets, testing and tracking with the Meta Pixel and Conversions API. GTech Digital also writes the ads, reports cost per lead and sale every month, and keeps the ad account in your name.'],
                    ['Is it worth hiring a Facebook ads agency?', 'It is usually worth it once you spend more than a few hundred pounds a month, because small changes to audiences, creative and tracking have a big effect on cost per result. An agency also brings tested ad formats from other accounts, so you avoid paying to learn the same lessons.'],
                ],
                'sec' => ['facebook ads agency', 'facebook advertising agency', 'meta ads agency'],
                'ent' => ['Meta Ads Manager', 'Advantage+', 'Lookalike Audiences', 'Instagram ads'],
            ],
            'social-media-marketing' => [
                'faqs' => [
                    ['How much does a social media marketing agency cost?', 'Most UK businesses pay £500 to £3,000 a month for social media management, depending on the number of platforms, posts and videos, plus any paid social budget. GTech Digital quotes a fixed monthly fee after a free audit, with content, community management and reporting included.'],
                    ['What is the 5-5-5 rule for social media?', 'The 5-5-5 rule is a simple daily engagement habit: comment on five posts from others, like five posts, and share or reply to five. It helps a business account build relationships and reach, and works alongside a planned content calendar rather than replacing it.'],
                ],
                'sec' => ['social media management services', 'social media agency', 'social media management'],
                'ent' => ['Meta Business Suite', 'Instagram Reels', 'TikTok', 'LinkedIn', 'content calendar', 'social listening'],
            ],
            'web-design-development' => [
                'swap' => [
                    ['meta_title', 'Web Design & Development Agency UK | GTech Digital', 'Web Design & Development Company UK | GTech Digital'],
                ],
                'faqs' => [
                    ['What do web development companies do?', 'A web development company plans, designs, builds and launches websites and web applications, then hosts, maintains and improves them. GTech Digital covers the whole job: UX and design, WordPress, Laravel, Next.js or Shopify development, content migration, technical SEO and ongoing support.'],
                    ['How much does a website cost in the UK?', 'A small business website typically costs £2,500 to £8,000, a larger custom website £8,000 to £25,000, and an online store from £5,000 depending on the platform and integrations. GTech Digital gives a fixed quote after a short discovery call, so you know the full cost before work starts.'],
                ],
                'sec' => ['web development company', 'web design agency', 'website design company'],
                'ent' => ['WordPress', 'Next.js', 'Laravel', 'Shopify', 'Core Web Vitals', 'WCAG'],
            ],
            'ui-ux-design' => [
                'swap' => [
                    ['meta_title', 'UI/UX Design Agency UK | Website and App Design', 'UI/UX Design Agency UK | Research, Prototypes & Testing'],
                    ['meta_description', 'UK UI/UX design agency for websites and apps. User research, wireframes, prototypes and usability testing that make your product easier to use and convert better.',
                        'UI/UX design for websites and apps: user research, wireframes, clickable prototypes and usability testing that make products easier to use and convert better.'],
                    ['lead', 'We design websites and apps around how real people think and act. We map the journey, prototype the screens and test them with users before a line of code is written.',
                        'GTech Digital is a UI/UX design agency that designs websites and apps around how real people think and act: we map the journey, prototype the screens and test them with users before a line of code is written.'],
                ],
                'faqs' => [
                    ['What does a UX agency do?', 'A UX agency researches how people use a website or app, finds where they struggle, and designs and tests a better experience before it is built. User experience design covers journeys, information architecture, wireframes and prototypes; GTech Digital also designs the interface (UI) and hands developers ready-to-build files.'],
                    ['How much do agencies charge for UI/UX design?', 'UK agencies typically charge £650 to £1,000 a day for UI/UX design, so a focused website or app redesign often costs £6,000 to £20,000, and a usability audit from £2,500. GTech Digital fixes the price for an agreed scope after a free call.'],
                ],
                'sec' => ['ux design agency', 'ux agency', 'user experience design'],
                'ent' => ['Figma', 'wireframes', 'prototype', 'usability testing', 'design system', 'user research', 'WCAG'],
                'map' => ['kw' => 'ui ux design agency', 'links' => [
                    ['target' => 'website-design', 'anchor' => 'website design', 'why' => 'Designs built into a full website'],
                    ['target' => 'mobile-app-development', 'anchor' => 'mobile app development', 'why' => 'Prototypes turned into apps'],
                    ['target' => 'conversion-rate-optimization', 'anchor' => 'conversion rate optimisation', 'why' => 'Testing designs against real data'],
                ]],
            ],
            'print-media' => [
                'swap' => [
                    ['meta_title', 'Print Design Agency UK | Brochures, Stationery and Packaging', 'Print Design Services UK | Brochures, Packaging & Signage'],
                    ['meta_description', 'UK print media design for brochures, flyers, business cards, posters, signage and packaging. Print-ready files, colour-checked and delivered on time.',
                        'Print design services for brochures, flyers, business cards, posters, signage and packaging, designed for where they are used, with print-ready files.'],
                    ['lead', 'Brochures, stationery, posters and packaging designed for the place each piece will be used, with print-ready files set up correctly for your printer.',
                        'GTech Digital\'s print design services cover brochures, stationery, posters, signage and packaging, each designed for the place it will be used, with print-ready files set up correctly for your printer.'],
                ],
                'faqs' => [
                    ['What is the difference between print design and graphic design?', 'Graphic design covers all visual communication, on screen and on paper. Print design is the part of graphic design made for physical items, so it also deals with paper stock, finishes, CMYK colour, bleed and how the piece is held or seen at a distance. As a graphic design agency, GTech Digital designs for both, so your print and digital work match.'],
                ],
                'sec' => ['graphic design agency', 'packaging design'],
                'ent' => ['CMYK', 'Pantone', 'bleed', 'PDF/X', 'paper stock', 'dieline'],
                'map' => ['kw' => 'print design services', 'links' => [
                    ['target' => 'branding', 'anchor' => 'brand identity design', 'why' => 'Print that matches the brand'],
                    ['target' => 'branding-strategy', 'anchor' => 'branding and strategy', 'why' => 'Messages that work on paper too'],
                ]],
            ],
            'wordpress-development' => [
                'faqs' => [
                    ['What does a WordPress development agency do?', 'A WordPress development agency designs and builds WordPress websites, custom themes, Gutenberg blocks, plugins and WooCommerce stores, then keeps them fast, secure and updated. GTech Digital builds without heavy page builders, so sites load quickly and are simple for your team to edit.'],
                ],
                'sec' => ['wordpress agency', 'wordpress development agency', 'wordpress developers'],
                'ent' => ['Gutenberg', 'WooCommerce', 'Advanced Custom Fields', 'PHP', 'Core Web Vitals'],
            ],
            'ecommerce-development' => [
                'swap' => [
                    ['meta_title', 'Ecommerce Development Agency UK | Shopify & WooCommerce', 'Ecommerce Development UK | Shopify & WooCommerce Websites'],
                ],
                'sec' => ['ecommerce website development', 'ecommerce web design', 'shopify development'],
                'ent' => ['Shopify', 'WooCommerce', 'Magento', 'Stripe', 'Klarna', 'Google Merchant Center'],
            ],
            'mobile-app-development' => [
                'faqs' => [
                    ['How much does it cost to build an app in the UK?', 'A simple app typically costs £15,000 to £40,000, a business app with accounts, payments and integrations £40,000 to £100,000, and complex platforms more. Cross-platform apps built with Flutter or React Native usually cost less than two separate native apps.'],
                ],
                'sec' => ['app development company', 'app developers uk', 'mobile app developers'],
                'ent' => ['Flutter', 'React Native', 'Swift', 'Kotlin', 'App Store', 'Google Play'],
            ],
            'api-system-integration' => [
                'sec' => ['api integration services', 'system integration company', 'api development'],
                'ent' => ['REST API', 'webhooks', 'OAuth', 'Zapier', 'Make', 'middleware'],
            ],
            'website-maintenance' => [
                'faqs' => [
                    ['How much do website maintenance services cost?', 'UK website maintenance plans typically cost £50 to £150 a month for a small site and £150 to £500 a month for business-critical sites and online stores, depending on updates, monitoring, backups and support hours. GTech Digital offers fixed monthly plans with no long contract.'],
                ],
                'sec' => ['website maintenance packages', 'website support services', 'wordpress maintenance'],
                'ent' => ['SSL certificate', 'uptime monitoring', 'daily backups', 'firewall', 'PHP updates'],
            ],
        ];
    }

    public static function apply(): void
    {
        foreach (self::all() as $slug => $c) {
            $p = Page::query()->find("service~$slug");
            if (! $p) continue;
            $hero = (array) $p->hero;
            $sections = (array) $p->sections;
            foreach ($c['swap'] ?? [] as [$field, $old, $new]) {
                if (in_array($field, ['meta_title', 'meta_description'], true)) {
                    if ($p->{$field} === $old) $p->{$field} = $new;
                } elseif ($field === 'lead') {
                    if (($hero['lead'] ?? null) === $old) $hero['lead'] = $new;
                } elseif (str_starts_with($field, 'section:')) {
                    [, $id, $key] = explode(':', $field);
                    foreach ($sections as $i => $s) if (($s['id'] ?? '') === $id && ($s[$key] ?? null) === $old) $sections[$i][$key] = $new;
                }
            }
            $p->hero = $hero;
            $p->sections = $sections;
            $faqs = (array) $p->faqs;
            $have = array_map('mb_strtolower', array_column($faqs, 'q'));
            foreach ($c['faqs'] ?? [] as [$q, $a]) {
                if (count($faqs) >= 10) break;
                if (! in_array(mb_strtolower($q), $have, true)) $faqs[] = ['q' => $q, 'a' => $a];
            }
            $p->faqs = $faqs;
            $p->save();

            $k = SeoKeyword::query()->find($slug);
            if (! $k && isset($c['map'])) $k = new SeoKeyword(['slug' => $slug, 'kw' => $c['map']['kw'], 'sec' => [], 'ent' => [], 'links' => $c['map']['links']]);
            if ($k) {
                $k->sec = array_values(array_unique([...(array) $k->sec, ...($c['sec'] ?? [])]));
                $k->ent = array_values(array_unique([...(array) $k->ent, ...($c['ent'] ?? [])]));
                $k->save();
            }
        }
    }
}
