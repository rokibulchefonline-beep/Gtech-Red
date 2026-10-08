<?php

use Illuminate\Database\Migrations\Migration;

/** Home page rewrite: brand-led title and H1, entity statement, updated service cards and eight FAQs. */
return new class extends Migration
{
    public function up(): void
    {
        \App\Support\Site\HomeContent::apply();
    }

    public function down(): void
    {
    }
};
