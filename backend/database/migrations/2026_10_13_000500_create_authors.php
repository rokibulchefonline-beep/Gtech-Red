<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Blog authors: real people with a bio and profile page (Google looks for who wrote an article). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $t) {
            $t->id();
            $t->string('name', 80)->unique();
            $t->string('slug', 90)->unique();
            $t->string('job_title', 100)->default('');
            $t->text('bio')->nullable();
            $t->string('photo', 500)->default('');
            $t->string('linkedin', 300)->default('');
            $t->string('x', 300)->default('');
            $t->string('website', 300)->default('');
            $t->json('expertise')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
