<?php

namespace App\Filament\Admin\Resources\ContactResource\RelationManagers;

use App\Filament\Admin\Resources\LeadResource;
use App\Models\Lead;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LeadsRelationManager extends RelationManager
{
    protected static string $relationship = 'leads';
    protected static ?string $title = 'Enquiries';

    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('service')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Received')->date('j M Y'),
                Tables\Columns\TextColumn::make('service')->description(fn (Lead $r) => $r->originLabel() ? 'via '.$r->originLabel() : null),
                Tables\Columns\TextColumn::make('status')->badge()->formatStateUsing(fn (Lead $r) => $r->statusLabel()),
                Tables\Columns\TextColumn::make('owner.name')->label('Assigned to')->placeholder('Nobody'),
                Tables\Columns\TextColumn::make('value')->money('GBP')->placeholder('-'),
            ])
            ->recordUrl(fn (Lead $r) => LeadResource::getUrl(LeadResource::canEdit($r) ? 'edit' : 'view', ['record' => $r]))
            ->paginated(false);
    }
}
