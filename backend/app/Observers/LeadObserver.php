<?php

namespace App\Observers;

use App\Models\Lead;
use App\Models\User;
use App\Notifications\LeadAssigned;

/** Writes every change to a lead onto its timeline, and emails people when a lead is assigned to them. */
class LeadObserver
{
    public function created(Lead $lead): void
    {
        $where = $lead->form_path ? ' on '.$lead->form_path : '';
        $from = $lead->originLabel();
        $lead->log('created', ucfirst($lead->source ?: 'contact')." form$where".($from ? " · came from $from" : '').($lead->landing_path && $lead->landing_path !== $lead->form_path ? " · landed on {$lead->landing_path}" : ''), null, $lead->created_at);
        if ($lead->assigned_to) $this->assigned($lead, null);
    }

    public function updating(Lead $lead): void
    {
        // The first time someone moves the lead on from "New" counts as the first response.
        if ($lead->isDirty('status') && $lead->getOriginal('status') === 'new' && ! $lead->first_contacted_at) $lead->first_contacted_at = now();
        if ($lead->isDirty('assigned_to')) $lead->assignee = (string) User::query()->find($lead->assigned_to)?->name;
    }

    public function updated(Lead $lead): void
    {
        if ($lead->wasChanged('status')) {
            $from = Lead::STATUSES[$lead->getOriginal('status')] ?? $lead->getOriginal('status');
            $lead->log('status', "$from → ".(Lead::STATUSES[$lead->status] ?? $lead->status));
        }
        if ($lead->wasChanged('assigned_to')) $this->assigned($lead, $lead->getOriginal('assigned_to'));
        if ($lead->wasChanged(['next_action_at', 'next_action'])) {
            $lead->log('follow_up', $lead->next_action_at ? 'Set for '.$lead->next_action_at->format('D j M Y').($lead->next_action ? ": {$lead->next_action}" : '') : 'Cleared');
        }
        if ($lead->wasChanged('value') && (float) $lead->value != (float) $lead->getOriginal('value')) {
            $lead->log('value', '£'.number_format((float) $lead->value, 2));
        }
    }

    private function assigned(Lead $lead, ?int $before): void
    {
        $owner = $lead->assigned_to ? User::query()->find($lead->assigned_to) : null;
        $lead->log('assigned', $owner ? "To {$owner->name}" : 'Unassigned');
        // No email when you assign a lead to yourself.
        if ($owner && $owner->active && $owner->id !== auth()->id() && $owner->id !== $before) {
            try {
                $owner->notify(new LeadAssigned($lead, auth()->user()?->name));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
