<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\AuditRequestResource;
use App\Models\Lead;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** The newest free audit requests from /free-audit, on the dashboard. */
class LatestAuditRequests extends TableWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Latest free audit requests';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasPerm('leads.view');
    }

    public function table(Table $table): Table
    {
        return $table->query(Lead::query()->visibleTo(auth()->user())->where('source', 'audit')->with('owner')->latest()->limit(6))->paginated(false)
            ->emptyStateHeading('No audit requests yet')->emptyStateDescription('Requests from the Free Audit page appear here.')
            ->columns([
                Tables\Columns\TextColumn::make('name')->description(fn (Lead $r) => $r->business),
                Tables\Columns\TextColumn::make('website')->limit(40),
                Tables\Columns\TextColumn::make('status')->badge()->formatStateUsing(fn (string $state) => Lead::statuses()[$state] ?? $state),
                Tables\Columns\TextColumn::make('owner.name')->label('Assigned to')->placeholder('Nobody')->visibleFrom('md'),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Received'),
            ])->recordUrl(fn (Lead $r) => AuditRequestResource::getUrl(AuditRequestResource::canEdit($r) ? 'edit' : 'view', ['record' => $r]));
    }
}
