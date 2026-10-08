<?php

use Illuminate\Database\Migrations\Migration;

/** SEO page focused on SEO only, with a link to the AEO & GEO page. */
return new class extends Migration
{
    public function up(): void
    {
        if (! \App\Models\ServiceItem::query()->exists()) return;
        \App\Support\Site\SeoContent::apply();
    }

    public function down(): void
    {
    }
};
