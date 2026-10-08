<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * One email in the Email dashboard: sent by the website or a person (Sent), received over IMAP (Inbox), saved
 * unsent (Drafts) or moved to Trash. Every email the site sends is recorded automatically.
 */
class Email extends Model
{
    public const FOLDERS = ['inbox' => 'Inbox', 'sent' => 'Sent', 'draft' => 'Drafts', 'trash' => 'Trash'];

    protected $fillable = ['folder', 'trashed_from', 'status', 'from_email', 'from_name', 'to', 'cc', 'subject', 'body', 'error', 'message_id', 'lead_id', 'user_id', 'starred', 'read_at', 'sent_at'];

    protected function casts(): array
    {
        return ['starred' => 'boolean', 'read_at' => 'datetime', 'sent_at' => 'datetime'];
    }

    public function lead() { return $this->belongsTo(Lead::class); }
    public function user() { return $this->belongsTo(User::class); }

    /** Emails a user may see: everyone's with "See everyone's", otherwise the ones they sent or wrote, and the inbox. */
    public function scopeVisibleTo(Builder $q, ?User $u): Builder
    {
        if (! $u || ! $u->hasPerm('email.view')) return $q->whereRaw('1 = 0');
        return $u->hasPerm('email.all') ? $q : $q->where(fn ($w) => $w->where('user_id', $u->id)->orWhere(fn ($i) => $i->where('folder', 'inbox')->orWhere('trashed_from', 'inbox')));
    }

    /** Body as plain text, for previews and search. */
    public function snippet(int $len = 120): string
    {
        $t = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags(preg_replace('~<(style|script)\b[\s\S]*?</\1>~i', '', (string) $this->body)))));
        return mb_strlen($t) > $len ? mb_substr($t, 0, $len - 1).'…' : $t;
    }

    public function moveToTrash(): void
    {
        if ($this->folder === 'trash') return;
        $this->forceFill(['trashed_from' => $this->folder, 'folder' => 'trash'])->save();
    }

    public function restore(): void
    {
        if ($this->folder !== 'trash') return;
        $this->forceFill(['folder' => $this->trashed_from ?: 'sent', 'trashed_from' => ''])->save();
    }

    /** "Name <email>" list as an array of addresses. */
    public static function addresses(?string $list): array
    {
        preg_match_all('/[A-Z0-9._%+\'-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', (string) $list, $m);
        return array_values(array_unique(array_map('strtolower', $m[0])));
    }

    /** The lead with this email address, if any (most recent). */
    public static function leadFor(array $addresses): ?int
    {
        return $addresses ? Lead::query()->whereIn('email', $addresses)->latest()->value('id') : null;
    }
}
