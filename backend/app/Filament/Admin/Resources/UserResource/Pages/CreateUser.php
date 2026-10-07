<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /** The "Email an invitation" switch is not saved with the user, so it is read before the form is processed. */
    protected bool $sendInvite = false;

    protected function beforeValidate(): void
    {
        $this->sendInvite = (bool) ($this->data['invite'] ?? false);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return UserResource::beforeSave($data);
    }

    protected function afterCreate(): void
    {
        if ($this->sendInvite) UserResource::invite($this->record);
    }
}
