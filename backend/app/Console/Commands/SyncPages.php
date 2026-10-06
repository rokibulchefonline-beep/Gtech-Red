<?php

namespace App\Console\Commands;

use App\Models\PageContent;
use App\Support\PageBase;
use Illuminate\Console\Command;

/** Makes sure every editable page has a row in the panel (existing edits are never touched). */
class SyncPages extends Command
{
    protected $signature = 'gtech:sync-pages';

    protected $description = 'List every website page in the Page editor';

    public function handle(): int
    {
        $n = 0;
        foreach (PageBase::all() as $key => $p) {
            $row = PageContent::query()->firstOrCreate(['key' => $key], ['kind' => $p['kind'], 'slug' => $p['slug'], 'hero' => [], 'sections' => [], 'faqs' => []]);
            if ($row->wasRecentlyCreated) $n++;
        }
        $this->info("Pages ready ($n added).");
        return self::SUCCESS;
    }
}
