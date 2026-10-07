<?php

namespace App\Support\Crm;

use App\Models\Lead;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Http;

/** What happens when a new enquiry arrives: automatic assignment and the chat alert (Site settings > Leads). */
class LeadRouting
{
    /** The user a new lead should go to, or null. Round robin over the chosen team members who are still active. */
    public static function pickOwner(): ?int
    {
        $s = Setting::group('leads');
        if (($s['autoAssign'] ?? 'off') !== 'round_robin') return null;
        $ids = array_values(array_map('intval', (array) ($s['assignees'] ?? [])));
        $ids = array_values(array_intersect($ids, User::query()->whereIn('id', $ids)->where('active', true)->pluck('id')->all()));
        if (! $ids) return null;
        $last = Lead::query()->whereIn('assigned_to', $ids)->latest('id')->value('assigned_to');
        $i = $last === null ? -1 : array_search((int) $last, $ids, true);
        return $ids[($i === false ? -1 : $i) + 1] ?? $ids[0];
    }

    /** Posts the new lead to a Slack, Google Chat or Microsoft Teams incoming webhook. */
    public static function alert(Lead $lead): void
    {
        $url = trim((string) (Setting::group('leads')['webhook'] ?? ''));
        if (! self::validWebhook($url)) return;
        $lines = ['*New lead:* '.$lead->name.($lead->business ? " ({$lead->business})" : ''), implode(' · ', array_filter([$lead->service, $lead->budget]))];
        if ($o = $lead->originLabel()) $lines[] = "Came from: $o";
        if ($lead->owner) $lines[] = 'Assigned to '.$lead->owner->name;
        $lines[] = url("/admin/leads/{$lead->id}/edit");
        Http::timeout(5)->post($url, ['text' => implode("\n", array_filter($lines))]);
    }

    public static function validWebhook(string $url): bool
    {
        return (bool) preg_match('#^https://[a-z0-9.-]+\.[a-z]{2,}/\S+$#i', $url);
    }
}
