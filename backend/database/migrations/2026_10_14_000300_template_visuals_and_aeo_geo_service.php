<?php

use Illuminate\Database\Migrations\Migration;

/** UI/UX and Print visuals in the site's template style, and the new AEO & GEO service. */
return new class extends Migration
{
    public function up(): void
    {
        if (\App\Models\ServiceItem::query()->exists()) \App\Support\Site\ServiceUpdates::apply();
    }

    public function down(): void
    {
    }
};
