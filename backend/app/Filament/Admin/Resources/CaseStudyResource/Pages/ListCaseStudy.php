<?php

namespace App\Filament\Admin\Resources\CaseStudyResource\Pages;

use App\Filament\Admin\Resources\CaseStudyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCaseStudy extends ListRecords
{
    protected static string $resource = CaseStudyResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make(),];
    }
}
