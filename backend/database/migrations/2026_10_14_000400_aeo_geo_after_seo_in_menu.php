<?php

use Illuminate\Database\Migrations\Migration;

/** AEO & GEO shown straight after Search Engine Optimization in the Digital Marketing menu. */
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
