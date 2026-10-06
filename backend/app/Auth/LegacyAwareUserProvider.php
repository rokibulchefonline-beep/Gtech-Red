<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Accepts passwords hashed by the old Next.js admin (pbkdf2$<iterations>$<salt>$<hash>, PBKDF2-SHA256) and
 * upgrades them to Laravel's bcrypt on the first successful login, so imported users keep their passwords.
 */
class LegacyAwareUserProvider extends EloquentUserProvider
{
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $plain = (string) ($credentials['password'] ?? '');
        $stored = (string) $user->getAuthPassword();

        if (str_starts_with($stored, 'pbkdf2$')) {
            if (! static::checkPbkdf2($plain, $stored)) return false;
            $user->forceFill(['password' => $this->hasher->make($plain)])->save();
            return true;
        }
        return parent::validateCredentials($user, $credentials);
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        if (str_starts_with((string) $user->getAuthPassword(), 'pbkdf2$')) return;
        parent::rehashPasswordIfRequired($user, $credentials, $force);
    }

    public static function checkPbkdf2(string $plain, string $stored): bool
    {
        $parts = explode('$', $stored);
        if (count($parts) !== 4) return false;
        [, $iter, $salt, $hash] = $parts;
        $want = base64_decode($hash, true);
        $saltBin = base64_decode($salt, true);
        if ($want === false || $saltBin === false || (int) $iter < 1) return false;
        $got = hash_pbkdf2('sha256', $plain, $saltBin, (int) $iter, strlen($want), true);
        return hash_equals($want, $got);
    }
}
