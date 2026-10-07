<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// First-party website analytics: visits (where people came from), the pages they viewed, and bots reading the site.
// No cookies: a visit is a random id kept in the browser tab; a visitor is a daily hash that cannot be reversed.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_visits', function (Blueprint $t) {
            $t->id();
            $t->char('sid', 32)->unique();
            $t->char('visitor', 16)->index();
            $t->timestamp('started_at')->index();
            $t->timestamp('last_seen_at')->nullable();
            $t->string('landing_path', 300)->index();
            $t->string('channel', 20)->index();        // Search, AI, Social, Referral, Paid, Email, Campaign, Direct
            $t->string('source', 60)->index();         // Google, ChatGPT, LinkedIn, example.com...
            $t->string('referrer_host', 120)->default('');
            $t->string('referrer', 500)->default('');
            $t->string('utm_source', 100)->default('');
            $t->string('utm_medium', 100)->default('');
            $t->string('utm_campaign', 150)->default('')->index();
            $t->string('utm_term', 150)->default('');
            $t->string('utm_content', 150)->default('');
            $t->string('click_id', 10)->default('');   // gclid, msclkid, fbclid...
            $t->string('device', 10)->default('');
            $t->string('browser', 20)->default('');
            $t->char('country', 2)->default('');
            $t->unsignedSmallInteger('pageviews')->default(0);
            $t->unsignedInteger('seconds')->default(0);
            $t->foreignId('lead_id')->nullable()->index();
        });

        Schema::create('analytics_pageviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('visit_id')->index();
            $t->string('path', 300)->index();
            $t->timestamp('viewed_at')->index();
            $t->unsignedInteger('seconds')->default(0);
        });

        Schema::create('analytics_bot_hits', function (Blueprint $t) {
            $t->id();
            $t->string('bot', 40)->index();
            $t->string('kind', 10);                     // ai, search
            $t->string('path', 300)->index();
            $t->timestamp('hit_at')->index();
        });

        Schema::table('leads', function (Blueprint $t) {
            $t->foreignId('visit_id')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('leads', fn (Blueprint $t) => $t->dropColumn('visit_id'));
        Schema::dropIfExists('analytics_bot_hits');
        Schema::dropIfExists('analytics_pageviews');
        Schema::dropIfExists('analytics_visits');
    }
};
