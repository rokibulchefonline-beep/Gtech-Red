<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use \App\Models\Concerns\BlankNotNull;

    /** Stages that always exist (the others are set in Site settings > Leads). */
    public const FIXED = ['new' => 'New', 'won' => 'Won', 'lost' => 'Lost'];

    /** Statuses that need no more follow-up. */
    public const CLOSED = ['won', 'lost'];

    /** Pipeline stages in order: key => label. */
    public static function statuses(): array
    {
        return once(function () {
            $out = [];
            foreach ((array) (Setting::group('pipeline')['stages'] ?? []) as $s) if (! empty($s['key'])) $out[$s['key']] = ($s['label'] ?? '') ?: ucfirst($s['key']);
            // The fixed stages are always there: New first, Won and Lost last.
            return ['new' => $out['new'] ?? 'New'] + array_diff_key($out, self::FIXED) + ['won' => $out['won'] ?? 'Won', 'lost' => $out['lost'] ?? 'Lost'];
        });
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? ucfirst((string) $this->status);
    }

    protected $table = 'leads';

    protected $fillable = ['legacy_id','name','business','email','phone','service','budget','designation','company_size','website','postcode','message','source','status','notes','assignee','value',
        'assigned_to','next_action_at','next_action','channel','origin','landing_path','form_path','utm_campaign','contact_id','consent_text','ip'];

    protected function casts(): array
    {
        return ['value' => 'decimal:2', 'next_action_at' => 'datetime', 'first_contacted_at' => 'datetime'];
    }

    public function owner() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function contact() { return $this->belongsTo(Contact::class); }
    public function activities() { return $this->hasMany(LeadActivity::class)->latest('created_at')->latest('id'); }
    /** Calls, emails and meetings: each time someone followed the lead up. */
    public function followUps() { return $this->hasMany(LeadActivity::class)->whereIn('type', LeadActivity::FOLLOW_UPS); }

    /** Leads a user may see: everyone's with "See everyone's leads", otherwise only the ones assigned to them. */
    public function scopeVisibleTo(Builder $q, ?User $user): Builder
    {
        if (! $user) return $q->whereRaw('1 = 0');
        return $user->hasPerm('leads.all') ? $q : $q->where('assigned_to', $user->id);
    }

    /** Open leads whose follow-up date has passed (or is today, with $includeToday). */
    public function scopeDue(Builder $q, bool $includeToday = true): Builder
    {
        return $q->whereNotIn('status', self::CLOSED)->whereNotNull('next_action_at')
            ->where('next_action_at', '<', $includeToday ? now()->endOfDay() : now()->startOfDay());
    }

    public function isOverdue(): bool
    {
        return $this->next_action_at && ! in_array($this->status, self::CLOSED, true) && $this->next_action_at->lt(now()->startOfDay());
    }

    /** "Google (Search)", "ChatGPT (AI assistant)", "Direct". */
    public function originLabel(): string
    {
        if (! $this->channel) return '';
        $ch = $this->channel === 'AI' ? 'AI assistant' : $this->channel;
        return $this->origin && $this->origin !== $this->channel ? "{$this->origin} ($ch)" : $ch;
    }

    public function log(string $type, string $body = '', ?int $userId = null, ?\DateTimeInterface $at = null): LeadActivity
    {
        $a = new LeadActivity(['type' => $type, 'body' => $body, 'user_id' => $userId ?? auth()->id()]);
        $a->lead_id = $this->id;
        if ($at) $a->created_at = $at;
        $a->save();
        return $a;
    }

    /** Phone number for tel: and WhatsApp links: digits only, UK numbers starting with 0 written with +44. */
    public function phoneDigits(): string
    {
        $d = preg_replace('/\D+/', '', (string) $this->phone);
        if (str_starts_with((string) $this->phone, '+')) return $d;
        return str_starts_with($d, '00') ? substr($d, 2) : (str_starts_with($d, '0') ? '44'.substr($d, 1) : $d);
    }
}
