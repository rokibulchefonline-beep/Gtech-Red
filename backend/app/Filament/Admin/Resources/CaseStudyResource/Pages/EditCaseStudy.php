<?php

namespace App\Filament\Admin\Resources\CaseStudyResource\Pages;

use App\Filament\Admin\Resources\CaseStudyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCaseStudy extends EditRecord
{
    use \App\Filament\Support\HasSchemaPanel;

    protected function schemaPath(): string
    {
        return '/case-studies/'.$this->record->slug;
    }

    protected static string $resource = CaseStudyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('history')->label('Version history')->icon('heroicon-o-clock')->color('gray')
                ->url(fn () => \App\Filament\Admin\Pages\VersionHistory::urlFor($this->record)),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->fillSchema(CaseStudyResource::beforeFill($data));
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return CaseStudyResource::beforeSave($data, $this->record);
    }

    protected function afterSave(): void
    {
        $this->saveSchema();
    }
}
