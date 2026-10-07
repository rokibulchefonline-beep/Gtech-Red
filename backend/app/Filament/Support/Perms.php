<?php

namespace App\Filament\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Resources declare their section (protected static string $section = 'posts') and get view, create, edit and
 * delete checks from the user's role (Users and roles > Roles).
 */
trait Perms
{
    protected static function allows(string $action): bool
    {
        return (bool) auth()->user()?->hasPerm(static::$section.'.'.$action);
    }

    public static function canViewAny(): bool { return static::allows('view'); }
    public static function canCreate(): bool { return static::allows('create'); }
    public static function canEdit(Model $record): bool { return static::allows('edit'); }
    public static function canDelete(Model $record): bool { return static::allows('delete'); }
    public static function canDeleteAny(): bool { return static::allows('delete'); }
}
