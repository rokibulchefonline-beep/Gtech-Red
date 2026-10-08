<?php

namespace App\Support\Mail;

use App\Models\Email;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Webklex\PHPIMAP\ClientManager;

/**
 * Brings received email into the Email dashboard (Inbox) from the mailbox set in Site settings > Email > Inbox
 * (IMAP). Runs every five minutes and when someone clicks "Check for new mail". Mail is copied, never deleted
 * from the mailbox.
 */
class Inbox
{
    public static function settings(): array
    {
        return Setting::group('imap') + ['enabled' => false, 'host' => '', 'port' => 993, 'encryption' => 'ssl', 'user' => '', 'pass' => '', 'folder' => 'INBOX'];
    }

    public static function enabled(): bool
    {
        $s = self::settings();
        return ! empty($s['enabled']) && $s['host'] !== '' && self::user() !== '' && self::password() !== '';
    }

    private static function user(): string
    {
        return (string) (self::settings()['user'] ?: (Setting::group('smtp')['user'] ?? ''));
    }

    /** The inbox password, or the SMTP password when the same account is used for both. */
    public static function password(): string
    {
        $enc = (string) (self::settings()['pass'] ?? '');
        if ($enc === '') return Setting::smtpPassword();
        try { return Crypt::decryptString($enc); } catch (\Throwable) { return ''; }
    }

    /** Fetches new messages (the last $days days, up to $max). Returns how many were added. */
    public static function fetch(int $days = 14, int $max = 100): int
    {
        if (! self::enabled()) throw new \RuntimeException('The inbox is not set up: add your IMAP details in Site settings > Email.');
        $s = self::settings();
        $client = (new ClientManager())->make([
            'host' => $s['host'], 'port' => (int) $s['port'], 'encryption' => in_array($s['encryption'], ['ssl', 'tls'], true) ? $s['encryption'] : false,
            'validate_cert' => true, 'username' => self::user(), 'password' => self::password(), 'protocol' => 'imap', 'timeout' => 20,
        ]);
        $client->connect();
        $folder = $client->getFolderByPath($s['folder'] ?: 'INBOX') ?? $client->getFolder('INBOX');
        $messages = $folder->query()->since(now()->subDays($days))->leaveUnread()->setFetchOrder('desc')->limit($max)->get();
        $added = 0;
        foreach ($messages as $m) {
            $id = mb_substr(trim((string) $m->getMessageId()), 0, 300) ?: 'imap-'.sha1($m->getUid().'|'.$m->getSubject().'|'.$m->getDate());
            if (Email::query()->where('message_id', $id)->exists()) continue;
            $from = $m->getFrom()[0] ?? null;
            $addr = fn ($list) => collect($list?->all() ?? [])->map(fn ($a) => trim(($a->personal ? $a->personal.' ' : '').'<'.$a->mail.'>'))->implode(', ');
            $html = $m->hasHTMLBody() ? (string) $m->getHTMLBody() : nl2br(e((string) $m->getTextBody()));
            $fromEmail = strtolower((string) $from?->mail);
            Email::query()->create([
                'folder' => 'inbox', 'status' => 'received', 'from_email' => $fromEmail, 'from_name' => (string) $from?->personal,
                'to' => $addr($m->getTo()), 'cc' => $addr($m->getCc()), 'subject' => mb_substr((string) $m->getSubject(), 0, 300),
                'body' => $html, 'message_id' => $id, 'lead_id' => Email::leadFor([$fromEmail]),
                'sent_at' => $m->getDate()?->toDate() ?? now(),
            ]);
            $added++;
        }
        $client->disconnect();
        Setting::put('imap', ['lastChecked' => now()->toIso8601String()] + Setting::group('imap'));
        return $added;
    }
}
