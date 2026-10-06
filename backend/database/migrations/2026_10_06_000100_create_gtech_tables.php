<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tables for the GTech Digital website. Every table that came from MongoDB keeps the old document id in
// `legacy_id`, so links such as /api/media/<old id> keep working after the import.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role', 20)->default('editor')->after('email');
            $t->boolean('active')->default(true)->after('role');
            $t->string('legacy_id', 64)->nullable()->unique()->after('id');
        });

        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('legacy_id', 64)->nullable()->unique();
            $t->string('name', 60);
            $t->string('slug', 80)->unique();
            $t->timestamps();
        });

        Schema::create('posts', function (Blueprint $t) {
            $t->id();
            $t->string('legacy_id', 64)->nullable()->unique();
            $t->string('title', 160);
            $t->string('slug', 160)->unique();
            $t->string('excerpt', 400)->default('');
            $t->longText('body')->nullable();
            $t->string('format', 10)->default('html');
            $t->string('category', 60)->default('Insights');
            $t->json('categories')->nullable();
            $t->json('tags')->nullable();
            $t->string('post_format', 20)->default('standard');
            $t->string('visibility', 10)->default('public');
            $t->boolean('allow_comments')->default(true);
            $t->boolean('allow_pingbacks')->default(true);
            $t->json('custom_fields')->nullable();
            $t->string('image', 500)->default('');
            $t->string('image_alt', 200)->default('');
            $t->string('author', 80)->default('GTech Editorial Team');
            $t->boolean('featured')->default(false);
            $t->string('status', 12)->default('draft')->index();
            $t->timestamp('date')->nullable();
            $t->string('meta_title', 120)->default('');
            $t->string('meta_description', 300)->default('');
            $t->string('focus_keyword', 80)->default('');
            $t->string('canonical', 500)->default('');
            $t->boolean('noindex')->default(false);
            $t->timestamps();
        });

        Schema::create('case_studies', function (Blueprint $t) {
            $t->id();
            $t->string('legacy_id', 64)->nullable()->unique();
            $t->string('title', 120);
            $t->string('slug', 120)->unique();
            $t->string('client', 120)->default('');
            $t->string('industry', 80)->default('');
            $t->string('duration', 60)->default('');
            $t->string('website', 500)->default('');
            $t->string('excerpt', 300)->default('');
            $t->string('image', 500)->default('');
            $t->string('image_alt', 200)->default('');
            $t->string('logo', 500)->default('');
            $t->json('services')->nullable();
            $t->json('metrics')->nullable();
            $t->text('challenge')->nullable();
            $t->text('solution')->nullable();
            $t->json('results')->nullable();
            $t->json('quote')->nullable();
            $t->longText('body')->nullable();
            $t->string('status', 12)->default('draft')->index();
            $t->integer('order')->default(100);
            $t->string('meta_title', 120)->default('');
            $t->string('meta_description', 300)->default('');
            $t->string('focus_keyword', 80)->default('');
            $t->timestamps();
        });

        foreach (['partners', 'clients'] as $name) {
            Schema::create($name, function (Blueprint $t) {
                $t->id();
                $t->string('legacy_id', 64)->nullable()->unique();
                $t->string('name', 80);
                $t->string('logo', 500)->default('');
                $t->string('url', 500)->default('');
                $t->integer('order')->default(100);
                $t->boolean('visible')->default(true);
                $t->timestamps();
            });
        }

        // Per-page SEO overrides, keyed by the encoded path ("home", "services~seo").
        Schema::create('seo_entries', function (Blueprint $t) {
            $t->string('key', 200)->primary();
            $t->string('path', 200);
            $t->string('title', 120)->default('');
            $t->string('description', 300)->default('');
            $t->string('canonical', 500)->default('');
            $t->string('og_image', 500)->default('');
            $t->boolean('noindex')->default(false);
            $t->string('focus_keyword', 80)->default('');
            $t->boolean('schema_off')->default(false);
            $t->longText('schema_custom')->nullable();
            $t->timestamps();
        });

        Schema::create('leads', function (Blueprint $t) {
            $t->id();
            $t->string('legacy_id', 64)->nullable()->unique();
            $t->string('name', 120);
            $t->string('business', 160)->default('');
            $t->string('email', 160);
            $t->string('phone', 40)->default('');
            $t->string('service', 120)->default('');
            $t->string('budget', 60)->default('');
            $t->string('designation', 80)->default('');
            $t->string('company_size', 40)->default('');
            $t->string('website', 200)->default('');
            $t->string('postcode', 20)->default('');
            $t->text('message')->nullable();
            $t->string('source', 40)->default('contact');
            $t->string('status', 20)->default('new')->index();
            $t->text('notes')->nullable();
            $t->string('assignee', 80)->default('');
            $t->decimal('value', 12, 2)->default(0);
            $t->timestamps();
        });

        Schema::create('subscribers', function (Blueprint $t) {
            $t->id();
            $t->string('email', 160)->unique();
            $t->string('source', 40)->default('blog');
            $t->timestamps();
        });

        // Uploaded images. Files live on disk (storage/app/public/media); the row keeps the metadata.
        Schema::create('media', function (Blueprint $t) {
            $t->id();
            $t->string('legacy_id', 64)->nullable()->unique();
            $t->string('name', 160);
            $t->string('type', 60);
            $t->unsignedInteger('size')->default(0);
            $t->string('path', 300);
            $t->string('uploaded_by', 160)->default('');
            $t->timestamps();
        });

        // Site settings, one row per group (general, contact, socials, tracking, seo, smtp, publish).
        Schema::create('settings', function (Blueprint $t) {
            $t->string('key', 40)->primary();
            $t->json('value')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['settings', 'media', 'subscribers', 'leads', 'seo_entries', 'clients', 'partners', 'case_studies', 'posts', 'categories'] as $t) {
            Schema::dropIfExists($t);
        }
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['role', 'active', 'legacy_id']));
    }
};
