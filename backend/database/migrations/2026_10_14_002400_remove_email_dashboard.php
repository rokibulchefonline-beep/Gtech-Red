<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// The Email dashboard was removed: drop its saved IMAP login and its role permissions. The sent-email log stays.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->where('key', 'imap')->delete();
        foreach (Role::query()->get() as $r) {
            $perms = array_values(array_filter((array) $r->perms, fn ($p) => ! str_starts_with((string) $p, 'email.')));
            if ($perms !== array_values((array) $r->perms)) { $r->perms = $perms; $r->save(); }
        }
    }

    public function down(): void {}
};
