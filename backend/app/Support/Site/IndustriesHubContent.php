<?php

namespace App\Support\Site;

use App\Models\Page;

/**
 * Gives the /industries page editable sections: the "how we work" cards and the heading above the industry cards.
 * Added only when missing, so edits made in the panel stay.
 */
class IndustriesHubContent
{
    public static function apply(): void
    {
        $p = Page::query()->find('page~industries-hub');
        if (! $p) return;
        $ids = array_column((array) $p->sections, 'id');
        $add = [];
        if (! in_array('how', $ids, true)) $add[] = ['type' => 'cards', 'id' => 'how', 'nav' => 'How we work', 'heading' => 'How we work with every sector', 'cards' => [
            ['icon' => 'lucide:search', 'title' => 'Sector research', 'text' => 'We study your market, buyers and competitors first.'],
            ['icon' => 'lucide:shield-check', 'title' => 'Compliance-aware', 'text' => 'Campaigns that respect the rules of your industry.'],
            ['icon' => 'lucide:trending-up', 'title' => 'Tracked to revenue', 'text' => 'Every channel measured against leads and sales.'],
        ]];
        if (! in_array('sectors', $ids, true)) $add[] = ['type' => 'text', 'id' => 'sectors', 'nav' => 'Industries', 'heading' => 'Industries [[We Serve]]',
            'intro' => 'GTech Digital builds marketing around how each sector buys, from hotels and travel to SaaS, property and B2B.'];
        if (! $add) return;
        $p->sections = [...(array) $p->sections, ...$add];
        $p->save();
        PageCache::flush();
    }
}
