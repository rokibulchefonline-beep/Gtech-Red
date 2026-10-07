<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A reusable email for replying to leads. {placeholders} are filled in when it is used. */
class EmailTemplate extends Model
{
    public const PLACEHOLDERS = [
        '{first_name}' => 'Their first name', '{name}' => 'Their full name', '{business}' => 'Their business', '{service}' => 'The service they asked about',
        '{my_name}' => 'Your name', '{my_first_name}' => 'Your first name', '{my_email}' => 'Your email', '{company_name}' => 'Company name (Site settings)',
        '{company_phone}' => 'Company phone (Site settings)',
    ];

    protected $fillable = ['name', 'subject', 'body'];

    /** Fills the placeholders for a lead, as the signed-in user. Values are HTML-escaped. */
    public static function render(string $text, Lead $lead, ?User $me, bool $html = true): string
    {
        $s = Setting::all_();
        $vals = [
            '{first_name}' => explode(' ', trim($lead->name))[0] ?? '', '{name}' => $lead->name, '{business}' => $lead->business, '{service}' => $lead->service,
            '{my_name}' => $me?->name ?? '', '{my_first_name}' => explode(' ', trim((string) $me?->name))[0] ?? '', '{my_email}' => $me?->email ?? '',
            '{company_name}' => $s['general']['siteName'] ?? '', '{company_phone}' => $s['contact']['phone'] ?? '',
        ];
        return strtr($text, $html ? array_map(fn ($v) => e((string) $v), $vals) : array_map('strval', $vals));
    }
}
