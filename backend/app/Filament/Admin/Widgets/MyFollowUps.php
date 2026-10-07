<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\LeadResource;
use App\Models\Lead;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/** "My day": the signed-in person's follow-ups due today or overdue, and new leads waiting for someone to take them. */
class MyFollowUps extends TableWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'My follow-ups';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasPerm('leads.view');
    }

    protected function query(): Builder
    {
        $u = auth()->user();
        return Lead::query()->visibleTo($u)->where(fn (Builder $q) => $q
            ->where(fn (Builder $q) => $q->where('assigned_to', $u->id)->due())
            ->orWhere(fn (Builder $q) => $q->where('assigned_to', $u->id)->where('status', 'new')->whereNull('next_action_at'))
            ->when($u->hasPerm('leads.assign'), fn (Builder $q) => $q->orWhere(fn (Builder $q) => $q->whereNull('assigned_to')->where('status', 'new'))));
    }

    public function table(Table $table): Table
    {
        return $table->query($this->query()->with('owner')->orderByRaw('next_action_at is null')->orderBy('next_action_at')->latest())
            ->paginated([5, 10, 25])->defaultPaginationPageOption(5)
            ->description('Follow-ups due today or overdue, new leads assigned to you, and new leads nobody has taken yet.')
            ->columns([
                Tables\Columns\TextColumn::make('name')->description(fn (Lead $r) => $r->business),
                Tables\Columns\TextColumn::make('next_action_at')->label('Follow-up')->placeholder('New lead')
                    ->formatStateUsing(fn (Lead $r) => $r->isOverdue() ? 'Overdue: '.$r->next_action_at->format('D j M') : 'Today')
                    ->description(fn (Lead $r) => $r->next_action ? str($r->next_action)->limit(50) : null)
                    ->color(fn (Lead $r) => $r->isOverdue() ? 'danger' : ($r->next_action_at ? 'warning' : 'info'))->badge(),
                Tables\Columns\TextColumn::make('owner.name')->label('Assigned to')->placeholder('Nobody yet')->visibleFrom('md'),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Received')->visibleFrom('md'),
            ])
            ->recordUrl(fn (Lead $r) => LeadResource::getUrl(LeadResource::canEdit($r) ? 'edit' : 'view', ['record' => $r]))
            ->emptyStateHeading('All caught up')->emptyStateDescription('No follow-ups are due and no new leads are waiting.')->emptyStateIcon('heroicon-o-check-circle');
    }
}
