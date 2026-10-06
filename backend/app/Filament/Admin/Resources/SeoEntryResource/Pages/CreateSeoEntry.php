<?php

namespace App\Filament\Admin\Resources\SeoEntryResource\Pages;

use App\Filament\Admin\Resources\SeoEntryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSeoEntry extends CreateRecord
{
    protected static string $resource = SeoEntryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return SeoEntryResource::beforeSave($data);
    }
}
