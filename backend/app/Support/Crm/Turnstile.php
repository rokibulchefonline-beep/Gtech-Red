<?php

namespace App\Support\Crm;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

/** Cloudflare Turnstile ("I am human" check) on the website forms, when keys are set in Site settings > Forms. */
class Turnstile
{
    public static function secret(): string
    {
        $enc = (string) (Setting::group('forms')['turnstileSecret'] ?? '');
        if ($enc === '') return '';
        try { return Crypt::decryptString($enc); } catch (\Throwable) { return ''; }
    }

    public static function enabled(): bool
    {
        return self::secret() !== '' && (string) (Setting::group('forms')['turnstileSite'] ?? '') !== '';
    }

    /** True when Turnstile is off, or Cloudflare confirms the token. If Cloudflare cannot be reached, the form is let through. */
    public static function passes(string $token, ?string $ip): bool
    {
        if (! self::enabled()) return true;
        if ($token === '') return false;
        try {
            $res = Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', ['secret' => self::secret(), 'response' => $token, 'remoteip' => $ip]);
            if (! $res->successful()) return true;
            return (bool) $res->json('success');
        } catch (\Throwable $e) {
            report($e);
            return true;
        }
    }
}
