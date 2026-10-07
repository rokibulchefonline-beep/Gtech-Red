<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use \App\Models\Concerns\BlankNotNull;

    protected $fillable = ['key', 'name', 'description', 'perms'];

    protected function casts(): array
    {
        return ['perms' => 'array'];
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role', 'key');
    }

    public function isLocked(): bool
    {
        return $this->key === 'super_admin';
    }

    /** Role key => name, for selects. */
    public static function options(): array
    {
        return static::query()->orderBy('id')->pluck('name', 'key')->all();
    }
}
