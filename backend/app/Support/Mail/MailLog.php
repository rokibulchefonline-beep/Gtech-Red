<?php

namespace App\Support\Mail;

use App\Models\Email;
use App\Support\SiteMailer;
use Illuminate\Mail\Events\MessageSent;
use Symfony\Component\Mime\Address;

/** Records every email the website sends (lead replies, alerts, password resets, notifications) in the Email dashboard. */
class MailLog
{
    public static function sent(MessageSent $event): void
    {
        try {
            $msg = $event->message;
            $list = fn (array $a) => implode(', ', array_map(fn (Address $x) => $x->toString(), $a));
            $ctx = SiteMailer::$context;
            $to = $list($msg->getTo());
            $from = $msg->getFrom()[0] ?? null;
            Email::query()->create([
                'folder' => 'sent', 'status' => 'sent',
                'from_email' => (string) $from?->getAddress(), 'from_name' => (string) $from?->getName(),
                'to' => $to, 'cc' => $list($msg->getCc()), 'subject' => (string) $msg->getSubject(),
                // A message written in the dashboard keeps what was typed; others keep the email as sent.
                'body' => $ctx['body'] ?? (string) ($msg->getHtmlBody() ?? nl2br(e((string) $msg->getTextBody()))),
                'message_id' => self::id($event),
                'lead_id' => $ctx['lead_id'] ?? Email::leadFor(Email::addresses($to)),
                'user_id' => $ctx['user_id'] ?? auth()->id(),
                'read_at' => now(), 'sent_at' => now(),
            ]);
            if (! empty($ctx['draft_id'])) Email::query()->whereKey($ctx['draft_id'])->where('folder', 'draft')->delete();
        } catch (\Throwable $e) {
            report($e); // never stop an email because of the log
        }
    }

    public static function failed(string $to, ?string $cc, string $subject, string $html, string $error, array $ctx = []): void
    {
        try {
            Email::query()->create(['folder' => 'sent', 'status' => 'failed', 'to' => $to, 'cc' => (string) $cc, 'subject' => $subject, 'body' => $html,
                'error' => mb_substr($error, 0, 2000), 'lead_id' => $ctx['lead_id'] ?? Email::leadFor(Email::addresses($to)),
                'user_id' => $ctx['user_id'] ?? auth()->id(), 'read_at' => now(), 'sent_at' => now()]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private static function id(MessageSent $event): ?string
    {
        try {
            $id = $event->sent->getMessageId();
            return $id && ! Email::query()->where('message_id', $id)->exists() ? mb_substr($id, 0, 300) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
