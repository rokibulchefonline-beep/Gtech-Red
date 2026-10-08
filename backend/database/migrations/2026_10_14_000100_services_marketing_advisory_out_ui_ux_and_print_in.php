<?php

use Illuminate\Database\Migrations\Migration;

/** Marketing Advisory is removed; UI/UX Design and Print Media are added under Branding & Strategy. */
return new class extends Migration
{
    public function up(): void
    {
        // Only for an existing install. On a new one the content seeder loads the services and applies the change itself.
        if (\App\Models\ServiceItem::query()->exists()) \App\Support\Site\ServiceUpdates::apply();
    }

    public function down(): void
    {
        // Content change; restore from a backup if needed.
    }
};
