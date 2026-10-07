<?php

namespace App\Support;

use App\Models\Lead;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

/** Sends email with the SMTP account saved in Settings > Email (falls back to the .env mailer). */
class SiteMailer
{
    public static function configure(): bool
    {
        $s = Setting::group('smtp');
        if (empty($s['host'])) return false;
        config([
            'mail.mailers.site' => [
                'transport' => 'smtp', 'host' => $s['host'], 'port' => (int) ($s['port'] ?? 587),
                'scheme' => ! empty($s['secure']) ? 'smtps' : null,
                'username' => $s['user'] ?? null, 'password' => Setting::smtpPassword(), 'timeout' => 15,
            ],
            'mail.from' => ['address' => $s['fromEmail'] ?: ($s['user'] ?? ''), 'name' => $s['fromName'] ?: 'GTech Digital'],
        ]);
        return true;
    }

    /**
     * Make the panel's SMTP account the default mailer, so Laravel's own emails (password reset, notifications)
     * use it too instead of the .env mailer. Called just before a notification is sent.
     */
    public static function useAsDefault(): void
    {
        try {
            if (self::configure()) {
                config(['mail.default' => 'site']);
                app('mail.manager')->purge('site');
            }
        } catch (\Throwable) {
            // Settings unavailable (e.g. during install): keep the .env mailer.
        }
    }

    public static function send(string $to, string $subject, string $html, ?string $replyTo = null): void
    {
        $mailer = self::configure() ? Mail::mailer('site') : Mail::mailer();
        $mailer->html(self::shell($subject, $html), function ($m) use ($to, $subject, $replyTo) {
            $m->to($to)->subject($subject);
            if ($replyTo) $m->replyTo($replyTo);
        });
    }

    public static function newLead(Lead $lead): void
    {
        $s = Setting::all_();
        $e = fn ($v) => e((string) $v);
        $rows = collect(['Name' => $lead->name, 'Business' => $lead->business, 'Email' => $lead->email, 'Phone' => $lead->phone, 'Service' => $lead->service,
            'Budget' => $lead->budget, 'Website' => $lead->website, 'Message' => $lead->message,
            'Came from' => $lead->originLabel().($lead->utm_campaign ? " · campaign {$lead->utm_campaign}" : ''), 'Landing page' => $lead->landing_path, 'Assigned to' => $lead->owner?->name])
            ->filter()->map(fn ($v, $k) => "<tr><td style=\"padding:6px 12px 6px 0;color:#666\">$k</td><td style=\"padding:6px 0\"><b>{$e($v)}</b></td></tr>")->implode('');
        $to = $s['smtp']['notifyTo'] ?: ($s['contact']['email'] ?? '');
        if ($to) self::send($to, "New lead: {$lead->name} ({$lead->service})", "<table>$rows</table>", $lead->email);
        if (! empty($s['smtp']['autoReply'])) {
            $first = $e(explode(' ', $lead->name)[0]);
            $site = $e($s['general']['siteName'] ?? 'GTech Digital');
            self::send($lead->email, "Thanks, $first. We have your enquiry",
                "<p>Hi $first,</p><p>We have received your enquiry about <b>{$e($lead->service)}</b> and will send your free proposal within one working day.</p><p>$site<br>{$e($s['contact']['phone'] ?? '')}</p>");
        }
    }

    private static function shell(string $title, string $body): string
    {
        return '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;border-top:4px solid #e8202f;padding:24px"><h2 style="margin:0 0 16px;color:#111">'
            .e($title).'</h2>'.$body.'<p style="color:#999;font-size:12px;margin-top:28px">GTech Digital</p></div>';
    }
}
