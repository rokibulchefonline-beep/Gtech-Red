<?php

namespace App\Filament\Auth;

use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Sign-in with a record of every attempt (Users and roles shows them) and an account lock: 8 wrong passwords in a
 * row lock that email for 15 minutes, whichever network they come from. Filament's per-IP limit still applies.
 */
class Login extends BaseLogin
{
    public const MAX_FAILURES = 8;
    public const LOCK_MINUTES = 15;

    /** Email with an envelope icon; password with a lock icon and a show/hide eye button. */
    protected function getEmailFormComponent(): \Filament\Forms\Components\Component
    {
        return parent::getEmailFormComponent()->prefixIcon('heroicon-o-envelope');
    }

    protected function getPasswordFormComponent(): \Filament\Forms\Components\Component
    {
        return parent::getPasswordFormComponent()->prefixIcon('heroicon-o-lock-closed')->revealable();
    }

    public function authenticate(): ?LoginResponse
    {
        $email = mb_strtolower(trim((string) ($this->data['email'] ?? '')));
        $key = 'login-fail:'.sha1($email);
        $until = (int) Cache::get($key.':until', 0);
        if ($until > time()) {
            self::log($email, 'locked');
            throw ValidationException::withMessages(['data.email' => 'Too many wrong passwords. This account is locked for '.max(1, (int) ceil(($until - time()) / 60)).' more minutes. You can also reset your password.']);
        }

        try {
            $response = parent::authenticate();
        } catch (ValidationException $e) {
            $n = (int) Cache::get($key, 0) + 1;
            Cache::put($key, $n, now()->addMinutes(self::LOCK_MINUTES));
            self::log($email, 'failed');
            if ($n >= self::MAX_FAILURES) {
                Cache::put($key.':until', time() + self::LOCK_MINUTES * 60, now()->addMinutes(self::LOCK_MINUTES));
                Cache::forget($key);
            }
            throw $e;
        }

        if ($response && ($user = auth()->user())) {
            Cache::forget($key);
            $user->forceFill(['last_login_at' => now()])->saveQuietly();
            self::log($email, 'signed_in', $user->id);
        }
        return $response;
    }

    public static function log(string $email, string $event, ?int $userId = null): void
    {
        try {
            $userId ??= \App\Models\User::query()->where('email', $email)->value('id');
            DB::table('login_events')->insert(['user_id' => $userId, 'email' => mb_substr($email, 0, 160), 'event' => $event,
                'ip' => (string) request()->ip(), 'user_agent' => mb_substr((string) request()->userAgent(), 0, 300), 'at' => now()]);
        } catch (\Throwable) {
            // never block a sign-in because of the log
        }
    }
}
