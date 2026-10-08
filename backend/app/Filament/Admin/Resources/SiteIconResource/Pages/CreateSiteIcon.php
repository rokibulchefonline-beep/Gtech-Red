<?php

namespace App\Filament\Admin\Resources\SiteIconResource\Pages;

use App\Filament\Admin\Resources\SiteIconResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSiteIcon extends CreateRecord
{
    protected static string $resource = SiteIconResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return SiteIconResource::prepare($data);
    }
}
