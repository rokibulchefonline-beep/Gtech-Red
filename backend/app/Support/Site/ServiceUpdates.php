<?php

namespace App\Support\Site;

use App\Filament\Support\PageBlocks;
use App\Models\CaseStudy;
use App\Models\Page;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Revision;
use App\Models\SeoEntry;
use App\Models\SeoKeyword;
use App\Models\ServiceItem;
use App\Models\Testimonial;

/**
 * The service list changes made by the owner: Marketing Advisory is removed (its page, menu entry, keyword entry,
 * links from other pages and case studies; its address redirects to /services), and UI/UX Design and Print Media are
 * added under Branding & Strategy with their own service pages. Idempotent: it runs from the migration (existing
 * installs) and from gtech:seed-content (new installs), so both end in the same state.
 */
class ServiceUpdates
{
    private const REMOVE = ['marketing-advisory'];

    /** New services: the same sections as the other service pages, each with its own pictures and wording. */
    public static function added(): array
    {
        return [
            'ui-ux-design' => [
                'name' => 'UI/UX Design', 'group' => 'branding-strategy', 'icon' => 'lucide:layout-dashboard', 'sort' => 3,
                'blurb' => 'Research-led interfaces that are easy to use and turn visitors into customers.',
                'meta_title' => 'UI/UX Design Agency UK | Website and App Design',
                'meta_description' => 'UK UI/UX design agency for websites and apps. User research, wireframes, prototypes and usability testing that make your product easier to use and convert better.',
                'keyword' => 'UI UX design', 'title' => 'UI/UX Design for Websites and Apps', 'motion' => '/pages/ux/hero.png',
                'highlight' => 'the First Time', 'h1' => 'Designs People Understand [[the First Time]]',
                'lead' => 'We design websites and apps around how real people think and act. We map the journey, prototype the screens and test them with users before a line of code is written.',
                'points' => ['User research and journey mapping', 'Wireframes and clickable prototypes', 'Usability testing with real users'],
                'related' => ['website-design', 'web-application-development', 'conversion-rate-optimization', 'mobile-app-development', 'saas-product-development'],
                'testimonials' => ['Imran K'],
                'industries' => [
                    ['e-commerce', 'Product pages and checkout flows that cut abandoned baskets.'],
                    ['technology-saas', 'Onboarding and dashboards that keep users coming back.'],
                    ['real-estate', 'Property search and enquiry forms that are quick on a phone.'],
                    ['travel', 'Booking journeys that make the next step obvious.'],
                    ['hospitality-hotels', 'Room booking and menu pages that work on any device.'],
                    ['b2b-marketing', 'Demo request and lead forms that people actually finish.'],
                ],
                'faqs' => [
                    ['q' => 'What is the difference between UI and UX design?', 'a' => 'UX design is how a product works: the steps a visitor takes, what they see first and whether they can finish what they came to do. UI design is how it looks and responds: layout, colour, type and the buttons they press. We design both together so the look supports the experience.'],
                    ['q' => 'Do you design for both websites and apps?', 'a' => 'Yes. We design websites, web applications and mobile apps, and we check that each design works on phones, tablets and desktops.'],
                    ['q' => 'How many people do you test with?', 'a' => 'Usually five people per round, and most projects run at least two rounds. Five users reveal most of the common problems in a design, and each round shows whether the fixes worked.'],
                    ['q' => 'Can you improve an existing website or app?', 'a' => 'Yes. We start by seeing how people use what you have now, find the points where they get stuck, and redesign those parts first. Every change is tested before it is built.'],
                    ['q' => 'Will you hand the designs to our developers?', 'a' => 'Yes. You receive the final designs, a design system and specifications the developers can build from. We can also work alongside your developers during the build.'],
                    ['q' => 'How long does a UI/UX project take?', 'a' => 'It depends on the scope. A single landing page can take a few weeks and a full web application several months. We agree a timeline in the proposal before work starts.'],
                ],
                'sections' => [
                    ['type' => 'logos', 'id' => 'clients'],
                    ['type' => 'text', 'id' => 'what-is-ux', 'heading' => 'What is UI/UX design, in [[plain English]]?',
                        'paras' => ['UX design is how a product works: the steps a visitor takes, what they see first, and whether they can finish what they came to do. UI design is how it looks and responds: layout, colour, type and the buttons they press.',
                            'Good design is not decoration. It removes the questions that stop people buying, booking or signing up.'],
                        'bullets' => ['Navigation people understand without help', 'Forms that ask only for what is needed', 'Buttons and messages that say what happens next']],
                    ['type' => 'impact', 'id' => 'in-numbers', 'heading' => 'How we design, in [[numbers]]', 'text' => 'These are the standards every UI/UX project follows.',
                        'stats' => [['value' => '5', 'label' => 'users in each usability test round'], ['value' => '3', 'label' => 'rounds of testing before we build'], ['value' => '100%', 'label' => 'of designs tested with users before development']]],
                    ['type' => 'media', 'id' => 'journey', 'heading' => 'Mapping the journey to find where people drop off', 'image' => '/pages/ux/journey.png',
                        'alt' => 'User journey map with five stages, each with the problem a visitor meets there',
                        'paras' => ['We follow a visitor from the first search result to the enquiry or sale, and note every point where they hesitate, get lost or leave.', 'Each problem becomes a design task with a clear owner and priority.'],
                        'bullets' => ['Personas based on real customer research', 'Stage-by-stage problem list', 'A prioritised list of fixes'], 'flip' => false],
                    ['type' => 'media', 'id' => 'prototype', 'heading' => 'From wireframe to a prototype people can click', 'image' => '/pages/ux/wire.png',
                        'alt' => 'A wireframe layout beside the finished prototype screen', 'tone' => 'grey',
                        'paras' => ['We start with simple wireframes to agree the structure. Then we design a clickable prototype in your brand, so you can see the real experience before any development starts.']],
                    ['type' => 'media', 'id' => 'testing', 'heading' => 'Usability testing: watch real people use it', 'image' => '/pages/ux/test.png',
                        'alt' => 'Usability test results showing where users click and how many finish the task', 'flip' => true,
                        'paras' => ['Five people try the prototype on set tasks while we watch and listen. We record where they click, where they stop and what they say.', 'We fix what they struggle with, then test again.'],
                        'bullets' => ['Task success rate', 'Time to complete each task', 'Where people hesitate']],
                    ['type' => 'media', 'id' => 'system', 'heading' => 'A design system so every screen stays consistent', 'image' => '/pages/ux/system.png',
                        'alt' => 'Design system with brand colours, the type scale, buttons and form fields', 'tone' => 'grey',
                        'paras' => ['Colours, type, buttons and form fields are set up once and reused. Your developers build from the same parts, so the product stays consistent as it grows.']],
                    ['type' => 'cards', 'id' => 'services', 'heading' => 'What is [[included]] in a UI/UX project', 'cards' => [
                        ['icon' => 'lucide:users', 'title' => 'User research', 'text' => 'Interviews, a review of your analytics and the questions your customers ask most.'],
                        ['icon' => 'lucide:map', 'title' => 'Journey mapping', 'text' => 'The stages a visitor goes through, with the points where they hesitate.'],
                        ['icon' => 'lucide:pen-tool', 'title' => 'Wireframes and prototypes', 'text' => 'Structure first, then a clickable prototype in your brand.'],
                        ['icon' => 'lucide:flask-conical', 'title' => 'Usability testing', 'text' => 'Real people try the design, and we change it based on what they do.'],
                        ['icon' => 'lucide:layout-dashboard', 'title' => 'Design system', 'text' => 'Reusable parts and a style guide for your developers.'],
                        ['icon' => 'lucide:book-open', 'title' => 'Handover pack', 'text' => 'Final files and specifications, so the build matches the design.'],
                    ]],
                    ['type' => 'steps', 'id' => 'process', 'heading' => 'Our UI/UX process, [[step by step]]', 'steps' => [
                        ['title' => 'Discover', 'text' => 'Interviews, analytics and a review of what you have now.'],
                        ['title' => 'Map', 'text' => 'Journeys and problems, agreed with you before any screens are drawn.'],
                        ['title' => 'Design', 'text' => 'Wireframes, then a clickable prototype in your brand.'],
                        ['title' => 'Test', 'text' => 'Five users try the prototype and we refine what they struggle with.'],
                        ['title' => 'Hand over', 'text' => 'Final designs, a design system and specifications for your developers.'],
                    ]],
                    ['type' => 'cases', 'id' => 'case-studies', 'heading' => 'Results from our [[design and marketing]] work', 'service' => '*'],
                    ['type' => 'table', 'id' => 'compare', 'heading' => 'UI/UX design, website design or a usability audit: which do you need?',
                        'columns_text' => ' | UI/UX design | Website design | Usability audit',
                        'rows_text' => "Main goal | Make it easy to use and finish tasks | Build a new, good-looking website | Find what is stopping people today\nYou get | Tested prototypes and a design system | A built website | A report with prioritised fixes\nBest when | Launching or redesigning a product | Starting a new site from scratch | Your current site is not converting",
                        'note' => 'Not sure which one you need? Start with a usability audit. It costs less and shows what to fix first.'],
                    ['type' => 'cards', 'id' => 'pricing', 'heading' => 'What shapes the price of [[UI/UX design]]', 'cards' => [
                        ['icon' => 'lucide:layers', 'title' => 'Scope', 'text' => 'How many pages, screens or user journeys we design.'],
                        ['icon' => 'lucide:users', 'title' => 'Research', 'text' => 'Whether we interview users or work from your analytics and feedback.'],
                        ['icon' => 'lucide:flask-conical', 'title' => 'Testing', 'text' => 'How many rounds of testing and how many people take part.'],
                        ['icon' => 'lucide:layout-dashboard', 'title' => 'Design system', 'text' => 'Whether we build a reusable system or design the screens only.'],
                    ]],
                    ['type' => 'reviews', 'id' => 'reviews', 'heading' => 'What our [[clients]] say'],
                    ['type' => 'industries', 'id' => 'industries', 'heading' => 'UI/UX design for your [[sector]]'],
                ],
            ],
            'print-media' => [
                'name' => 'Print Media', 'group' => 'branding-strategy', 'icon' => 'lucide:newspaper', 'sort' => 4,
                'blurb' => 'Brochures, stationery, posters and packaging that look as good in hand as on screen.',
                'meta_title' => 'Print Design Agency UK | Brochures, Stationery and Packaging',
                'meta_description' => 'UK print media design for brochures, flyers, business cards, posters, signage and packaging. Print-ready files, colour-checked and delivered on time.',
                'keyword' => 'print design', 'title' => 'Print Media Design for Your Brand', 'motion' => '/pages/print/hero.png',
                'highlight' => 'Gets Noticed', 'h1' => 'Print Media Design That [[Gets Noticed]]',
                'lead' => 'Brochures, stationery, posters and packaging designed for the place each piece will be used, with print-ready files set up correctly for your printer.',
                'points' => ['Brochures and flyers', 'Business cards and stationery', 'Posters, signage and packaging'],
                'related' => ['branding', 'branding-strategy', 'digital-advertising', 'content-marketing'],
                'testimonials' => ['James T'],
                'industries' => [
                    ['hospitality-hotels', 'Menus, table cards and event posters.'],
                    ['real-estate', 'Property brochures and board-ready signs.'],
                    ['automotive', 'Showroom posters, brochures and offer leaflets.'],
                    ['e-commerce', 'Product packaging and shipping labels.'],
                    ['b2b-marketing', 'Trade show banners and sales sheets.'],
                    ['travel', 'Travel brochures and destination posters.'],
                ],
                'faqs' => [
                    ['q' => 'Do you handle printing as well as design?', 'a' => 'We design the files and can recommend trusted UK printers and help you choose paper and finishes. You can order directly, or we can place the order for you.'],
                    ['q' => 'What file do I need to send to the printer?', 'a' => 'A print-ready PDF at the finished size, with bleed, crop marks and the right colour mode. We prepare this for you, so there are no surprises at the proof stage.'],
                    ['q' => 'Can you match my existing brand?', 'a' => 'Yes. If you have a brand guide we follow it. If not, we start with a short review so your print matches your website and social media.'],
                    ['q' => 'How many proofs will I see?', 'a' => 'Three across a project: a concept, a refined layout, and a final print-ready proof for your approval. Nothing is printed until you have signed off the proof.'],
                    ['q' => 'Can you design packaging for a product that already exists?', 'a' => 'Yes. We take the dimensions from the product or your supplier, build the dieline (the flat plan that folds into the box) and place the artwork on each panel.'],
                    ['q' => 'What is bleed, and why does it matter?', 'a' => 'Bleed is extra colour that runs past the trim line, usually 3 mm. It stops thin white edges showing when the printed sheet is cut.'],
                ],
                'sections' => [
                    ['type' => 'logos', 'id' => 'clients'],
                    ['type' => 'text', 'id' => 'what-is-print', 'heading' => 'What makes print work in [[the real world]]?',
                        'paras' => ['A brochure is read on a train, a poster is seen from across a car park and a box is picked up in a shop. Each one needs its own layout, size and finish.',
                            'We design for the place the print will be used, then prepare the files so the printer delivers exactly what was approved.'],
                        'bullets' => ['A layout that suits the size and how it is held or viewed', 'Colours checked for print, not just a screen', 'Files set up with bleed, crop marks and the right colour mode']],
                    ['type' => 'impact', 'id' => 'in-numbers', 'heading' => 'How we prepare print, in [[numbers]]', 'text' => 'These are the checks every print project goes through.',
                        'stats' => [['value' => '3', 'label' => 'proofs before anything is printed'], ['value' => '3 mm', 'label' => 'bleed on every print-ready file'], ['value' => '100%', 'label' => 'of files checked before they reach the printer']]],
                    ['type' => 'media', 'id' => 'files', 'heading' => 'Print-ready files, set up before they reach the printer', 'image' => '/pages/print/bleed.png',
                        'alt' => 'Print file with the bleed, the trim line and crop marks', 'flip' => true,
                        'paras' => ['Every file is built at the finished size with bleed and crop marks, so the printer trims exactly where it should.', 'We check fonts, images and colours before anything is sent to print.'],
                        'bullets' => ['Bleed and crop marks', 'Images at print resolution', 'Fonts embedded or outlined']],
                    ['type' => 'media', 'id' => 'stationery', 'heading' => 'Stationery that looks like one family', 'image' => '/pages/print/stationery.png',
                        'alt' => 'Letterhead, envelope and business card in the same brand style', 'tone' => 'grey',
                        'paras' => ['Business cards, letterheads and envelopes share one set of colours, fonts and layouts, so your brand looks consistent wherever it appears.']],
                    ['type' => 'media', 'id' => 'poster', 'heading' => 'Posters and signage: one message, read at a [[distance]]', 'image' => '/pages/print/poster.png',
                        'alt' => 'Poster with one headline, a strong colour and a clear grid', 'flip' => true,
                        'paras' => ['We design for the distance people will read from: a headline they can see across the room, one picture and one clear next step.']],
                    ['type' => 'media', 'id' => 'packaging', 'heading' => 'Packaging: from a flat plan to a box on the shelf', 'image' => '/pages/print/pack.png',
                        'alt' => 'Flat packaging plan showing lid, sides, front panel and base, with cut and fold lines', 'tone' => 'grey',
                        'paras' => ['We build the dieline, the flat plan that folds into the box, and place the artwork on each panel, so the box looks right once it is made up.']],
                    ['type' => 'cards', 'id' => 'services', 'heading' => 'What is [[included]] in print design', 'cards' => [
                        ['icon' => 'lucide:file-text', 'title' => 'Brochures and flyers', 'text' => 'Leaflets, catalogues and flyers laid out to be read quickly.'],
                        ['icon' => 'lucide:palette', 'title' => 'Business cards and stationery', 'text' => 'Cards, letterheads and envelopes that match your brand.'],
                        ['icon' => 'lucide:newspaper', 'title' => 'Posters and signage', 'text' => 'Posters, banners and shop signs designed for how far they will be read.'],
                        ['icon' => 'lucide:package', 'title' => 'Packaging design', 'text' => 'Box and label artwork, with the dieline checked before print.'],
                        ['icon' => 'lucide:pen-tool', 'title' => 'Brand consistency', 'text' => 'One look across every printed item and on your website.'],
                        ['icon' => 'lucide:layers', 'title' => 'Print preparation', 'text' => 'Print-ready files, proofs and checks before the order goes in.'],
                    ]],
                    ['type' => 'steps', 'id' => 'process', 'heading' => 'How a print project [[runs]]', 'steps' => [
                        ['title' => 'Brief', 'text' => 'What the piece is for, the quantity, the size and the deadline.'],
                        ['title' => 'Concept', 'text' => 'Two or three design directions to choose from.'],
                        ['title' => 'Refine', 'text' => 'Revisions on the chosen direction, with the copy agreed.'],
                        ['title' => 'Proof', 'text' => 'A print-ready proof for your approval before anything is printed.'],
                        ['title' => 'Deliver', 'text' => 'Print-ready files and a checked order handed to your printer.'],
                    ]],
                    ['type' => 'cases', 'id' => 'case-studies', 'heading' => 'Results from our [[design and marketing]] work', 'service' => '*'],
                    ['type' => 'table', 'id' => 'compare', 'heading' => 'Brochure, poster or packaging: which do you need?',
                        'columns_text' => ' | Brochure | Poster | Packaging',
                        'rows_text' => "Best for | Explaining an offer in detail | Catching attention in a shop or street | Products that sell on a shelf\nTypical size | A4 or A5, folded or flat | A3 up to large format | A flat plan made up into a box\nDesign focus | Layout and reading order | One headline and a strong picture | Structure, labels and the unboxing",
                        'note' => 'Unsure? Tell us where the piece will be seen and we will recommend the format.'],
                    ['type' => 'cards', 'id' => 'pricing', 'heading' => 'What shapes the price of [[print]]', 'cards' => [
                        ['icon' => 'lucide:layers', 'title' => 'Format', 'text' => 'The size, the number of pages or panels, and any folds or die-cuts.'],
                        ['icon' => 'lucide:palette', 'title' => 'Finishes', 'text' => 'Paper choice, coatings, foil or embossing.'],
                        ['icon' => 'lucide:file-text', 'title' => 'Designs', 'text' => 'How many different pieces we design, and how many revisions.'],
                        ['icon' => 'lucide:package', 'title' => 'Quantity', 'text' => 'The print run. Larger runs lower the cost of each piece.'],
                    ]],
                    ['type' => 'reviews', 'id' => 'reviews', 'heading' => 'What our [[clients]] say'],
                    ['type' => 'industries', 'id' => 'industries', 'heading' => 'Print for your [[sector]]'],
                ],
            ],
        ];
    }

