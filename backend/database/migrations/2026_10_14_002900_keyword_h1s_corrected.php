<?php

use Illuminate\Database\Migrations\Migration;

/** Corrected keyword H1s: no "UK", and clearer grammar on a few pages. Only headings not edited since are changed. */
return new class extends Migration
{
    public function up(): void
    {
        \App\Support\Site\Content\HeadingUpdates::apply();
    }

    public function down(): void {}
};
