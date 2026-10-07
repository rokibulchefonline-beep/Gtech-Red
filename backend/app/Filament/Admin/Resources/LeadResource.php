<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Support\Perms;
use App\Filament\Admin\Resources\LeadResource\Pages;
use App\Filament\Support\Csv;
use App\Filament\Support\HooksDefault;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Filament\Forms;
use Illuminate\Support\HtmlString;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    use HooksDefault, Perms;

    public const STATUSES = Lead::STATUSES;

    protected static string $section = 'leads';
    protected static ?string $model = Lead::class;
    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationGroup = 'Leads';
    protected static ?string $navigationLabel = 'Leads';
    protected static ?int $navigationSort = 1;


    public static function canCreate(): bool { return false; }

    /** Sales users without "See everyone's leads" only get the leads assigned to them, everywhere in the panel. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }

    public static function getNavigationBadge(): ?string
    {
        $n = static::getEloquentQuery()->where('status', 'new')->count();
        return $n ? (string) $n : null;
    }

    /** Active team members who can work leads, for the "Assigned to" choices. */
    public static function ownerOptions(): array
    {
        return User::query()->where('active', true)->orderBy('name')->get()->filter(fn (User $u) => $u->hasPerm('leads.view'))->pluck('name', 'id')->all();
    }

    public static function canAssign(): bool
    {
        return (bool) auth()->user()?->hasPerm('leads.assign');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Enquiry')->schema([
                Forms\Components\Placeholder::make('who')->label('From')->content(fn (?Lead $r) => $r ? "{$r->name}, {$r->business}" : ''),
                Forms\Components\Placeholder::make('contact')->label('Contact')->content(fn (?Lead $r) => $r ? new HtmlString(implode(' · ', array_filter([
                    $r->email ? '<a class="text-primary-600 underline" href="mailto:'.e($r->email).'?subject='.rawurlencode('Your enquiry about '.$r->service).'">'.e($r->email).'</a>' : '',
                    $r->phone ? '<a class="text-primary-600 underline" href="tel:+'.$r->phoneDigits().'">'.e($r->phone).'</a>' : '',
                ]))) : ''),
                Forms\Components\Placeholder::make('svc')->label('Service and budget')->content(fn (?Lead $r) => $r ? trim("{$r->service} · {$r->budget}", ' ·') : ''),
                Forms\Components\Placeholder::make('site')->label('Website / postcode')->content(fn (?Lead $r) => $r ? new HtmlString(trim(
                    ($r->website ? '<a class="text-primary-600 underline" target="_blank" rel="noopener noreferrer" href="'.e(preg_match('#^https?://#i', $r->website) ? $r->website : 'https://'.$r->website).'">'.e($r->website).'</a> ' : '').e($r->postcode)) ?: '-') : ''),
                Forms\Components\Placeholder::make('msg')->label('Message')->content(fn (?Lead $r) => $r?->message ?: '-')->columnSpanFull(),
                Forms\Components\Placeholder::make('origin')->label('Came from')->content(function (?Lead $r) {
                    $v = $r?->visit_id ? \App\Models\AnalyticsVisit::query()->find($r->visit_id) : null;
                    if (! $v) return $r?->originLabel() ?: 'Not known (sent before analytics, or from a browser that blocks scripts)';
                    $bits = array_filter([$v->source.($v->channel !== $v->source ? ' ('.($v->channel === 'AI' ? 'AI assistant' : $v->channel).')' : ''),
                        $v->utm_campaign ? 'campaign "'.$v->utm_campaign.'"' : '', 'landed on '.$v->landing_path, $v->pageviews.' '.str('page')->plural($v->pageviews).' viewed', $v->device]);
                    return implode(' · ', $bits);
                })->columnSpanFull(),
                Forms\Components\Placeholder::make('when')->label('Received')->content(fn (?Lead $r) => $r?->created_at?->format('d M Y, H:i').' via '.$r?->source.' form'.($r?->form_path ? ' on '.$r->form_path : '')),
                Forms\Components\Placeholder::make('response')->label('First response')->content(fn (?Lead $r) => ! $r ? '' : ($r->first_contacted_at
                    ? $r->first_contacted_at->diffForHumans($r->created_at, \Carbon\CarbonInterface::DIFF_ABSOLUTE).' after the enquiry'
                    : ($r->status === 'new' ? 'Not contacted yet' : '-'))),
            ])->columns(2),
            Forms\Components\Section::make('Follow-up')->schema([
                Forms\Components\Select::make('status')->options(self::STATUSES)->required()->selectablePlaceholder(false),
                Forms\Components\Select::make('assigned_to')->label('Assigned to')->options(fn () => self::ownerOptions())->placeholder('Nobody')
                    ->disabled(fn () => ! self::canAssign())->dehydrated(fn () => self::canAssign())
                    ->helperText(fn () => self::canAssign() ? 'They get an email with the lead.' : null),
                Forms\Components\TextInput::make('value')->label('Deal value (£)')->numeric()->minValue(0),
                Forms\Components\DatePicker::make('next_action_at')->label('Next follow-up')->native(false)->displayFormat('D j M Y')->closeOnDateSelection()
                    ->helperText('Shows on the dashboard and in the morning reminder email.'),
                Forms\Components\TextInput::make('next_action')->label('What to do')->placeholder('e.g. Call back with the proposal')->maxLength(160)->columnSpan(2),
                Forms\Components\Textarea::make('notes')->label('Summary notes')->helperText('Calls, emails and meetings go on the timeline below.')->rows(3)->maxLength(5000)->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        $me = fn () => auth()->id();
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('owner'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(['name', 'business', 'email', 'phone'])->sortable()->description(fn (Lead $r) => $r->business),
                Tables\Columns\TextColumn::make('email')->url(fn (Lead $r) => 'mailto:'.$r->email)->description(fn (Lead $r) => $r->phone)->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('service')->searchable()->wrap()->visibleFrom('lg')->description(fn (Lead $r) => $r->originLabel() ? 'via '.$r->originLabel() : null),
                Tables\Columns\SelectColumn::make('status')->options(self::STATUSES)->selectablePlaceholder(false)->disabled(fn () => ! static::allows('edit')),
                Tables\Columns\TextColumn::make('owner.name')->label('Assigned to')->placeholder('Nobody')->visibleFrom('md'),
                Tables\Columns\TextColumn::make('next_action_at')->label('Follow-up')->date('D j M')->sortable()->placeholder('-')
                    ->description(fn (Lead $r) => $r->next_action_at ? str($r->next_action)->limit(40) : null)
                    ->color(fn (Lead $r) => $r->isOverdue() ? 'danger' : ($r->next_action_at?->isToday() ? 'warning' : null))
                    ->icon(fn (Lead $r) => $r->isOverdue() ? 'heroicon-m-exclamation-triangle' : null),
                Tables\Columns\TextColumn::make('channel')->label('Source')->formatStateUsing(fn (Lead $r) => $r->originLabel())->placeholder('-')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->label('Received')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('mine')->label('Assigned to me')->toggle()->query(fn (Builder $query) => $query->where('assigned_to', $me())),
                Tables\Filters\SelectFilter::make('status')->options(self::STATUSES)->multiple(),
                Tables\Filters\SelectFilter::make('follow_up')->label('Follow-up')->options(['due' => 'Due today or overdue', 'overdue' => 'Overdue', 'none' => 'Open, with no follow-up set'])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'due' => $query->due(), 'overdue' => $query->due(false),
                        'none' => $query->whereNotIn('status', Lead::CLOSED)->whereNull('next_action_at'),
                        default => $query,
                    }),
                Tables\Filters\SelectFilter::make('assigned_to')->label('Assigned to')->options(fn () => ['none' => 'Nobody'] + self::ownerOptions())
                    ->visible(fn () => (bool) auth()->user()?->hasPerm('leads.all'))
                    ->query(fn (Builder $query, array $data) => match ($v = $data['value'] ?? null) { null, '' => $query, 'none' => $query->whereNull('assigned_to'), default => $query->where('assigned_to', $v) }),
                Tables\Filters\SelectFilter::make('channel')->label('Source')->options(fn () => Lead::query()->where('channel', '!=', '')->distinct()->orderBy('channel')->pluck('channel', 'channel')->all()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Open'),
                Tables\Actions\ViewAction::make()->label('Open')->visible(fn (Lead $r) => ! static::canEdit($r)),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('assign')->label('Assign')->icon('heroicon-o-user-plus')->visible(fn () => self::canAssign())
                    ->form([Forms\Components\Select::make('user')->label('Assign to')->options(fn () => self::ownerOptions())->placeholder('Nobody')])
                    ->action(fn (\Illuminate\Support\Collection $records, array $data) => $records->each->update(['assigned_to' => $data['user'] ?: null]))
                    ->deselectRecordsAfterCompletion(),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function exportAction(): Action
    {
        return Action::make('export')->label('Export CSV')->icon('heroicon-o-arrow-down-tray')->color('gray')->visible(fn () => (bool) auth()->user()?->hasPerm('leads.export'))
            ->action(fn () => Csv::download('leads-'.now()->format('Y-m-d').'.csv',
                ['Received', 'Name', 'Business', 'Email', 'Phone', 'Service', 'Budget', 'Website', 'Postcode', 'Message', 'Form', 'Came from', 'Landing page', 'Campaign', 'Status', 'Assigned to', 'Value', 'Next follow-up', 'What to do', 'Notes'],
                static::getEloquentQuery()->latest()->cursor()->map(fn (Lead $l) => [$l->created_at?->format('Y-m-d H:i'), $l->name, $l->business, $l->email, $l->phone, $l->service, $l->budget, $l->website, $l->postcode, $l->message, $l->source, $l->originLabel(), $l->landing_path, $l->utm_campaign, $l->status, $l->assignee, $l->value, $l->next_action_at?->format('Y-m-d'), $l->next_action, $l->notes])));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLead::route('/'),
            'view' => Pages\ViewLead::route('/{record}'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [LeadResource\RelationManagers\ActivitiesRelationManager::class];
    }
}