    public static function apply(): void
    {
        foreach (self::REMOVE as $slug) self::remove($slug);
        foreach (self::added() as $slug => $s) self::add($slug, $s);
        PageCache::flush();
        Repo::flush();
    }

    private static function add(string $slug, array $s): void
    {
        ServiceItem::query()->updateOrCreate(['slug' => $slug], ['group_slug' => $s['group'], 'name' => $s['name'], 'blurb' => $s['blurb'], 'icon' => $s['icon'], 'sort' => $s['sort']]);
        $path = "/services/$slug";
        Page::query()->updateOrCreate(['key' => "service~$slug"], [
            'kind' => 'service', 'slug' => $slug, 'name' => $s['name'], 'path' => $path, 'sort' => 40, 'published' => true,
            'meta_title' => $s['meta_title'], 'meta_description' => $s['meta_description'], 'focus_keyword' => $s['keyword'],
            'hero' => ['keyword' => $s['keyword'], 'title' => $s['title'], 'highlight' => $s['highlight'], 'lead' => $s['lead'],
                'motion' => $s['motion'], 'points' => $s['points'], 'h1' => $s['h1']],
            'sections' => self::sections($s),
            'faqs' => $s['faqs'], 'related' => $s['related'], 'data' => [],
        ]);
    }

    /** The page's sections in the page builder's format; reviews and industries come from the site's own data. */
    private static function sections(array $s): array
    {
        return PageBlocks::fromBuilder(array_map(function (array $sec) use ($s) {
            $data = $sec;
            if ($sec['type'] === 'reviews') $data['reviews'] = self::reviews($s['testimonials'] ?? []);
            if ($sec['type'] === 'industries') $data['items'] = array_map(fn ($i) => ['slug' => $i[0], 'text' => $i[1]], $s['industries'] ?? []);
            return ['type' => $sec['type'], 'data' => $data];
        }, $s['sections']));
    }

