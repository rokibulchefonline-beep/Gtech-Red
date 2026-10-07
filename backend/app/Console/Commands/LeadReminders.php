<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\FollowUpReminder;
use Illuminate\Console\Command;

/** Emails each person the follow-ups assigned to them that are due today or overdue (scheduled weekday mornings). */
class LeadReminders extends Command
{
    protected $signature = 'leads:remind';
    protected $description = 'Email everyone their follow-ups due today or overdue';

    public function handle(): int
    {
        if (! (Setting::group('leads')['reminders'] ?? true)) {
            $this->info('Reminders are turned off in Site settings > Leads.');
            return self::SUCCESS;
        }
        $sent = 0;
        $byOwner = Lead::query()->due()->whereNotNull('assigned_to')->orderBy('next_action_at')->get()->groupBy('assigned_to');
        foreach ($byOwner as $userId => $leads) {
            $u = User::query()->find($userId);
            if (! $u?->active || ! $u->hasPerm('leads.view')) continue;
            try {
                $u->notify(new FollowUpReminder($leads));
                $sent++;
            } catch (\Throwable $e) {
                report($e);
                $this->error("Could not email {$u->email}: ".$e->getMessage());
            }
        }
        $this->info("Sent $sent reminder ".str('email')->plural($sent).'.');
        return self::SUCCESS;
    }
}
