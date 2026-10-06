<?php

namespace App\Filament\Admin\Resources\SubscriberResource\Pages;

use App\Filament\Admin\Resources\SubscriberResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubscriber extends EditRecord
{
    protected static string $resource = SubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return SubscriberResource::beforeFill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return SubscriberResource::beforeSave($data, $this->record);
    }
}
