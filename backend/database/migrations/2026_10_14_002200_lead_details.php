<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Extra answers from the longer forms (the free audit request): goals, competitors and so on.
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('leads', 'details')) {
            Schema::table('leads', fn (Blueprint $t) => $t->json('details')->nullable());
        }
    }

    public function down(): void
    {
        Schema::table('leads', fn (Blueprint $t) => $t->dropColumn('details'));
    }
};
