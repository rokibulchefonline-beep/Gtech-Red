<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Page builder: unpublished drafts and scheduled publishing for pages, and a version history for pages, blog posts
// and case studies.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $t) {
            $t->json('draft')->nullable();              // changes saved but not live yet (same shape as the page row)
            $t->foreignId('draft_by')->nullable();
            $t->timestamp('draft_at')->nullable();
            $t->timestamp('publish_at')->nullable()->index(); // when the draft (or a new, unpublished page) goes live
        });

        Schema::create('revisions', function (Blueprint $t) {
            $t->id();
            $t->string('model', 40);                    // page, post, case_study
            $t->string('model_key', 120);
            $t->foreignId('user_id')->nullable();
            $t->string('label', 60)->default('');       // Published, Restored, Scheduled publish...
            $t->json('data');
            $t->timestamp('created_at')->nullable();
            $t->index(['model', 'model_key', 'id']);
        });

        // New page permissions: whoever could edit pages keeps publishing straight away; roles that could also
        // write blog posts (admin, editor) may create and delete landing pages.
        foreach (\Illuminate\Support\Facades\DB::table('roles')->get() as $r) {
            $perms = json_decode($r->perms ?: '[]', true) ?: [];
            if (! in_array('pages.edit', $perms, true)) continue;
            $add = ['pages.publish'];
            if (in_array('posts.publish', $perms, true)) array_push($add, 'pages.create', 'pages.delete');
            \Illuminate\Support\Facades\DB::table('roles')->where('id', $r->id)->update(['perms' => json_encode(array_values(array_unique([...$perms, ...$add])))]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('revisions');
        Schema::table('pages', fn (Blueprint $t) => $t->dropColumn(['draft', 'draft_by', 'draft_at', 'publish_at']));
    }
};
