<?php

namespace App\Support;

use App\Models\Setting;

/** Sign-in rules from Site settings > Security. */
class Security
{
    public static function requiresTwoFactor(?\App\Models\User $user): bool
    {
        if (! $user) return false;
        try {
            $rule = Setting::group('security')['require2fa'] ?? 'off';
        } catch (\Throwable) {
            return false;
        }
        return $rule === 'everyone' || ($rule === 'managers' && $user->hasPerm('users.manage'));
    }
}
