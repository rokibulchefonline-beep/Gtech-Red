<?php

namespace App\Filament\Support;

/** Resources declare which permission they need: content, leads, settings or users. */
trait Perms
{
    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->hasPerm(static::$perm);
    }
}
