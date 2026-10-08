<?php

use Illuminate\Database\Migrations\Migration;

/** Services hub: AEO & GEO, UI/UX and print in the copy and cards, eight FAQs. */
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
