<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/** The Industries page intro still listed the removed sectors (healthcare, finance, education). */
return new class extends Migration
{
    public function up(): void
    {
        $p = Page::query()->find('page~industries-hub');
        if (! $p) return;
        $hero = (array) $p->hero;
        $hero['lead'] = str_replace('ecommerce, healthcare, hospitality, property, finance, education, travel, automotive, B2B and SaaS.',
            'ecommerce, hospitality, property, travel, automotive, B2B and SaaS.', (string) ($hero['lead'] ?? ''));
        $p->hero = $hero;
        if ($p->isDirty()) $p->save();
    }

    public function down(): void
    {
    }
};
