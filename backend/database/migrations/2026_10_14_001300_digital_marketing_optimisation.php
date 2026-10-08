<?php

use Illuminate\Database\Migrations\Migration;

/** Digital Marketing pages: AEO & GEO keyword targeting and keyword map, internal links, Content Marketing FAQ. */
return new class extends Migration
{
    public function up(): void
    {
        if (! \App\Models\ServiceItem::query()->exists()) return;
        \App\Support\Site\ServiceUpdates::apply();
        \App\Support\Site\DigitalMarketingContent::apply();
    }

    public function down(): void
    {
    }
};
