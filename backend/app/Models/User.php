<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /** Same roles and permissions as the old admin. */
    public const ROLES = [
        'super_admin' => ['label' => 'Super admin', 'perms' => ['content', 'leads', 'settings', 'users']],
        'admin' => ['label' => 'Admin', 'perms' => ['content', 'leads', 'settings']],
        'editor' => ['label' => 'Editor', 'perms' => ['content']],
        'sales' => ['label' => 'Sales', 'perms' => ['leads']],
    ];

    protected $fillable = ['legacy_id', 'name', 'email', 'password', 'role', 'active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'active' => 'boolean'];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->active && isset(static::ROLES[$this->role]);
    }

    public function hasPerm(string $perm): bool
    {
        return in_array($perm, static::ROLES[$this->role]['perms'] ?? [], true);
    }

    public static function roleOptions(): array
    {
        return array_map(fn ($r) => $r['label'], static::ROLES);
    }
}
