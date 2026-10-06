<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Old addresses that send visitors (and search engines) to the new one, e.g. after a post's slug changes.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $t) {
            $t->id();
            $t->string('from_path', 300)->unique();
            $t->string('to_path', 500);
            $t->unsignedSmallInteger('status_code')->default(301);
            $t->boolean('automatic')->default(false);
            $t->unsignedInteger('hits')->default(0);
            $t->timestamp('last_hit_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
    }
};
