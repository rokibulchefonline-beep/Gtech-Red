<?php

namespace App\Notifications;

use Filament\Facades\Filament;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** "You've been invited to the admin panel": a link to set a password (the same mechanism as a password reset). */
class InviteUser extends Notification
{
    public function __construct(private string $token, private string $by) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $site = \App\Models\Setting::group('general')['siteName'] ?? 'GTech Digital';
        $hours = (int) round(config('auth.passwords.users.expire', 60) / 60);
        return (new MailMessage)
            ->subject("You're invited to the $site admin panel")
            ->greeting('Hello '.$notifiable->name.',')
            ->line("{$this->by} has given you access to the $site admin panel.")
            ->action('Set your password', Filament::getResetPasswordUrl($this->token, $notifiable))
            ->line("The link works for $hours hours. If it expires, use \"Forgot password\" on the sign-in page.")
            ->line('Your email address is your username: '.$notifiable->email);
    }
}
