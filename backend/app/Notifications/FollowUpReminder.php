<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

/** The morning list of a user's follow-ups that are due today or overdue. */
class FollowUpReminder extends Notification
{
    /** @param Collection<\App\Models\Lead> $leads */
    public function __construct(public Collection $leads) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $overdue = $this->leads->filter->isOverdue()->count();
        $m = (new MailMessage)
            ->subject($this->leads->count().' '.str('follow-up')->plural($this->leads->count()).' today'.($overdue ? " ($overdue overdue)" : ''))
            ->greeting('Good morning '.explode(' ', $notifiable->name)[0].',')
            ->line('These leads are waiting on you:');
        foreach ($this->leads as $l) {
            $when = $l->isOverdue() ? 'OVERDUE since '.$l->next_action_at->format('j M') : 'today';
            $m->line("• {$l->name}".($l->business ? " ({$l->business})" : '')." — ".($l->next_action ?: 'follow up')." — $when");
        }
        return $m->action('Open my follow-ups', url('/admin/leads?tableFilters[follow_up][value]=due&tableFilters[mine][isActive]=1'));
    }
}
