<?php

use App\Support\Site\RemoveIndustry;
use Illuminate\Database\Migrations\Migration;

/** The Education, Healthcare and Finance industries are no longer offered: their pages and every link to them go. */
return new class extends Migration
{
    public function up(): void
    {
        RemoveIndustry::run(['education', 'healthcare', 'finance']);
    }

    public function down(): void
    {
        // Content removal; restore from a backup if needed.
    }
};
