<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Phase 1 of the Blade move: every piece of website content lives in MySQL and is edited in the panel.
return new class extends Migration
{
    public function up(): void
    {
        // Full content of every page: main pages, services, industries and legal pages.
        Schema::create('pages', function (Blueprint $t) {
            $t->string('key', 120)->primary();          // "service~seo", "industry~finance", "page~home", "legal~terms"
            $t->string('kind', 20)->index();             // page | service | industry | legal
            $t->string('slug', 80);
            $t->string('name', 120);
            $t->string('path', 160)->unique();
            $t->integer('sort')->default(0);
            $t->string('meta_title', 160)->default('');
            $t->string('meta_description', 320)->default('');
            $t->string('focus_keyword', 80)->default('');
            $t->json('hero')->nullable();
            $t->json('sections')->nullable();
            $t->json('faqs')->nullable();
            $t->json('related')->nullable();
            $t->json('data')->nullable();                // anything else (e.g. legal "last updated")
            $t->boolean('published')->default(true);
            $t->timestamps();
        });

        Schema::create('service_groups', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 80)->unique();
            $t->string('title', 120);
            $t->text('intro')->nullable();
            $t->string('icon', 80)->default('');
            $t->integer('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('service_items', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 80)->unique();
            $t->string('group_slug', 80)->index();
            $t->string('name', 120);
            $t->string('blurb', 300)->default('');
            $t->string('icon', 80)->default('');
            $t->integer('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('industries', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 80)->unique();
            $t->string('name', 120);
            $t->string('icon', 80)->default('');
            $t->integer('sort')->default(0);
            $t->timestamps();
        });

        // The service cards on the home page.
        Schema::create('core_services', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 80);
            $t->string('title', 120);
            $t->string('line', 300)->default('');
            $t->json('points')->nullable();
            $t->string('image', 500)->default('');
            $t->integer('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('stats', function (Blueprint $t) {
            $t->id();
            $t->decimal('value', 12, 2);
            $t->string('suffix', 10)->default('');
            $t->string('label', 80);
            $t->unsignedTinyInteger('decimals')->default(0);
            $t->integer('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $t) {
            $t->id();
            $t->string('title', 160);
            $t->string('name', 80);
            $t->text('text');
            $t->boolean('visible')->default(true);
            $t->integer('sort')->default(0);
            $t->timestamps();
        });

        // Keyword map: target keyword, related keywords, entities and contextual internal links per page.
        Schema::create('seo_keywords', function (Blueprint $t) {
            $t->string('slug', 80)->primary();
            $t->string('kw', 160);
            $t->json('sec')->nullable();
            $t->json('ent')->nullable();
            $t->json('links')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['seo_keywords', 'testimonials', 'stats', 'core_services', 'industries', 'service_items', 'service_groups', 'pages'] as $t) Schema::dropIfExists($t);
    }
};
