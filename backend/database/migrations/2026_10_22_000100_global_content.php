<?php

use Illuminate\Database\Migrations\Migration;

/** The UK-and-worldwide content rewrite (see App\Support\Site\Content\GlobalContent). */
return new class extends Migration
{
    public function up(): void
    {
        [$done, $kept] = \App\Support\Site\Content\GlobalContent::apply();
        if (app()->runningInConsole() && ! app()->runningUnitTests()) echo "  Content rewrite: $done fields updated, $kept kept because they were edited in the admin.\n";
    }

    public function down(): void
    {
    }
};
