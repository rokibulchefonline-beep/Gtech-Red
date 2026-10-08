<?php

namespace App\Support\Crm;

use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;

/** "Send email" on a lead: write or pick a template, send it with the site's SMTP account, log it on the timeline. */
class LeadMailer
{
    public static function action(string $class, \Closure $after): mixed
    {
        return $class::make('sendEmail')->label('Send email')->icon('heroicon-o-paper-airplane')
            ->visible(fn (Lead $record) => (bool) $record->email && (bool) auth()->user()?->hasPerm('leads.edit'))
            ->modalHeading(fn (Lead $record) => 'Email '.$record->name)->modalWidth('3xl')->modalSubmitActionLabel('Send')
            ->form(fn (Lead $record) => [
                Forms\Components\Placeholder::make('smtp')->hiddenLabel()->visible(fn () => blank(Setting::group('smtp')['host'] ?? ''))
                    ->content('Email is not set up yet: add your SMTP account in Site settings > Email first.'),
                Forms\Components\Select::make('template')->label('Start from a template')->placeholder('A blank email')
                    ->options(fn () => EmailTemplate::query()->orderBy('name')->pluck('name', 'id')->all())->live()
                    ->afterStateUpdated(function (?string $state, Set $set) use ($record) {
                        $t = $state ? EmailTemplate::query()->find($state) : null;
                        if (! $t) return;
                        $set('subject', EmailTemplate::render($t->subject, $record, auth()->user(), html: false));
                        $set('body', EmailTemplate::render($t->body, $record, auth()->user()));
                    }),
                Forms\Components\TextInput::make('to')->default($record->email)->disabled()->dehydrated(false),
                Forms\Components\TextInput::make('subject')->required()->maxLength(200)->default('Your enquiry about '.$record->service),
                Forms\Components\RichEditor::make('body')->required()->toolbarButtons(['bold', 'italic', 'underline', 'link', 'bulletList', 'orderedList', 'undo', 'redo']),
                Forms\Components\Toggle::make('copy')->label('Send me a copy')->default(false),
            ])
            ->action(function (Lead $record, array $data) use ($after) {
                $me = auth()->user();
                try {
                    \App\Support\SiteMailer::send($record->email, $data['subject'], (string) $data['body'], $me?->email, plain: true, context: ['lead_id' => $record->id]);
                    if (! empty($data['copy']) && $me?->email) \App\Support\SiteMailer::send($me->email, 'Copy: '.$data['subject'], (string) $data['body'], null, plain: true);
                } catch (\Throwable $e) {
                    Notification::make()->title('The email was not sent')->body($e->getMessage())->danger()->persistent()->send();
                    return;
                }
                $record->log('email', 'Sent "'.$data['subject']."\"\n\n".\App\Support\RevisionDiff::text((string) $data['body']));
                $changes = $record->status === 'new' ? ['status' => 'contacted'] : [];
                if (! $record->first_contacted_at) $record->first_contacted_at = now();
                $record->fill($changes)->save();
                Notification::make()->title('Email sent to '.$record->email)->success()->send();
                $after();
            });
    }
}
