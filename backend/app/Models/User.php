<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use \App\Models\Concerns\BlankNotNull;

    use HasFactory, Notifiable, \Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;

    protected $fillable = ['legacy_id', 'name', 'email', 'password', 'role', 'active', 'last_login_at', 'invited_at'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'active' => 'boolean', 'last_login_at' => 'datetime', 'invited_at' => 'datetime'];
    }

    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role', 'key');
    }

    /** Permission keys of this user's role (looked up once per request; super admin has all). */
    public function permissions(): array
    {
        if ($this->role === 'super_admin') return \App\Support\Permissions::all();
        return once(fn () => (array) (Role::query()->where('key', $this->role)->first()?->perms ?? []));
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->active && ($this->role === 'super_admin' || Role::query()->where('key', $this->role)->exists());
    }

    /**
     * "posts.edit" style checks. The old single words (content, leads, settings, users, analytics) still work and
     * mean "can view at least one of those sections".
     */
    public function hasPerm(string $perm): bool
    {
        $mine = $this->permissions();
        if (str_contains($perm, '.')) return in_array($perm, $mine, true);
        foreach (\App\Support\Permissions::LEGACY[$perm] ?? [] as $section) if (in_array("$section.view", $mine, true)) return true;
        return false;
    }

    /** Role key => name, for selects. */
    public static function roleOptions(): array
    {
        return Role::options();
    }
}
