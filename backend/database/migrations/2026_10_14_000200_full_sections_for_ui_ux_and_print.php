<?php

use Illuminate\Database\Migrations\Migration;

/** UI/UX Design and Print Media get the full set of service page sections, with their own pictures and content. */
return new class extends Migration
{
    public function up(): void
    {
        if (\App\Models\ServiceItem::query()->exists()) \App\Support\Site\ServiceUpdates::apply();
    }

    public function down(): void
    {
        // Content change; restore from a backup if needed.
    }
};
