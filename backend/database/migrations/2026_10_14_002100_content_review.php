<?php

use Illuminate\Database\Migrations\Migration;

/** October 2026 content review: researched copy for the industry pages and service page improvements. */
return new class extends Migration
{
    public function up(): void
    {
        if (! \App\Models\ServiceItem::query()->exists()) return;
        \App\Support\Site\Headings::apply();
        \App\Support\Site\Content\ContentReview::apply();
    }

    public function down(): void
    {
    }
};
