<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\LeadResource;
use App\Models\Lead;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestLeads extends TableWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Latest leads';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasPerm('leads.view');
    }

    public function table(Table $table): Table
    {
        return $table->query(Lead::query()->visibleTo(auth()->user())->with('owner')->latest()->limit(6))->paginated(false)->columns([
            Tables\Columns\TextColumn::make('name')->description(fn (Lead $r) => $r->business),
            Tables\Columns\TextColumn::make('service'),
            Tables\Columns\TextColumn::make('status')->badge()->formatStateUsing(fn (string $state) => Lead::statuses()[$state] ?? $state),
            Tables\Columns\TextColumn::make('owner.name')->label('Assigned to')->placeholder('Nobody')->visibleFrom('md'),
            Tables\Columns\TextColumn::make('created_at')->since()->label('Received'),
        ])->recordUrl(fn (Lead $r) => LeadResource::getUrl(LeadResource::canEdit($r) ? 'edit' : 'view', ['record' => $r]));
    }
}
