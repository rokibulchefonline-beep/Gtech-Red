<?php

use Illuminate\Database\Migrations\Migration;

/** Home page: Global Tech Digital since 2014, in the copy and the schema. */
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
