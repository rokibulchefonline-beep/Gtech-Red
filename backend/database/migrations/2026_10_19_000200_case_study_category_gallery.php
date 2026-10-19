<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Case studies get a category (the filter on /case-studies) and a gallery of extra images. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_studies', function (Blueprint $t) {
            if (! Schema::hasColumn('case_studies', 'category')) $t->string('category', 40)->nullable()->index();
            if (! Schema::hasColumn('case_studies', 'gallery')) $t->json('gallery')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('case_studies', fn (Blueprint $t) => $t->dropColumn(['category', 'gallery']));
    }
};
