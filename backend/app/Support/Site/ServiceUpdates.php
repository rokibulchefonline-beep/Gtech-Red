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

/**
 * The service list changes made by the owner: Marketing Advisory is removed (its page, menu entry, keyword entry,
 * links from other pages and case studies; its address redirects to /services), and UI/UX Design and Print Media are
 * added under Branding & Strategy with their own service pages. Idempotent: it runs from the migration (existing
 * installs) and from gtech:seed-content (new installs), so both end in the same state.
 */
class ServiceUpdates
{
    private const REMOVE = ['marketing-advisory'];

    /** New services: menu entry plus a service page built from the page builder's sections. */
    public static function added(): array
    {
        return [
            'ui-ux-design' => [
                'name' => 'UI/UX Design', 'group' => 'branding-strategy', 'icon' => 'lucide:layout-dashboard', 'sort' => 3,
                'blurb' => 'Research-led interfaces that are easy to use and turn visitors into customers.',
                'meta_title' => 'UI/UX Design Agency UK | Website and App Design',
                'meta_description' => 'UK UI/UX design agency for websites and apps. User research, wireframes, prototypes and usability testing that make your product easy to use and convert better.',
                'keyword' => 'UI UX design', 'title' => 'UI/UX Design for Websites and Apps',
                'highlight' => 'Customers', 'h1' => 'UI/UX Design That [[Turns Visitors into Customers]]',
                'lead' => 'We design websites and apps around how real people think and act. We research your users, map their journey, test ideas with them and deliver a clear, tested design your developers can build.',
                'points' => ['User research and personas', 'Wireframes and clickable prototypes', 'Usability testing'],
                'related' => ['website-design', 'web-application-development', 'conversion-rate-optimization'],
                'faqs' => [
                    ['q' => 'What is the difference between UI and UX design?', 'a' => 'UX design is how a product works: the journey, structure and tasks people complete. UI design is how it looks and responds: layout, colour, type and interactive details. We design both together so the look supports the experience.'],
                    ['q' => 'Do you design for both websites and apps?', 'a' => 'Yes. We design websites, web applications and mobile apps, and we make sure the design works on phones, tablets and desktops.'],
                    ['q' => 'Can you improve an existing website or app?', 'a' => 'Yes. We start with a review of how people currently use it, find the points where they get stuck, and redesign those parts first, testing each change.'],
                ],
                'sections' => [
                    ['type' => 'text', 'id' => 'why', 'heading' => 'Why good [[design]] pays for itself', 'paras' => [
                        'Most people decide within seconds whether a website or app is worth their time. A clear, easy design keeps them there and helps them act.',
                        'We base every decision on evidence: what your customers need, what stops them and what they expect. Then we test before the build starts, so you pay for fewer changes later.',
                    ], 'bullets' => ['Fewer drop-offs in forms and checkouts', 'Clear navigation that people understand first time', 'Designs your developers can build without guesswork']],
                    ['type' => 'cards', 'id' => 'included', 'heading' => 'What is [[included]]', 'cards' => [
                        ['icon' => 'lucide:users', 'title' => 'User research', 'text' => 'Interviews, surveys and a review of your analytics to understand who uses your product and why.'],
                        ['icon' => 'lucide:map', 'title' => 'User journeys', 'text' => 'The steps people take from first visit to goal, with the points where they hesitate.'],
                        ['icon' => 'lucide:pen-tool', 'title' => 'Wireframes and visual design', 'text' => 'Layouts, then the look and feel, built on a consistent design system.'],
                        ['icon' => 'lucide:flask-conical', 'title' => 'Usability testing', 'text' => 'Real people try the prototype, and we change the design based on what they do.'],
                    ]],
                    ['type' => 'steps', 'id' => 'process', 'heading' => 'Our design [[process]]', 'steps' => [
                        ['title' => 'Discover', 'text' => 'We learn about your users, goals and current results.'],
                        ['title' => 'Design', 'text' => 'We create wireframes and a clickable prototype.'],
                        ['title' => 'Test', 'text' => 'Real users try the prototype and we refine it.'],
                        ['title' => 'Hand over', 'text' => 'You receive finished, documented designs ready to build.'],
                    ]],
                    ['type' => 'cta', 'id' => 'book', 'heading' => 'Ready to make your product [[easier to use]]?', 'text' => 'Book a free design review. We will tell you where users are getting stuck.', 'button' => 'Book a Free Design Review', 'link' => '#inquiry', 'tone' => 'red'],
                ],
            ],
            'print-media' => [
                'name' => 'Print Media', 'group' => 'branding-strategy', 'icon' => 'lucide:newspaper', 'sort' => 4,
                'blurb' => 'Brochures, stationery, posters and packaging that look as good in hand as on screen.',
                'meta_title' => 'Print Design Agency UK | Brochures, Stationery and Packaging',
                'meta_description' => 'UK print media design for brochures, flyers, business cards, posters, signage and packaging. Print-ready files, colour-checked and delivered on time.',
                'keyword' => 'print design', 'title' => 'Print Media Design for Your Brand',
                'highlight' => 'Gets Noticed', 'h1' => 'Print Media Design That [[Gets Noticed]]',
                'lead' => 'From a single flyer to a full range of stationery, we design print that matches your brand, with print-ready files prepared correctly for your printer.',
                'points' => ['Brochures and flyers', 'Business cards and stationery', 'Posters, signage and packaging'],
                'related' => ['branding', 'branding-strategy', 'digital-advertising'],
                'faqs' => [
                    ['q' => 'Do you handle the printing as well as the design?', 'a' => 'We design the files and can recommend trusted UK printers. We prepare everything to the printer\'s specification, so you can order directly or we can place the order for you.'],
                    ['q' => 'What file do I need to send to the printer?', 'a' => 'A print-ready PDF with the right colour mode, bleed and crop marks. We prepare this for you so there are no surprises at the proof stage.'],
                    ['q' => 'Can you match my existing brand?', 'a' => 'Yes. If you have a brand guide we follow it. If not, we start with a short brand review and make sure your print matches your digital presence.'],
                ],
                'sections' => [
                    ['type' => 'text', 'id' => 'why', 'heading' => 'Print that makes a [[lasting impression]]', 'paras' => [
                        'Printed material is still one of the most trusted ways to introduce a business. A well-designed brochure, card or sign tells people you take your work seriously.',
                        'We design each piece to suit how it will be used: held in a hand, pinned to a wall or seen from across the street.',
                    ], 'bullets' => ['Designs that match your website and social media', 'Print-ready files checked before they go to the printer', 'Clear pricing and a proof before anything is printed']],
                    ['type' => 'cards', 'id' => 'products', 'heading' => 'Print we [[design]]', 'cards' => [
                        ['icon' => 'lucide:file-text', 'title' => 'Brochures and flyers', 'text' => 'Leaflets, catalogues and flyers that explain your offer clearly.'],
                        ['icon' => 'lucide:palette', 'title' => 'Business cards and stationery', 'text' => 'Cards, letterheads and envelopes that match your brand.'],
                        ['icon' => 'lucide:newspaper', 'title' => 'Posters and signage', 'text' => 'Eye-catching posters, banners and shop signs.'],
                        ['icon' => 'lucide:package', 'title' => 'Packaging', 'text' => 'Box and label design for products that stand out on the shelf.'],
                    ]],
                    ['type' => 'steps', 'id' => 'process', 'heading' => 'How it [[works]]', 'steps' => [
                        ['title' => 'Brief', 'text' => 'Tell us what you need, how many, and by when.'],
                        ['title' => 'Concept', 'text' => 'We present design directions for your approval.'],
                        ['title' => 'Print-ready files', 'text' => 'We prepare files to your printer\'s specification and send a proof.'],
                        ['title' => 'Delivery', 'text' => 'Your print is checked on delivery, and we help with reprints.'],
                    ]],
                    ['type' => 'cta', 'id' => 'book', 'heading' => 'Need something [[printed]]?', 'text' => 'Tell us what you need and get a fixed quote within one working day.', 'button' => 'Get a Print Quote', 'link' => '#inquiry', 'tone' => 'dark'],
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
                'motion' => '/services/branding.webp', 'points' => $s['points'], 'h1' => $s['h1']],
            'sections' => PageBlocks::fromBuilder(array_map(fn ($sec) => [
                'type' => $sec['type'], 'data' => $sec + ['heading' => $sec['heading'] ?? ''],
            ], $s['sections'])),
            'faqs' => $s['faqs'], 'related' => $s['related'], 'data' => [],
        ]);
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
