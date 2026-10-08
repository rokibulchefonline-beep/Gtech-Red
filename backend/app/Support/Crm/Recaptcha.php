<?php

namespace App\Support\Crm;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

/**
 * Google reCAPTCHA on the website forms, when keys are set in Site settings > Forms. v2 shows the "I'm not a robot"
 * box; v3 is invisible and gives each send a score from 0 (bot) to 1 (person).
 */
class Recaptcha
{
    public static function secret(): string
    {
        $enc = (string) (Setting::group('forms')['recaptchaSecret'] ?? '');
        if ($enc === '') return '';
        try { return Crypt::decryptString($enc); } catch (\Throwable) { return ''; }
    }

    public static function enabled(): bool
    {
        return self::secret() !== '' && (string) (Setting::group('forms')['recaptchaSite'] ?? '') !== '';
    }

    public static function version(): string
    {
        return (Setting::group('forms')['recaptchaVersion'] ?? 'v2') === 'v3' ? 'v3' : 'v2';
    }

    /** True when reCAPTCHA is off, or Google confirms the token (and, for v3, the score is high enough). If Google cannot be reached, the form is let through. */
    public static function passes(string $token, ?string $ip): bool
    {
        if (! self::enabled()) return true;
        if ($token === '') return false;
        try {
            $res = Http::asForm()->timeout(5)->post('https://www.google.com/recaptcha/api/siteverify', ['secret' => self::secret(), 'response' => $token, 'remoteip' => $ip]);
            if (! $res->successful()) return true;
            if (! $res->json('success')) return false;
            if (self::version() === 'v3') {
                $min = (float) (Setting::group('forms')['recaptchaScore'] ?? 0.5);
                return (float) $res->json('score', 0) >= $min;
            }
            return true;
        } catch (\Throwable $e) {
            report($e);
            return true;
        }
    }
}
