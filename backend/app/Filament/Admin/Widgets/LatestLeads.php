<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\LeadResource;
use App\Models\Lead;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestLeads extends TableWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Latest leads';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasPerm('leads');
    }

    public function table(Table $table): Table
    {
        return $table->query(Lead::query()->latest()->limit(6))->paginated(false)->columns([
            Tables\Columns\TextColumn::make('name')->description(fn (Lead $r) => $r->business),
            Tables\Columns\TextColumn::make('service'),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('created_at')->since()->label('Received'),
        ])->recordUrl(fn (Lead $r) => LeadResource::getUrl('edit', ['record' => $r]));
    }
}
