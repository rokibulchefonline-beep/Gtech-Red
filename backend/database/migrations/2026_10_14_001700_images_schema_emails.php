<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Image alt text and captions, blog image captions, site-wide schema rules and the Email dashboard. */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('media', 'alt')) Schema::table('media', function (Blueprint $t) {
            $t->string('alt', 200)->default('');
            $t->string('caption', 250)->default('');
        });
        if (! Schema::hasColumn('posts', 'image_caption')) Schema::table('posts', fn (Blueprint $t) => $t->string('image_caption', 250)->default(''));

        if (! Schema::hasTable('schema_rules')) Schema::create('schema_rules', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);
            $t->string('scope', 40)->default('all');
            $t->string('path', 300)->default('');
            $t->text('json');
            $t->boolean('active')->default(true);
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        if (! Schema::hasTable('emails')) Schema::create('emails', function (Blueprint $t) {
            $t->id();
            $t->string('folder', 12)->default('sent')->index();   // inbox, sent, draft, trash
            $t->string('trashed_from', 12)->default('');
            $t->string('status', 12)->default('sent');             // sent, failed, received, draft
            $t->string('from_email', 190)->default('');
            $t->string('from_name', 190)->default('');
            $t->text('to');
            $t->text('cc')->nullable();
            $t->string('subject', 300)->default('');
            $t->longText('body')->nullable();
            $t->text('error')->nullable();
            $t->string('message_id', 300)->nullable()->unique();
            $t->foreignId('lead_id')->nullable()->index();
            $t->foreignId('user_id')->nullable();
            $t->boolean('starred')->default(false);
            $t->timestamp('read_at')->nullable();
            $t->timestamp('sent_at')->nullable()->index();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emails');
        Schema::dropIfExists('schema_rules');
    }
};
