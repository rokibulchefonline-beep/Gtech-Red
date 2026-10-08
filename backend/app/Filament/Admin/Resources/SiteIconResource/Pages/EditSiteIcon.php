<?php

namespace App\Filament\Admin\Resources\SiteIconResource\Pages;

use App\Filament\Admin\Resources\SiteIconResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSiteIcon extends EditRecord
{
    protected static string $resource = SiteIconResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return SiteIconResource::prepare($data, $this->record);
    }
}
