<?php

use Illuminate\Database\Migrations\Migration;

/** H1s that lead with each page's primary keyword (only headings still unchanged since launch). */
return new class extends Migration
{
    public function up(): void
    {
        \App\Support\Site\Content\HeadingUpdates::apply();
    }

    public function down(): void {}
};
