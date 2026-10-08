<?php

use Illuminate\Database\Migrations\Migration;

/** Readable H1s across the site, with the keyword and no "UK". */
return new class extends Migration
{
    public function up(): void
    {
        if (! \App\Models\ServiceItem::query()->exists()) return;
        \App\Support\Site\Headings::apply();
    }

    public function down(): void
    {
    }
};
