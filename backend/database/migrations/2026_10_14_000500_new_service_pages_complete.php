<?php

use Illuminate\Database\Migrations\Migration;

/** UI/UX, Print and AEO & GEO: animated heroes, "On this page" bar, 4 numbers, 6 steps, pricing, reviews and 8 FAQs. */
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
