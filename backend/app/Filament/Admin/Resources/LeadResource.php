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

    /** Links to the same person's other enquiries (those this user may see) and their contact page. */
    public static function otherEnquiries(Lead $r): HtmlString
    {
        $links = static::getEloquentQuery()->where('contact_id', $r->contact_id)->whereKeyNot($r->id)->latest()->get()
            ->map(fn (Lead $o) => '<a class="text-primary-600 underline" href="'.e(self::getUrl('edit', ['record' => $o])).'">'.e($o->created_at?->format('j M Y').' · '.$o->service.' · '.$o->statusLabel()).'</a>')
            ->implode('<br>');
        $contact = auth()->user()?->hasPerm('leads.all') ? '<br><a class="text-primary-600 underline" href="'.e(ContactResource::getUrl('view', ['record' => $r->contact_id])).'">Open the contact</a>' : '';
        return new HtmlString(($links ?: 'Assigned to someone else.').$contact);
    }

    /** The extra answers of a free audit request, as a short list. */
    public static function detailsHtml(?Lead $r): string
    {
        $labels = ['goals' => 'Goals', 'areas' => 'Check first', 'competitors' => 'Competitors', 'location' => 'Target area'];
        return collect((array) $r?->details)->map(fn ($v, $k) => '<b>'.e($labels[$k] ?? $k).':</b> '.e(is_array($v) ? implode(', ', $v) : $v))->implode('<br>');
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
                Forms\Components\Placeholder::make('audit')->label('Audit request')->columnSpanFull()->visible(fn (?Lead $r) => filled($r?->details))
                    ->content(fn (?Lead $r) => new HtmlString(self::detailsHtml($r))),
                Forms\Components\Placeholder::make('msg')->label('Message')->content(fn (?Lead $r) => $r?->message ?: '-')->columnSpanFull(),
                Forms\Components\Placeholder::make('origin')->label('Came from')->content(function (?Lead $r) {
                    $v = $r?->visit_id ? \App\Models\AnalyticsVisit::query()->find($r->visit_id) : null;
                    if (! $v) return $r?->originLabel() ?: 'Not known (sent before analytics, or from a browser that blocks scripts)';
                    $bits = array_filter([$v->source.($v->channel !== $v->source ? ' ('.($v->channel === 'AI' ? 'AI assistant' : $v->channel).')' : ''),
                        $v->utm_campaign ? 'campaign "'.$v->utm_campaign.'"' : '', 'landed on '.$v->landing_path, $v->pageviews.' '.str('page')->plural($v->pageviews).' viewed', $v->device]);
                    return implode(' · ', $bits);
                })->columnSpanFull(),
                Forms\Components\Placeholder::make('when')->label('Received')->content(fn (?Lead $r) => $r?->created_at?->format('d M Y, H:i').' via '.$r?->source.' form'.($r?->form_path ? ' on '.$r->form_path : '')),
                Forms\Components\Placeholder::make('others')->label('Other enquiries from this person')->columnSpanFull()
                    ->visible(fn (?Lead $r) => $r?->contact_id && Lead::query()->where('contact_id', $r->contact_id)->whereKeyNot($r->id)->exists())
                    ->content(fn (?Lead $r) => self::otherEnquiries($r)),
                Forms\Components\Placeholder::make('privacy')->label('Privacy notice shown with the form')->columnSpanFull()
                    ->visible(fn (?Lead $r) => filled($r?->consent_text))
                    ->content(fn (?Lead $r) => $r->consent_text.($r->ip ? ' (sent from '.$r->ip.')' : '')),
                Forms\Components\Placeholder::make('response')->label('First response')->content(fn (?Lead $r) => ! $r ? '' : ($r->first_contacted_at
                    ? $r->first_contacted_at->diffForHumans($r->created_at, \Carbon\CarbonInterface::DIFF_ABSOLUTE).' after the enquiry'
                    : ($r->status === 'new' ? 'Not contacted yet' : '-'))),
            ])->columns(2),
            Forms\Components\Section::make('Follow-up')->schema([
                Forms\Components\Select::make('status')->options(fn () => Lead::statuses())->required()->selectablePlaceholder(false),
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

    /** The lead's details page (View): everything about the enquiry, with the summary and follow-up in full. */
    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Summary')->schema([
                Infolists\Components\TextEntry::make('notes')->label('Summary notes')->placeholder('No summary yet. Use Edit to add one.')
                    ->columnSpanFull()->extraAttributes(['class' => 'whitespace-pre-line']),
            ])->icon('heroicon-o-document-text'),
            Infolists\Components\Section::make('Follow-up history')->icon('heroicon-o-clock')->schema([
                Infolists\Components\TextEntry::make('fu_count')->label('Followed up')->state(fn (Lead $r) => ($n = $r->followUps()->count()).' '.str('time')->plural($n))
                    ->helperText(fn (Lead $r) => collect(\App\Models\LeadActivity::FOLLOW_UPS)->map(fn ($t) => $r->followUps()->where('type', $t)->count().' '.str(\App\Models\LeadActivity::LOGGABLE[$t])->lower()->plural($r->followUps()->where('type', $t)->count()))->implode(' · ')),
                Infolists\Components\TextEntry::make('fu_last')->label('Last follow-up')->state(fn (Lead $r) => $r->followUps()->latest('created_at')->first()?->created_at?->format('D j M Y, H:i'))->placeholder('Not followed up yet')
                    ->helperText(fn (Lead $r) => $r->followUps()->latest('created_at')->first()?->created_at?->diffForHumans()),
                Infolists\Components\TextEntry::make('fu_first')->label('First response')->state(fn (Lead $r) => $r->first_contacted_at
                    ? $r->first_contacted_at->diffForHumans($r->created_at, \Carbon\CarbonInterface::DIFF_ABSOLUTE).' after the enquiry' : null)->placeholder('Not contacted yet'),
                Infolists\Components\TextEntry::make('fu_age')->label('Open for')->state(fn (Lead $r) => $r->created_at?->diffForHumans(null, \Carbon\CarbonInterface::DIFF_ABSOLUTE)),
                Infolists\Components\ViewEntry::make('timeline')->label('Timeline')->view('filament.admin.leads.timeline')->columnSpanFull(),
            ])->columns(4),
            Infolists\Components\Section::make('Summary history')->icon('heroicon-o-document-duplicate')->collapsed()->schema([
                Infolists\Components\ViewEntry::make('summaries')->hiddenLabel()->view('filament.admin.leads.summaries')->columnSpanFull(),
            ])->visible(fn (Lead $r) => $r->activities()->where('type', 'summary')->exists()),
            Infolists\Components\Section::make('Enquiry')->schema([
                Infolists\Components\TextEntry::make('name'),
                Infolists\Components\TextEntry::make('business')->placeholder('-'),
                Infolists\Components\TextEntry::make('email')->copyable()->placeholder('-'),
                Infolists\Components\TextEntry::make('phone')->copyable()->placeholder('-'),
                Infolists\Components\TextEntry::make('service')->placeholder('-'),
                Infolists\Components\TextEntry::make('budget')->placeholder('-'),
                Infolists\Components\TextEntry::make('website')->placeholder('-'),
                Infolists\Components\TextEntry::make('postcode')->placeholder('-'),
                Infolists\Components\TextEntry::make('details')->label('Audit request')->columnSpanFull()->visible(fn (Lead $r) => filled($r->details))
                    ->state(fn (Lead $r) => new HtmlString(self::detailsHtml($r)))->html(),
                Infolists\Components\TextEntry::make('message')->placeholder('-')->columnSpanFull()->extraAttributes(['class' => 'whitespace-pre-line']),
                Infolists\Components\TextEntry::make('created_at')->label('Received')->dateTime('D j M Y, H:i'),
                Infolists\Components\TextEntry::make('originLabel')->label('Came from')->state(fn (Lead $r) => $r->originLabel()),
                Infolists\Components\TextEntry::make('form_path')->label('Form sent on')->placeholder('-'),
                Infolists\Components\TextEntry::make('landing_path')->label('First page visited')->placeholder('-'),
            ])->columns(3),
            Infolists\Components\Section::make('Follow-up')->schema([
                Infolists\Components\TextEntry::make('status')->formatStateUsing(fn ($state, Lead $r) => $r->statusLabel())->badge(),
                Infolists\Components\TextEntry::make('owner.name')->label('Assigned to')->placeholder('Nobody'),
                Infolists\Components\TextEntry::make('value')->label('Deal value (£)')->placeholder('-'),
                Infolists\Components\TextEntry::make('next_action_at')->label('Next follow-up')->date('D j M Y')->placeholder('-'),
                Infolists\Components\TextEntry::make('next_action')->label('What to do')->placeholder('-'),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        $me = fn () => auth()->id();
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('owner')->withCount('followUps')->withMax('followUps', 'created_at'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(['name', 'business', 'email', 'phone'])->sortable()->description(fn (Lead $r) => $r->business),
                Tables\Columns\TextColumn::make('email')->url(fn (Lead $r) => 'mailto:'.$r->email)->description(fn (Lead $r) => $r->phone)->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('service')->searchable()->wrap()->visibleFrom('lg')->description(fn (Lead $r) => $r->originLabel() ? 'via '.$r->originLabel() : null),
                Tables\Columns\SelectColumn::make('status')->options(fn () => Lead::statuses())->selectablePlaceholder(false)->disabled(fn () => ! static::allows('edit')),
                Tables\Columns\TextColumn::make('notes')->label('Summary')->limit(70)->wrap()->toggleable()
                    ->tooltip(fn (Lead $r) => $r->notes)->placeholder('-')->visibleFrom('xl'),
                Tables\Columns\TextColumn::make('follow_ups_count')->label('Followed up')->sortable()->alignCenter()
                    ->formatStateUsing(fn ($state) => $state.'×')->badge()->color(fn ($state) => $state ? 'success' : 'gray')
                    ->description(fn (Lead $r) => $r->follow_ups_max_created_at ? 'last '.\Illuminate\Support\Carbon::parse($r->follow_ups_max_created_at)->diffForHumans(short: true) : null)
                    ->tooltip('Calls, emails and meetings logged on the timeline'),
                Tables\Columns\TextColumn::make('form_path')->label('Page')->placeholder('-')->toggleable(isToggledHiddenByDefault: true)->searchable(),
                Tables\Columns\TextColumn::make('phone')->placeholder('-')->toggleable(isToggledHiddenByDefault: true)->searchable(),
                Tables\Columns\TextColumn::make('owner.name')->label('Assigned to')->placeholder('Nobody')->visibleFrom('md'),
                Tables\Columns\TextColumn::make('next_action_at')->label('Follow-up')->date('D j M')->sortable()->placeholder('-')
                    ->description(fn (Lead $r) => $r->next_action_at ? str($r->next_action)->limit(40) : null)
                    ->color(fn (Lead $r) => $r->isOverdue() ? 'danger' : ($r->next_action_at?->isToday() ? 'warning' : null))
                    ->icon(fn (Lead $r) => $r->isOverdue() ? 'heroicon-m-exclamation-triangle' : null),
                Tables\Columns\TextColumn::make('channel')->label('Source')->formatStateUsing(fn (Lead $r) => $r->originLabel())->placeholder('-')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->label('Received')->since()->sortable(),
            ])
            ->filtersFormColumns(2)
            ->filters([
                Tables\Filters\Filter::make('person')->columnSpanFull()->columns(3)
                    ->form([
                        Forms\Components\TextInput::make('name')->placeholder('Name or business'),
                        Forms\Components\TextInput::make('email')->placeholder('Email'),
                        Forms\Components\TextInput::make('phone')->placeholder('Phone number'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (filled($data['name'] ?? null)) $query->where(fn ($q) => $q->where('name', 'like', '%'.$data['name'].'%')->orWhere('business', 'like', '%'.$data['name'].'%'));
                        if (filled($data['email'] ?? null)) $query->where('email', 'like', '%'.$data['email'].'%');
                        if (filled($data['phone'] ?? null)) {
                            // Spaces, dashes and brackets are ignored, so "07700 900123" finds "07700900123".
                            $digits = preg_replace('/\D+/', '', (string) $data['phone']);
                            $query->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') LIKE ?", ['%'.ltrim($digits, '0').'%']);
                        }
                        return $query;
                    })
                    ->indicateUsing(fn (array $data) => array_values(array_filter([
                        filled($data['name'] ?? null) ? 'Name: '.$data['name'] : null, filled($data['email'] ?? null) ? 'Email: '.$data['email'] : null, filled($data['phone'] ?? null) ? 'Phone: '.$data['phone'] : null,
                    ]))),
                Tables\Filters\SelectFilter::make('form_path')->label('Form sent on (page)')->searchable()
                    ->options(fn () => Lead::query()->where('form_path', '!=', '')->distinct()->orderBy('form_path')->pluck('form_path', 'form_path')->all()),
                Tables\Filters\SelectFilter::make('landing_path')->label('First page visited')->searchable()
                    ->options(fn () => Lead::query()->where('landing_path', '!=', '')->distinct()->orderBy('landing_path')->pluck('landing_path', 'landing_path')->all()),
                Tables\Filters\SelectFilter::make('service')->searchable()
                    ->options(fn () => Lead::query()->where('service', '!=', '')->distinct()->orderBy('service')->pluck('service', 'service')->all()),
                Tables\Filters\Filter::make('received')->columns(2)
                    ->form([Forms\Components\DatePicker::make('from')->label('Received from'), Forms\Components\DatePicker::make('until')->label('Received until')])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d)))
                    ->indicateUsing(fn (array $data) => array_values(array_filter([($data['from'] ?? null) ? 'From '.$data['from'] : null, ($data['until'] ?? null) ? 'Until '.$data['until'] : null]))),
                Tables\Filters\SelectFilter::make('followed')->label('Followed up')
                    ->options(['0' => 'Never', '1' => 'Once', '2' => '2-4 times', '5' => '5 times or more'])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        '0' => $query->doesntHave('followUps'), '1' => $query->has('followUps', '=', 1),
                        '2' => $query->has('followUps', '>=', 2)->has('followUps', '<=', 4), '5' => $query->has('followUps', '>=', 5),
                        default => $query,
                    }),
                Tables\Filters\Filter::make('mine')->label('Assigned to me')->toggle()->query(fn (Builder $query) => $query->where('assigned_to', $me())),
                Tables\Filters\SelectFilter::make('status')->options(fn () => Lead::statuses())->multiple(),
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
                Tables\Actions\ViewAction::make()->label('View')->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()->label('Edit')->visible(fn (Lead $r) => static::canEdit($r)),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('assign')->label('Assign')->icon('heroicon-o-user-plus')->visible(fn () => self::canAssign())
                    ->form([Forms\Components\Select::make('user')->label('Assign to')->options(fn () => self::ownerOptions())->placeholder('Nobody')])
                    ->action(fn (\Illuminate\Support\Collection $records, array $data) => $records->each->update(['assigned_to' => $data['user'] ?: null]))
                    ->deselectRecordsAfterCompletion(),
                Tables\Actions\BulkAction::make('exportSelected')->label('Export selected (CSV)')->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn () => (bool) auth()->user()?->hasPerm('leads.export'))
                    ->action(fn (\Illuminate\Support\Collection $records) => self::csv($records->load('owner')->loadCount('followUps'), 'leads-selected')),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    /** "Export CSV" above the list: the leads matching the current search and filters, in the current order. */
    public static function exportAction(): Action
    {
        return Action::make('export')->label('Export CSV')->icon('heroicon-o-arrow-down-tray')->color('gray')->visible(fn () => (bool) auth()->user()?->hasPerm('leads.export'))
            ->modalHeading('Export leads as CSV')->modalSubmitActionLabel('Download')
            ->modalDescription('Opens in Excel, Numbers or Google Sheets.')
            ->form([Forms\Components\Radio::make('which')->hiddenLabel()->default('filtered')->options([
                'filtered' => 'The leads shown now (current search and filters)', 'all' => 'All leads',
            ])])
            ->action(function (array $data, $livewire) {
                $q = ($data['which'] ?? 'filtered') === 'filtered' && method_exists($livewire, 'getFilteredSortedTableQuery')
                    ? $livewire->getFilteredSortedTableQuery() : static::getEloquentQuery()->latest();
                return self::csv($q->with('owner')->withCount('followUps')->cursor(), 'leads');
            });
    }

    /** CSV of leads, with how many times each was followed up and its latest summary. */
    public static function csv(iterable $leads, string $name): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return Csv::download($name.'-'.now()->format('Y-m-d').'.csv',
            ['Received', 'Name', 'Business', 'Email', 'Phone', 'Service', 'Budget', 'Website', 'Postcode', 'Message', 'Form', 'Form page', 'Came from', 'Landing page', 'Campaign', 'Status', 'Assigned to', 'Value', 'Followed up (times)', 'First response', 'Next follow-up', 'What to do', 'Summary notes'],
            (function () use ($leads) {
                foreach ($leads as $l) yield [$l->created_at?->format('Y-m-d H:i'), $l->name, $l->business, $l->email, $l->phone, $l->service, $l->budget, $l->website, $l->postcode, $l->message,
                    $l->source, $l->form_path, $l->originLabel(), $l->landing_path, $l->utm_campaign, $l->statusLabel(), $l->owner?->name ?: $l->assignee, $l->value,
                    $l->follow_ups_count ?? $l->followUps()->count(), $l->first_contacted_at?->format('Y-m-d H:i'), $l->next_action_at?->format('Y-m-d'), $l->next_action, $l->notes];
            })());
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
