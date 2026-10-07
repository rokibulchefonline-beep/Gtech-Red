<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** "A lead has been assigned to you", with the enquiry and a link to open it. */
class LeadAssigned extends Notification
{
    public function __construct(public Lead $lead, private ?string $by = null) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $l = $this->lead;
        $m = (new MailMessage)
            ->subject("Lead assigned to you: {$l->name}".($l->business ? " ({$l->business})" : ''))
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->by ? "{$this->by} assigned you a lead." : 'A new enquiry from the website has been assigned to you.')
            ->line(implode(' · ', array_filter([$l->name, $l->business, $l->service, $l->budget])));
        if ($o = $l->originLabel()) $m->line("Came from: $o");
        if ($l->message) $m->line('"'.str($l->message)->limit(300).'"');
        if ($l->next_action_at) $m->line('Follow-up: '.$l->next_action_at->format('D j M').($l->next_action ? " — {$l->next_action}" : ''));
        return $m->action('Open the lead', url("/admin/leads/{$l->id}/edit"));
    }
}