    /** Real client reviews already on the site, by name (never written out here). */
    private static function reviews(array $names): array
    {
        return Testimonial::query()->whereIn('name', $names)->orderBy('sort')->get()
            ->map(fn (Testimonial $t) => ['name' => $t->name, 'role' => '', 'text' => (string) $t->text])->values()->all();
    }

    private static function remove(string $slug): void
    {
        $path = "/services/$slug";
        ServiceItem::query()->where('slug', $slug)->delete();
        $keys = Page::query()->where('key', "service~$slug")->pluck('key');
        Revision::query()->where('model', 'page')->whereIn('model_key', $keys)->delete();
        Page::query()->whereIn('key', $keys)->delete();
        SeoEntry::query()->where('key', SeoEntry::keyFor($path))->delete();
        SeoKeyword::query()->where('slug', $slug)->delete();

        $targets = [$path, $slug];
        foreach (SeoKeyword::query()->get() as $k) {
            $links = array_values(array_filter((array) $k->links, fn ($l) => ! in_array($l['target'] ?? '', $targets, true)));
            if (count($links) !== count((array) $k->links)) { $k->links = $links; $k->save(); }
        }
        foreach (Page::query()->get() as $p) {
            $sections = array_map(function ($s) use ($slug, $path) {
                if (($s['type'] ?? '') === 'cases' && ($s['service'] ?? '') === $slug) $s['service'] = '';
                return self::unlinkDeep($s, [$path]);
            }, (array) $p->sections);
            $related = array_values(array_filter((array) $p->related, fn ($r) => $r !== $slug));
            $hero = self::unlinkDeep((array) $p->hero, [$path]);
            $faqs = self::unlinkDeep((array) $p->faqs, [$path]);
            if ($sections !== (array) $p->sections || $related !== (array) $p->related || $hero !== (array) $p->hero || $faqs !== (array) $p->faqs) {
                $p->forceFill(['sections' => $sections, 'related' => $related, 'hero' => $hero, 'faqs' => $faqs])->save();
            }
        }
        foreach (CaseStudy::query()->get() as $c) {
            $new = ['services' => array_values(array_diff((array) $c->services, [$slug]))];
            foreach (['body', 'challenge', 'solution'] as $f) $new[$f] = self::unlink((string) $c->$f, [$path]);
            $c->forceFill($new);
            if ($c->isDirty()) $c->save();
        }
        foreach (Post::query()->where('body', 'like', "%$path%")->get() as $post) {
            $post->body = self::unlink((string) $post->body, [$path]);
            if ($post->isDirty()) $post->save();
        }
        Redirect::query()->where('to_path', $path)->update(['to_path' => '/services']);
        Redirect::query()->updateOrCreate(['from_path' => $path], ['to_path' => '/services', 'status_code' => 301, 'automatic' => true]);
    }

    /** Links to the removed addresses become plain text (HTML and Markdown links). */
    public static function unlink(string $text, array $paths): string
    {
        if ($text === '' || ! str_contains($text, '/services/')) return $text;
        $alt = implode('|', array_map(fn ($p) => preg_quote($p, '#'), $paths));
        $text = preg_replace('#<a\b[^>]*href\s*=\s*["\'](?:https?://[^/"\']+)?(?:'.$alt.')/?(?:[?\#][^"\']*)?["\'][^>]*>(.*?)</a>#is', '$1', $text);
        return preg_replace('#\[([^\]]*)\]\((?:https?://[^/)]+)?(?:'.$alt.')/?\)#i', '$1', $text);
    }

    private static function unlinkDeep(mixed $v, array $paths): mixed
    {
        if (is_string($v)) return self::unlink($v, $paths);
        if (is_array($v)) return array_map(fn ($x) => self::unlinkDeep($x, $paths), $v);
        return $v;
    }
}
