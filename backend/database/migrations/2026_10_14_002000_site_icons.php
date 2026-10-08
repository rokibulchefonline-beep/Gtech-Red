<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Icons uploaded in the panel (Website content > Icons), used as custom:<slug>. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_icons', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 80)->unique();
            $t->string('name', 120);
            $t->text('body');
            $t->string('viewbox', 80)->default('0 0 24 24');
            $t->boolean('mono')->default(true);
            $t->unsignedInteger('size')->default(0);
            $t->string('uploaded_by', 160)->default('');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_icons');
    }
};
