<?php

use Illuminate\Database\Migrations\Migration;

/** Business address 218A Brick Lane, London E1 6SA (Google Business Profile) and the home page copy that mentions it. */
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
