<?php

namespace App\Filament\Admin\Resources\ContactResource\Pages;

use App\Filament\Admin\Resources\ContactResource;
use App\Models\Contact;
use App\Support\Crm\Gdpr;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewContact extends ViewRecord
{
    protected static string $resource = ContactResource::class;

    public function getTitle(): string
    {
        return $this->record->name ?: $this->record->email;
    }

    protected function getHeaderActions(): array
    {
        $u = auth()->user();
        return [
            Actions\Action::make('email')->label('Email')->icon('heroicon-o-envelope')->color('gray')->url('mailto:'.$this->record->email),
            Actions\ActionGroup::make([
                Actions\Action::make('merge')->label('Merge a duplicate into this contact')->icon('heroicon-o-arrows-pointing-in')
                    ->visible($u->hasPerm('leads.edit'))
                    ->form([Forms\Components\Select::make('dupe')->label('The duplicate (its enquiries move here, then it is removed)')->required()->searchable()
                        ->getSearchResultsUsing(fn (string $search) => Contact::query()->whereKeyNot($this->record->id)
                            ->where(fn ($q) => $q->where('name', 'like', "%$search%")->orWhere('email', 'like', "%$search%")->orWhere('business', 'like', "%$search%"))
                            ->limit(20)->get()->mapWithKeys(fn (Contact $c) => [$c->id => "{$c->name} <{$c->email}>"])->all())])
                    ->action(function (array $data) {
                        Gdpr::merge($this->record, Contact::query()->findOrFail($data['dupe']));
                        Notification::make()->title('Contacts merged')->success()->send();
                        $this->redirect(ContactResource::getUrl('view', ['record' => $this->record]));
                    }),
                Actions\Action::make('export')->label('Download their data (GDPR)')->icon('heroicon-o-arrow-down-tray')
                    ->visible($u->hasPerm('leads.export'))
                    ->action(function () {
                        $json = json_encode(Gdpr::export($this->record), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                        $name = 'personal-data-'.\Illuminate\Support\Str::slug($this->record->name ?: 'contact').'-'.now()->format('Y-m-d').'.json';
                        return response()->streamDownload(fn () => print($json), $name, ['Content-Type' => 'application/json']);
                    }),
                Actions\Action::make('erase')->label('Erase their data (GDPR)')->icon('heroicon-o-trash')->color('danger')
                    ->visible($u->hasPerm('leads.delete'))
                    ->modalHeading('Erase everything about this person?')
                    ->modalDescription('Their enquiries, timelines, notes and newsletter subscription are deleted for good. Website visit statistics stay, without any link to them. A record that an erasure took place is kept (without their details).')
                    ->form([
                        Forms\Components\TextInput::make('confirm')->label('Type their email address to confirm')->required()
                            ->rule(fn () => fn ($attr, $v, $fail) => mb_strtolower(trim((string) $v)) === $this->record->email ? null : $fail('The address does not match.')),
                        Forms\Components\TextInput::make('reason')->label('Reason (optional)')->placeholder('e.g. Erasure request by email, 7 Oct')->maxLength(200),
                    ])
                    ->modalSubmitActionLabel('Erase')
                    ->action(function (array $data) {
                        $c = Gdpr::erase($this->record, (string) ($data['reason'] ?? ''));
                        Notification::make()->title('Data erased')->body("{$c['enquiries']} enquiries, {$c['timeline_entries']} timeline entries and ".($c['newsletter'] ? 'the newsletter subscription' : 'no newsletter subscription').' removed.')->success()->send();
                        $this->redirect(ContactResource::getUrl());
                    }),
            ])->label('More')->icon('heroicon-m-ellipsis-vertical')->button()->color('gray'),
        ];
    }
}
