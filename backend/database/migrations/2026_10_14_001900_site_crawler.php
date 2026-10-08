<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** SEO audit > Site crawl: each crawl of the website, the pages it found and every link on them. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crawl_runs', function (Blueprint $t) {
            $t->id();
            $t->string('status', 12)->default('running');
            $t->string('trigger', 12)->default('manual');
            $t->unsignedInteger('pages')->default(0);
            $t->unsignedInteger('links')->default(0);
            $t->unsignedInteger('broken')->default(0);
            $t->unsignedInteger('errors')->default(0);
            $t->unsignedInteger('redirects')->default(0);
            $t->unsignedInteger('external')->default(0);
            $t->unsignedInteger('seconds')->default(0);
            $t->text('message')->nullable();
            $t->timestamp('started_at')->nullable();
            $t->timestamp('finished_at')->nullable();
            $t->timestamps();
        });
        Schema::create('crawl_pages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('run_id')->index();
            $t->string('path', 500);
            $t->unsignedSmallInteger('status')->default(0);
            $t->string('redirect_to', 500)->default('');
            $t->unsignedInteger('ms')->default(0);
            $t->unsignedInteger('bytes')->default(0);
            $t->string('title', 300)->default('');
            $t->unsignedSmallInteger('h1')->default(0);
            $t->unsignedInteger('words')->default(0);
            $t->boolean('noindex')->default(false);
            $t->string('canonical', 500)->default('');
            $t->unsignedSmallInteger('depth')->default(0);
            $t->string('found_on', 500)->default('');
            $t->unsignedInteger('inbound')->default(0);
            $t->unsignedInteger('out_internal')->default(0);
            $t->unsignedInteger('out_external')->default(0);
            $t->unsignedInteger('broken_links')->default(0);
            $t->index(['run_id', 'status']);
        });
        Schema::create('crawl_links', function (Blueprint $t) {
            $t->id();
            $t->foreignId('run_id')->index();
            $t->string('from_path', 500);
            $t->string('url', 1000);
            $t->string('kind', 10)->default('link');      // link, image
            $t->boolean('internal')->default(true);
            $t->string('anchor', 300)->default('');
            $t->boolean('nofollow')->default(false);
            $t->unsignedSmallInteger('status')->default(0); // 0 = could not connect
            $t->boolean('ok')->default(true);
            $t->string('error', 300)->default('');
            $t->index(['run_id', 'ok']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crawl_links');
        Schema::dropIfExists('crawl_pages');
        Schema::dropIfExists('crawl_runs');
    }
};
