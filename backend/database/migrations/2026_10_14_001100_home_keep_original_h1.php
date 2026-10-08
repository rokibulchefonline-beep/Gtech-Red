<?php

use Illuminate\Database\Migrations\Migration;

/** Home page keeps the original H1: Digital Marketing Agency for Scalable Growth. */
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
