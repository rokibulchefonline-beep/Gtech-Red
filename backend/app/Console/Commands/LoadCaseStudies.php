<?php

namespace App\Console\Commands;

use App\Models\CaseStudy;
use App\Support\Site\PageCache;
use App\Support\Site\Repo;
use Illuminate\Console\Command;

/**
 * Loads the real client case studies (database/data/case-studies.json, images in public/case/) into the database.
 * Case studies are matched by slug, so running it again never duplicates; ones that already exist are left alone
 * (they may have been edited in the admin) unless you pass --force. The demo case studies the site was installed
 * with are removed when they were never edited.
 */
class LoadCaseStudies extends Command
{
    protected $signature = 'gtech:load-case-studies {--force : Overwrite case studies that already exist}';

    protected $description = 'Load the real client case studies, with their images';

    private const OLD = ['chefonline' => 'chefonline-app-development', 'table-booking' => 'tablebookings-web-development', 'arta' => 'arta-web-development', 'salik-and-co' => 'salik-and-co-custom-software'];

    public function handle(): int
    {
        $items = json_decode((string) file_get_contents(database_path('data/case-studies.json')), true) ?: [];
        $added = $updated = $skipped = $removed = 0;

        foreach ($items as $c) {
            $row = CaseStudy::query()->where('slug', $c['slug'])->first();
            if ($row && ! $this->option('force')) { $skipped++; continue; }
            $data = [
                'title' => $c['title'], 'slug' => $c['slug'], 'client' => $c['client'], 'category' => $c['category'], 'industry' => $c['industry'],
                'website' => $c['website'], 'excerpt' => $c['excerpt'], 'image' => $c['image'], 'image_alt' => $c['imageAlt'], 'logo' => $c['logo'],
                'gallery' => $c['gallery'], 'services' => $c['services'], 'metrics' => [], 'challenge' => $c['challenge'], 'solution' => $c['solution'],
                'results' => $c['results'], 'status' => 'published', 'order' => $c['order'],
            ];
            $row ? $row->update($data) : CaseStudy::create($data);
            $row ? $updated++ : $added++;
        }

        // Demo case studies (made-up numbers): removed unless someone renamed them.
        $demo = collect((array) \App\Support\Content::get('demo.caseStudies'))->mapWithKeys(fn ($d) => [$d['slug'] => $d['title']])->all();
        $demo += ['demo-brand-five' => null, 'demo-brand-six' => null];
        foreach (CaseStudy::query()->whereIn('slug', array_keys($demo))->get() as $d) {
            if ($demo[$d->slug] === null || $d->title === $demo[$d->slug]) { $d->delete(); $removed++; }
        }

        // The old demo addresses go to the real case study for the same client.
        foreach (self::OLD as $from => $to) {
            if (! CaseStudy::query()->where('slug', $from)->exists()) \App\Models\Redirect::query()->firstOrCreate(['from_path' => "/case-studies/$from"], ['to_path' => "/case-studies/$to", 'status_code' => 301]);
        }

        PageCache::flush();
        Repo::flush();
        $this->info("Case studies: $added added, $updated updated, $skipped already there (use --force to overwrite)".($removed ? ", $removed demo case studies removed" : '').'.');

        return self::SUCCESS;
    }
}
