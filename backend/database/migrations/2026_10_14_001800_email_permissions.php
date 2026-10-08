<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

/** The new Email dashboard permissions for the roles that already exist. */
return new class extends Migration
{
    public function up(): void
    {
        $add = ['admin' => ['email.view', 'email.all', 'email.send', 'email.delete'], 'sales_manager' => ['email.view', 'email.all', 'email.send', 'email.delete'], 'sales' => ['email.view', 'email.send'], 'viewer' => ['email.view']];
        foreach ($add as $key => $perms) {
            $r = Role::query()->where('key', $key)->first();
            if ($r) { $r->perms = array_values(array_unique([...(array) $r->perms, ...$perms])); $r->save(); }
        }
    }

    public function down(): void
    {
    }
};
