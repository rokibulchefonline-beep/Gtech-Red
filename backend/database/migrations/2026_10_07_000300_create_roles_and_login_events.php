<?php

use App\Support\Permissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Roles edited in the panel (a tick-box grid of permissions), who wrote each post, and a log of sign-ins.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->string('key', 40)->unique();
            $t->string('name', 60);
            $t->string('description', 200)->default('');
            $t->json('perms')->nullable();
            $t->timestamps();
        });
        foreach (Permissions::defaultRoles() as $key => $r) {
            DB::table('roles')->insert(['key' => $key, 'name' => $r['name'], 'description' => $r['description'], 'perms' => json_encode($r['perms']), 'created_at' => now(), 'updated_at' => now()]);
        }

        Schema::table('posts', fn (Blueprint $t) => $t->foreignId('created_by')->nullable()->after('author'));

        Schema::create('login_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->index();
            $t->string('email', 160)->index();
            $t->string('event', 20);            // signed_in, failed, locked, signed_out
            $t->string('ip', 45)->default('');
            $t->string('user_agent', 300)->default('');
            $t->timestamp('at')->index();
        });

        Schema::table('users', function (Blueprint $t) {
            $t->timestamp('last_login_at')->nullable();
            $t->timestamp('invited_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['last_login_at', 'invited_at']));
        Schema::dropIfExists('login_events');
        Schema::table('posts', fn (Blueprint $t) => $t->dropColumn('created_by'));
        Schema::dropIfExists('roles');
    }
};
