<?php

use Illuminate\Database\Migrations\Migration;

/** Adds the Restaurant Digital Marketing service page under Digital Marketing (after Local SEO in the menu). */
return new class extends Migration
{
    public function up(): void
    {
        if (\App\Models\ServiceItem::query()->exists()) \App\Support\Site\ServiceUpdates::apply();
    }

    public function down(): void {}
};
