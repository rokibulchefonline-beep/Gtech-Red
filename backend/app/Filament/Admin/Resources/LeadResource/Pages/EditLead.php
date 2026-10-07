<?php

namespace App\Filament\Admin\Resources\LeadResource\Pages;

use App\Filament\Admin\Resources\LeadResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        $lead = $this->record;
        return [
            Actions\Action::make('email')->label('Email')->icon('heroicon-o-envelope')->color('gray')->visible((bool) $lead->email)
                ->url('mailto:'.$lead->email.'?subject='.rawurlencode('Your enquiry about '.$lead->service)),
            Actions\Action::make('call')->label('Call')->icon('heroicon-o-phone')->color('gray')->visible((bool) $lead->phone)
                ->url('tel:+'.$lead->phoneDigits()),
            Actions\Action::make('whatsapp')->label('WhatsApp')->icon('heroicon-o-chat-bubble-left-right')->color('gray')->visible((bool) $lead->phone)
                ->url('https://wa.me/'.$lead->phoneDigits(), shouldOpenInNewTab: true),
            Actions\DeleteAction::make(),
        ];
    }

    /** The timeline can change the status and follow-up (logging a call, say): show the new values. */
    #[\Livewire\Attributes\On('refresh-lead-form')]
    public function refreshLead(): void
    {
        $this->record->refresh();
        $this->refreshFormData(['status', 'next_action_at', 'next_action']);
    }

    public function getTitle(): string
    {
        return 'Lead: '.$this->record->name;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return LeadResource::beforeFill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return LeadResource::beforeSave($data, $this->record);
    }
}
