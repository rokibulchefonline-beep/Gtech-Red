<?php

namespace App\Filament\Admin\Resources\SeoEntryResource\Pages;

use App\Filament\Admin\Resources\SeoEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSeoEntry extends EditRecord
{
    protected static string $resource = SeoEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return SeoEntryResource::beforeFill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return SeoEntryResource::beforeSave($data, $this->record);
    }
}
