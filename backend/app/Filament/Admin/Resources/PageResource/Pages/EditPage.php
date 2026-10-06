<?php

namespace App\Filament\Admin\Resources\PageResource\Pages;

use App\Filament\Admin\Resources\PageResource;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return PageResource::beforeFill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return PageResource::beforeSave($data, $this->record);
    }
}
