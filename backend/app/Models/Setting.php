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
        // Contact details. The address is the Google Business Profile address, written exactly as on the profile (same
        // name, address and phone everywhere helps local rankings); shown on the contact page and in the schema.
        'contact' => ['email' => 'info@gtechdigital.co.uk', 'phone' => '0330 380 1000', 'phone2' => '0203 598 5956', 'address' => '', 'hours' => 'Mon to Fri, 9am to 6pm',
            'street' => '', 'city' => '', 'region' => '', 'postcode' => '', 'country' => 'GB', 'mapsUrl' => '', 'lat' => '', 'lng' => '', 'showAddress' => true, 'showAddressFooter' => false],
        'socials' => [],
        'tracking' => ['gtmId' => 'GTM-NRPJVVSH', 'ga4Id' => '', 'metaPixelId' => ''],
        'seo' => ['titleSuffix' => ' | GTech Digital', 'defaultDescription' => '', 'ogImage' => ''],
        'smtp' => ['host' => '', 'port' => 587, 'secure' => false, 'user' => '', 'pass' => '', 'fromName' => 'GTech Digital', 'fromEmail' => '', 'notifyTo' => '', 'autoReply' => true],
        'publish' => ['deployHook' => ''],
        // Website forms: the privacy notice under each form, and Cloudflare Turnstile spam protection (secret encrypted).
        'forms' => ['budgets' => [], 'privacyNotice' => 'We use your details only to reply to your enquiry. See our [Privacy Policy](/privacy-policy).', 'turnstileSite' => '', 'turnstileSecret' => '', 'recaptchaSite' => '', 'recaptchaSecret' => '', 'recaptchaVersion' => 'v2', 'recaptchaScore' => 0.5],
        'company' => ['legalName' => '', 'number' => '', 'address' => '', 'ico' => ''],
        // Two-factor sign-in: off, managers (people who can manage users) or everyone.
        'security' => ['require2fa' => 'off'],
        // New enquiries: automatic assignment (off or round_robin over the chosen user ids) and a chat webhook.
        'leads' => ['autoAssign' => 'off', 'assignees' => [], 'webhook' => '', 'reminders' => true, 'retainMonths' => 0],
        // Pipeline stages (Leads > Pipeline). "new", "won" and "lost" always exist; the others can be renamed or removed.
        'pipeline' => ['stages' => [
            ['key' => 'new', 'label' => 'New'], ['key' => 'contacted', 'label' => 'Contacted'], ['key' => 'qualified', 'label' => 'Qualified'],
            ['key' => 'proposal', 'label' => 'Proposal sent'], ['key' => 'won', 'label' => 'Won'], ['key' => 'lost', 'label' => 'Lost'],
        ]],
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

    /**
     * Settings that are safe to send to the public website. An allow-list, so a new settings group (or a new secret
     * in an existing one) is private until it is added here.
     */
    public const PUBLIC = [
        'general' => true, 'contact' => true, 'socials' => true, 'tracking' => true, 'seo' => true, 'company' => true,
        'forms' => ['budgets', 'privacyNotice', 'turnstileSite', 'recaptchaSite', 'recaptchaVersion'],
    ];

    public static function publicView(): array
    {
        $out = [];
        foreach (static::all_() as $group => $v) {
            $allow = self::PUBLIC[$group] ?? false;
            if ($allow === true) $out[$group] = $v;
            elseif (is_array($allow) && is_array($v)) $out[$group] = array_intersect_key($v, array_flip($allow));
        }
        return $out;
    }

    public static function smtpPassword(): string
    {
        $enc = static::group('smtp')['pass'] ?? '';
        if (! $enc) return '';
        try { return Crypt::decryptString($enc); } catch (\Throwable) { return ''; }
    }
}
