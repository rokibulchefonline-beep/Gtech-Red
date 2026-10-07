<?php

namespace App\Support\Crm;

use App\Models\Contact;
use App\Models\Erasure;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Setting;
use App\Models\Subscriber;
use Illuminate\Support\Facades\DB;

/**
 * Data protection requests (UK GDPR): everything held about a person as one file (right of access), erasing it
 * (right to erasure), and deleting lost leads after the retention period set in Site settings > Leads.
 */
class Gdpr
{
    /** Every lead for this person: linked to the contact, or older ones with the same address. */
    private static function leads(Contact $c)
    {
        return Lead::query()->where(fn ($q) => $q->where('contact_id', $c->id)->orWhereRaw('LOWER(email) = ?', [$c->email]));
    }

    /** Everything held about the person, ready to send them. */
    public static function export(Contact $c): array
    {
        $leads = self::leads($c)->orderBy('created_at')->get();
        $sub = $c->subscriber();
        return [
            'exported_at' => now()->toIso8601String(),
            'organisation' => Setting::group('general')['siteName'] ?? '',
            'person' => $c->only(['email', 'name', 'phone', 'business']) + ['first_contact' => $c->first_seen_at?->toIso8601String(), 'last_contact' => $c->last_seen_at?->toIso8601String()],
            'enquiries' => $leads->map(fn (Lead $l) => [
                'received' => $l->created_at?->toIso8601String(), 'form' => $l->source, 'name' => $l->name, 'business' => $l->business, 'email' => $l->email,
                'phone' => $l->phone, 'service' => $l->service, 'budget' => $l->budget, 'website' => $l->website, 'postcode' => $l->postcode,
                'job_title' => $l->designation, 'company_size' => $l->company_size, 'message' => $l->message,
                'came_from' => $l->originLabel(), 'landing_page' => $l->landing_path, 'campaign' => $l->utm_campaign, 'ip_address' => $l->ip,
                'privacy_notice_shown' => $l->consent_text, 'status' => $l->statusLabel(), 'our_notes' => $l->notes,
                'contact_history' => LeadActivity::query()->where('lead_id', $l->id)->orderBy('created_at')->get()
                    ->map(fn (LeadActivity $a) => ['when' => $a->created_at?->toIso8601String(), 'type' => $a->label(), 'details' => $a->body])->all(),
            ])->all(),
            'newsletter' => $sub ? ['email' => $sub->email, 'subscribed' => $sub->created_at?->toIso8601String(), 'source' => $sub->source] : null,
        ];
    }

    /**
     * Removes the person's enquiries, their timelines, their newsletter subscription and the contact. Website visits
     * stay as anonymous statistics (they hold no name or address); only the link to the enquiry is cut.
     * @return array<string,int> what was removed
     */
    public static function erase(Contact $c, string $reason = ''): array
    {
        return DB::transaction(function () use ($c, $reason) {
            $ids = self::leads($c)->pluck('id');
            $counts = [
                'enquiries' => $ids->count(),
                'timeline_entries' => LeadActivity::query()->whereIn('lead_id', $ids)->delete(),
                'newsletter' => Subscriber::query()->where('email', $c->email)->delete(),
            ];
            DB::table('analytics_visits')->whereIn('lead_id', $ids)->update(['lead_id' => null]);
            Lead::query()->whereIn('id', $ids)->delete();
            Erasure::query()->create(['email_hash' => Erasure::hash($c->email), 'user_id' => auth()->id(), 'reason' => mb_substr($reason, 0, 200), 'counts' => $counts]);
            $c->delete();
            return $counts;
        });
    }

    /** Deletes lost leads untouched for longer than the retention period. Returns how many. */
    public static function purgeOld(): int
    {
        $months = (int) (Setting::group('leads')['retainMonths'] ?? 0);
        if ($months < 1) return 0;
        $ids = Lead::query()->where('status', 'lost')->where('updated_at', '<', now()->subMonths($months))->pluck('id');
        if ($ids->isEmpty()) return 0;
        LeadActivity::query()->whereIn('lead_id', $ids)->delete();
        DB::table('analytics_visits')->whereIn('lead_id', $ids)->update(['lead_id' => null]);
        $contacts = Lead::query()->whereIn('id', $ids)->pluck('contact_id')->filter()->unique();
        Lead::query()->whereIn('id', $ids)->delete();
        // A contact with no enquiries left (and not on the newsletter) goes too.
        foreach (Contact::query()->whereIn('id', $contacts)->get() as $c) {
            if (! Lead::query()->where('contact_id', $c->id)->exists() && ! $c->subscriber()) $c->delete();
        }
        return $ids->count();
    }

    /** Joins a duplicate contact (another address of the same person) into this one. */
    public static function merge(Contact $keep, Contact $dupe): void
    {
        DB::transaction(function () use ($keep, $dupe) {
            Lead::query()->where('contact_id', $dupe->id)->update(['contact_id' => $keep->id]);
            $keep->fill(['name' => $keep->name ?: $dupe->name, 'phone' => $keep->phone ?: $dupe->phone, 'business' => $keep->business ?: $dupe->business,
                'first_seen_at' => collect([$keep->first_seen_at, $dupe->first_seen_at])->filter()->min(), 'last_seen_at' => collect([$keep->last_seen_at, $dupe->last_seen_at])->filter()->max()])->save();
            $dupe->delete();
        });
    }
}
