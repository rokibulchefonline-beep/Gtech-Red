<?php

use Illuminate\Database\Migrations\Migration;

/** Editable sections for the /industries page. */
return new class extends Migration
{
    public function up(): void
    {
        if (! \App\Models\ServiceItem::query()->exists()) return;
        \App\Support\Site\IndustriesHubContent::apply();
    }

    public function down(): void
    {
    }
};
