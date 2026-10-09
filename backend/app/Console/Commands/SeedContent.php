<?php

namespace App\Console\Commands;

use App\Models\CaseStudy;
use App\Models\Client;
use App\Models\CoreService;
use App\Models\Industry;
use App\Models\Page;
use App\Models\Partner;
use App\Models\Post;
use App\Models\SeoKeyword;
use App\Models\ServiceGroup;
use App\Models\ServiceItem;
use App\Models\Setting;
use App\Models\Stat;
use App\Models\Testimonial;
use App\Support\Content;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Loads the website content (exported from the old code into database/data/content.json) into MySQL.
 * Never overwrites anything that already exists unless you pass --force.
 */
class SeedContent extends Command
{
    protected $signature = 'gtech:seed-content {--force : Replace existing content with the exported copy} {--no-demo : Skip the demo posts, case studies and logos}';

    protected $description = 'Load all website pages and site content into the database';

    public function handle(): int
    {
        if (! Content::all()) {
            $this->error('database/data/content.json is missing. Run "npm run export:content" in the website folder.');
            return self::FAILURE;
        }
        $force = (bool) $this->option('force');
        $rows = [];

        DB::transaction(function () use ($force, &$rows) {
            $added = 0; $kept = 0;
            foreach (Content::get('pages') as $p) {
                $exists = Page::query()->whereKey($p['key'])->exists();
                if ($exists && ! $force) { $kept++; continue; }
                Page::query()->updateOrCreate(['key' => $p['key']], [
                    'kind' => $p['kind'], 'slug' => $p['slug'], 'name' => $p['name'], 'path' => $p['path'], 'sort' => $p['sort'],
                    'meta_title' => $p['metaTitle'] ?? '', 'meta_description' => $p['metaDescription'] ?? '', 'hero' => $p['hero'], 'sections' => $p['sections'],
                    'faqs' => $p['faqs'], 'related' => $p['related'] ?? [], 'data' => $p['data'] ?? [],
                ]);
                $added++;
            }
            $rows[] = ['Pages', $added, $kept];

            $rows[] = $this->table_('Service groups', ServiceGroup::class, Content::get('serviceGroups'), fn ($g) => ['slug' => $g['slug'], 'title' => $g['title'], 'intro' => $g['intro'], 'icon' => $g['icon'], 'sort' => $g['sort']], $force);
            $rows[] = $this->table_('Services', ServiceItem::class, Content::get('serviceItems'), fn ($s) => ['slug' => $s['slug'], 'group_slug' => $s['group'], 'name' => $s['name'], 'blurb' => $s['blurb'], 'icon' => $s['icon'], 'sort' => $s['sort']], $force);
            $rows[] = $this->table_('Industries', Industry::class, Content::get('industries'), fn ($s) => $s, $force);
            $rows[] = $this->table_('Home service cards', CoreService::class, Content::get('coreServices'), fn ($c) => ['slug' => $c['slug'], 'title' => $c['title'], 'line' => $c['line'], 'points' => $c['points'], 'image' => $c['image'] ?? '', 'sort' => $c['sort']], $force);
            $rows[] = $this->table_('Stats', Stat::class, Content::get('stats'), fn ($s) => $s, $force);
            $rows[] = $this->table_('Testimonials', Testimonial::class, Content::get('testimonials'), fn ($t) => ['title' => $t['title'], 'name' => $t['name'], 'text' => $t['text'], 'sort' => $t['sort']], $force);
            $rows[] = $this->table_('SEO keyword map', SeoKeyword::class, Content::get('seoMap'), fn ($k) => $k, $force);

            // Settings groups that the old code kept in files.
            foreach (['forms', 'company', 'general', 'contact', 'socials'] as $g) {
                $has = Setting::query()->whereKey($g)->exists();
                if (! $has || ($force && in_array($g, ['forms', 'company'], true))) Setting::put($g, Content::get("settings.$g"));
            }

            if (! $this->option('no-demo')) {
                $rows[] = $this->demo('Demo blog posts', Post::class, Content::get('demo.posts'), fn ($p) => [
                    'title' => $p['title'], 'slug' => $p['slug'], 'excerpt' => $p['excerpt'] ?? '', 'body' => $p['body'] ?? '', 'format' => $p['format'] ?? 'md',
                    'category' => $p['category'] ?? 'Insights', 'categories' => [$p['category'] ?? 'Insights'], 'tags' => $p['tags'] ?? [], 'image' => $p['image'] ?? '',
                    'image_alt' => $p['imageAlt'] ?? '', 'author' => $p['author'] ?? 'GTech Editorial Team', 'featured' => (bool) ($p['featured'] ?? false),
                    'status' => 'published', 'date' => $p['date'] ?? now(), 'meta_title' => $p['metaTitle'] ?? '',
                ]);
                $rows[] = $this->demo('Demo case studies', CaseStudy::class, Content::get('demo.caseStudies'), fn ($c) => [
                    'title' => $c['title'], 'slug' => $c['slug'], 'client' => $c['client'] ?? $c['title'], 'industry' => $c['industry'] ?? '', 'duration' => $c['duration'] ?? '',
                    'excerpt' => $c['excerpt'] ?? '', 'image' => $c['image'] ?? '', 'services' => $c['services'] ?? [], 'metrics' => $c['metrics'] ?? [],
                    'challenge' => $c['challenge'] ?? '', 'solution' => $c['solution'] ?? '', 'results' => $c['results'] ?? [], 'quote' => $c['quote'] ?? null,
                    'body' => $c['body'] ?? '', 'status' => 'published', 'order' => 100,
                ]);
                $rows[] = $this->demo('Partner badges', Partner::class, Content::get('demo.partners'), fn ($l, $i) => ['name' => $l['name'], 'logo' => $l['logo'], 'url' => $l['url'] ?? '', 'order' => ($i + 1) * 10, 'visible' => true]);
                $rows[] = $this->demo('Client logos', Client::class, Content::get('demo.clients'), fn ($l, $i) => ['name' => $l['name'], 'logo' => $l['logo'], 'url' => $l['url'] ?? '', 'order' => ($i + 1) * 10, 'visible' => true]);
            }
        });

        $this->table(['Content', 'Loaded', 'Kept (already there)'], $rows);
        // The service changes (see ServiceUpdates) apply to a fresh install too.
        \App\Support\Site\ServiceUpdates::apply();
        \App\Support\Site\HomeContent::apply();
        \App\Support\Site\DigitalMarketingContent::apply();
        \App\Support\Site\SeoContent::apply();
        \App\Support\Site\Headings::apply();
        \App\Support\Site\IndustriesHubContent::apply();
        \App\Support\Site\Content\ContentReview::apply();
        \App\Support\Site\Content\HeadingUpdates::apply();
        if (! in_array('Changes to These Terms', array_column((array) Page::query()->find('legal~terms')?->sections, 'heading'), true)) \App\Support\Site\LegalContent::apply();
        return self::SUCCESS;
    }

    /** Lists: loaded when the table is empty (or --force replaces them). */
    private function table_(string $label, string $class, array $items, callable $map, bool $force): array
    {
        if ($class::query()->exists() && ! $force) return [$label, 0, $class::query()->count()];
        $class::query()->delete();
        foreach ($items as $i => $it) $class::query()->create($map($it, $i));
        return [$label, count($items), 0];
    }

    /** Demo content only fills empty tables, exactly like the old site showed demos until real content existed. */
    private function demo(string $label, string $class, array $items, callable $map): array
    {
        if ($class::query()->exists()) return [$label, 0, $class::query()->count()];
        foreach ($items as $i => $it) $class::query()->create($map($it, $i));
        return [$label, count($items), 0];
    }
}
