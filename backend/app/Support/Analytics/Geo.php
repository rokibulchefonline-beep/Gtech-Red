<?php

namespace App\Support\Analytics;

use Illuminate\Http\Request;
use MaxMind\Db\Reader;

/**
 * The visitor's country (two-letter code). From the hosting's own header when there is one, otherwise looked up on this server in the free DB-IP country database (gtech:geoip-update). Only the
 * country is kept; the IP address is not stored.
 */
class Geo
{
    private static ?Reader $reader = null;
    private static bool $tried = false;

    public static function path(): string
    {
        return storage_path('app/geo/dbip-country-lite.mmdb');
    }

    public static function country(Request $r): string
    {
        foreach (['X-Country-Code', 'CloudFront-Viewer-Country'] as $h) {
            if ($c = self::clean($r->header($h))) return $c;
        }
        return self::lookup((string) $r->ip());
    }

    public static function lookup(string $ip): string
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return '';
        if (! self::$tried) {
            self::$tried = true;
            try { if (is_file(self::path())) self::$reader = new Reader(self::path()); } catch (\Throwable $e) { report($e); }
        }
        try {
            $rec = self::$reader?->get($ip);
        } catch (\Throwable) {
            return '';
        }
        return self::clean($rec['country']['iso_code'] ?? '');
    }

    /** "GB" -> "🇬🇧 United Kingdom". */
    public static function label(string $code): string
    {
        $code = strtoupper($code);
        if (! preg_match('/^[A-Z]{2}$/', $code)) return $code;
        $flag = mb_chr(0x1F1E6 + ord($code[0]) - 65).mb_chr(0x1F1E6 + ord($code[1]) - 65);
        $name = class_exists(\Locale::class) ? \Locale::getDisplayRegion('-'.$code, 'en') : '';
        return $flag.' '.($name && $name !== $code ? $name : $code);
    }

    public static function reset(): void
    {
        self::$reader = null;
        self::$tried = false;
    }

    private static function clean(?string $c): string
    {
        $c = strtoupper(trim((string) $c));
        return preg_match('/^[A-Z]{2}$/', $c) && ! in_array($c, ['XX', 'T1', 'A1', 'A2', 'O1'], true) ? $c : '';
    }
}
