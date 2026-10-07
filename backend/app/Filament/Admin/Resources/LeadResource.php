<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Support\Perms;
use App\Filament\Admin\Resources\LeadResource\Pages;
use App\Filament\Support\Csv;
use App\Filament\Support\HooksDefault;
use App\Models\Lead;
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

    public const STATUSES = ['new' => 'New', 'contacted' => 'Contacted', 'qualified' => 'Qualified', 'won' => 'Won', 'lost' => 'Lost'];

    protected static string $section = 'leads';
    protected static ?string $model = Lead::class;
    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationGroup = 'Leads';
    protected static ?string $navigationLabel = 'Leads';
    protected static ?int $navigationSort = 1;


    public static function canCreate(): bool { return false; }

    public static function getNavigationBadge(): ?string
    {
        $n = Lead::query()->where('status', 'new')->count();
        return $n ? (string) $n : null;
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
                    if (! $v) return 'Not known (sent before analytics, or from a browser that blocks scripts)';
                    $bits = array_filter([$v->source.($v->channel !== $v->source ? ' ('.($v->channel === 'AI' ? 'AI assistant' : $v->channel).')' : ''),
                        $v->utm_campaign ? 'campaign "'.$v->utm_campaign.'"' : '', 'landed on '.$v->landing_path, $v->pageviews.' '.str('page')->plural($v->pageviews).' viewed', $v->device]);
                    return implode(' · ', $bits);
                })->columnSpanFull(),
                Forms\Components\Placeholder::make('when')->label('Received')->content(fn (?Lead $r) => $r?->created_at?->format('d M Y, H:i').' via '.$r?->source),
            ])->columns(2),
            Forms\Components\Section::make('Follow-up')->schema([
                Forms\Components\Select::make('status')->options(self::STATUSES)->required(),
                Forms\Components\TextInput::make('assignee')->label('Assigned to')->maxLength(80),
                Forms\Components\TextInput::make('value')->label('Deal value (£)')->numeric()->minValue(0),
                Forms\Components\Textarea::make('notes')->rows(4)->maxLength(5000)->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->description(fn (Lead $r) => $r->business),
                Tables\Columns\TextColumn::make('email')->searchable()->url(fn (Lead $r) => 'mailto:'.$r->email)->description(fn (Lead $r) => $r->phone)->visibleFrom('md'),
                Tables\Columns\TextColumn::make('service')->searchable()->wrap()->visibleFrom('lg'),
                Tables\Columns\SelectColumn::make('status')->options(self::STATUSES)->selectablePlaceholder(false),
                Tables\Columns\TextColumn::make('assignee')->toggleable()->visibleFrom('lg'),
                Tables\Columns\TextColumn::make('created_at')->label('Received')->since()->sortable(),
            ])
            ->filters([Tables\Filters\SelectFilter::make('status')->options(self::STATUSES)])
            ->actions([Tables\Actions\EditAction::make()->label('Open'), Tables\Actions\DeleteAction::make()]);
    }

    public static function exportAction(): Action
    {
        return Action::make('export')->label('Export CSV')->icon('heroicon-o-arrow-down-tray')->color('gray')->visible(fn () => (bool) auth()->user()?->hasPerm('leads.export'))
            ->action(fn () => Csv::download('leads-'.now()->format('Y-m-d').'.csv',
                ['Received', 'Name', 'Business', 'Email', 'Phone', 'Service', 'Budget', 'Website', 'Postcode', 'Message', 'Source', 'Status', 'Assignee', 'Value', 'Notes'],
                Lead::query()->latest()->cursor()->map(fn (Lead $l) => [$l->created_at?->format('Y-m-d H:i'), $l->name, $l->business, $l->email, $l->phone, $l->service, $l->budget, $l->website, $l->postcode, $l->message, $l->source, $l->status, $l->assignee, $l->value, $l->notes])));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLead::route('/'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
