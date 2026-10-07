<?php

namespace App\Filament\Admin\Resources\LeadResource\RelationManagers;

use App\Models\Lead;
use App\Models\LeadActivity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * The lead's timeline: notes, calls, emails and meetings people log, plus status, assignment and follow-up changes
 * recorded automatically. Logging a call, email or meeting can set the next follow-up in the same step.
 */
class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';
    protected static ?string $title = 'Timeline';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return (bool) auth()->user()?->hasPerm('leads.view');
    }

    private static function canLog(): bool
    {
        return (bool) auth()->user()?->hasPerm('leads.edit');
    }

    /** People can change or remove what they logged themselves; automatic entries stay. */
    private static function mine(LeadActivity $a): bool
    {
        return self::canLog() && isset(LeadActivity::LOGGABLE[$a->type]) && $a->user_id === auth()->id();
    }

    public function form(Form $form): Form
    {
        return $form->schema(self::fields())->columns(1);
    }

    private static function fields(): array
    {
        return [
            Forms\Components\ToggleButtons::make('type')->options(LeadActivity::LOGGABLE)->default('note')->inline()->required()
                ->icons(array_intersect_key(LeadActivity::ICONS, LeadActivity::LOGGABLE)),
            Forms\Components\Textarea::make('body')->label('What happened')->rows(4)->required()->maxLength(5000),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->modifyQueryUsing(fn ($query) => $query->with('user'))
            ->paginated([10, 25, 50])
            ->columns([
                Tables\Columns\IconColumn::make('type')->label('')->icon(fn (string $state) => LeadActivity::ICONS[$state] ?? 'heroicon-o-information-circle')
                    ->color(fn (string $state) => isset(LeadActivity::LOGGABLE[$state]) ? 'primary' : 'gray')->grow(false),
                Tables\Columns\TextColumn::make('body')->label('Activity')->wrap()
                    ->formatStateUsing(fn (LeadActivity $r) => $r->body)->description(fn (LeadActivity $r) => $r->label(), 'above'),
                Tables\Columns\TextColumn::make('user.name')->label('By')->placeholder('Automatic')->visibleFrom('md'),
                Tables\Columns\TextColumn::make('created_at')->label('When')->since()->tooltip(fn (LeadActivity $r) => $r->created_at?->format('D j M Y, H:i')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options(LeadActivity::LOGGABLE + ['status' => 'Status changes', 'assigned' => 'Assignments', 'follow_up' => 'Follow-ups']),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Log activity')->icon('heroicon-o-plus')->modalHeading('Log activity')->modalSubmitActionLabel('Save')->createAnother(false)->visible(fn () => self::canLog())
                    ->form([
                        ...self::fields(),
                        Forms\Components\Fieldset::make('Next follow-up (optional)')->schema([
                            Forms\Components\DatePicker::make('next_action_at')->label('Date')->native(false)->displayFormat('D j M Y')->closeOnDateSelection()->minDate(today()),
                            Forms\Components\TextInput::make('next_action')->label('What to do')->maxLength(160),
                        ])->columns(2),
                    ])
                    ->using(function (array $data) {
                        /** @var Lead $lead */
                        $lead = $this->getOwnerRecord();
                        $a = $lead->log($data['type'], (string) $data['body']);
                        $changes = [];
                        // A call, email or meeting is a response: the lead moves on from "New".
                        if ($data['type'] !== 'note') {
                            if ($lead->status === 'new') $changes['status'] = 'contacted';
                            if (! $lead->first_contacted_at) $lead->first_contacted_at = now();
                        }
                        if (! empty($data['next_action_at'])) $changes += ['next_action_at' => $data['next_action_at'], 'next_action' => (string) ($data['next_action'] ?? '')];
                        $lead->fill($changes)->save();
                        return $a;
                    })
                    ->after(fn () => $this->dispatch('refresh-lead-form')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton()->visible(fn (LeadActivity $r) => self::mine($r)),
                Tables\Actions\DeleteAction::make()->iconButton()->visible(fn (LeadActivity $r) => self::mine($r)),
            ])
            ->emptyStateHeading('Nothing logged yet')
            ->emptyStateDescription('Log calls, emails, meetings and notes here. Status and assignment changes appear automatically.');
    }
}
