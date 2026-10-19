<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/** Loads the real client case studies and removes the demo ones (see gtech:load-case-studies). */
return new class extends Migration
{
    public function up(): void
    {
        Artisan::call('gtech:load-case-studies');
    }

    public function down(): void
    {
    }
};
