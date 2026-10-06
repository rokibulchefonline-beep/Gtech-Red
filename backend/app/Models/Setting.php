<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/** Site settings, one row per group. The SMTP password is encrypted with the Laravel app key. */
class Setting extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    protected function casts(): array
    {
        return ['value' => 'array'];
    }

    public const DEFAULTS = [
        'general' => ['siteName' => 'GTech Digital', 'tagline' => 'Digital marketing, web and software agency', 'siteUrl' => 'https://www.gtechdigital.co.uk'],
        'contact' => ['email' => '', 'phone' => '', 'address' => '', 'hours' => 'Mon to Fri, 9am to 6pm'],
        'socials' => [],
        'tracking' => ['gtmId' => '', 'ga4Id' => '', 'metaPixelId' => ''],
        'seo' => ['titleSuffix' => ' | GTech Digital', 'defaultDescription' => '', 'ogImage' => ''],
        'smtp' => ['host' => '', 'port' => 587, 'secure' => false, 'user' => '', 'pass' => '', 'fromName' => 'GTech Digital', 'fromEmail' => '', 'notifyTo' => '', 'autoReply' => true],
        'publish' => ['deployHook' => ''],
        'forms' => ['budgets' => []],
        'company' => ['legalName' => '', 'number' => '', 'address' => '', 'ico' => ''],
    ];

    /** All groups merged over the defaults. */
    public static function all_(): array
    {
        $rows = static::query()->pluck('value', 'key')->all();
        $out = [];
        foreach (static::DEFAULTS as $k => $def) {
            $v = $rows[$k] ?? null;
            $out[$k] = is_array($def) && array_is_list($def) ? ($v ?? $def) : array_merge($def, is_array($v) ? $v : []);
        }
        return $out;
    }

    public static function group(string $key): array
    {
        return static::all_()[$key] ?? [];
    }

    public static function put(string $key, array $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /** Settings that are safe to send to the public website (no SMTP credentials, no deploy hook). */
    public static function publicView(): array
    {
        $s = static::all_();
        unset($s['smtp'], $s['publish']);
        return $s;
    }

    public static function smtpPassword(): string
    {
        $enc = static::group('smtp')['pass'] ?? '';
        if (! $enc) return '';
        try { return Crypt::decryptString($enc); } catch (\Throwable) { return ''; }
    }
}
