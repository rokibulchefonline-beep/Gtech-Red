<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SchemaRuleResource\Pages;
use App\Filament\Support\Perms;
use App\Filament\Support\SchemaPanel;
use App\Models\SchemaRule;
use App\Support\Site\Schema;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * SEO > Additional schema: your own JSON-LD for every page, a whole type of page (all blog posts, all services,
 * all case studies...) or one address. One page's own schema is still set in its editor (Schema markup).
 */
class SchemaRuleResource extends Resource
{
    use Perms;

    protected static string $section = 'seo';
    protected static ?string $model = SchemaRule::class;
    protected static ?string $navigationIcon = 'heroicon-o-code-bracket-square';
    protected static ?string $navigationGroup = 'SEO';
    protected static ?string $navigationLabel = 'Additional schema';
    protected static ?string $modelLabel = 'schema rule';
    protected static ?int $navigationSort = 6;

    /** Extra ready-made templates for site-wide rules. */
    public const TEMPLATES = [
        'Reviews' => ['label' => 'Client testimonials as reviews', 'json' => ['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => 'GTech Digital', 'url' => 'https://www.gtechdigital.co.uk', 'review' => '{testimonials}']],
        'Speakable' => ['label' => 'Speakable (voice assistants)', 'json' => ['@context' => 'https://schema.org', '@type' => 'WebPage', 'url' => '{url}', 'speakable' => ['@type' => 'SpeakableSpecification', 'cssSelector' => ['h1', '.sp-lead']]]],
        'Course' => ['label' => 'Course or training', 'json' => ['@context' => 'https://schema.org', '@type' => 'Course', 'name' => '{name}', 'description' => '{description}', 'provider' => ['@type' => 'Organization', 'name' => 'GTech Digital']]],
        'JobPosting' => ['label' => 'Job posting', 'json' => ['@context' => 'https://schema.org', '@type' => 'JobPosting', 'title' => 'Job title', 'datePosted' => '2026-01-01', 'employmentType' => 'FULL_TIME',
            'hiringOrganization' => ['@type' => 'Organization', 'name' => 'GTech Digital'], 'jobLocation' => ['@type' => 'Place', 'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'London', 'addressCountry' => 'GB']], 'description' => 'About the role.']],
    ];

    public static function form(Form $form): Form
    {
        $templates = array_map(fn ($t) => $t['label'], SchemaPanel::TEMPLATES + self::TEMPLATES);
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')->label('Name (for you)')->required()->maxLength(120)->placeholder('e.g. Reviews on every service page'),
                Forms\Components\Select::make('scope')->label('Add it to')->options(SchemaRule::SCOPES)->default('all')->required()->live()->selectablePlaceholder(false),
                Forms\Components\TextInput::make('path')->label(fn (Get $get) => $get('scope') === 'prefix' ? 'Addresses starting with' : 'Page address')
                    ->placeholder('/services/local-seo')->maxLength(300)->rule('regex:#^/[A-Za-z0-9/_-]*$#')
                    ->visible(fn (Get $get) => in_array($get('scope'), ['path', 'prefix'], true))->required(fn (Get $get) => in_array($get('scope'), ['path', 'prefix'], true)),
                Forms\Components\Toggle::make('active')->label('On')->default(true),
            ])->columns(2),
            Forms\Components\Section::make('JSON-LD')->schema([
                Forms\Components\Select::make('template')->label('Start from a template')->placeholder('Choose one…')->options($templates)->live()->dehydrated(false)
                    ->afterStateUpdated(function (?string $state, Set $set) {
                        $t = (SchemaPanel::TEMPLATES + self::TEMPLATES)[$state] ?? null;
                        if ($t) $set('json', json_encode($t['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                        $set('template', null);
                    }),
                Forms\Components\Textarea::make('json')->hiddenLabel()->required()->rows(16)->maxLength(20000)
                    ->extraInputAttributes(['style' => 'font-family:ui-monospace,Menlo,Consolas,monospace;font-size:13px', 'spellcheck' => 'false'])
                    ->rule(fn () => fn ($attr, $v, $fail) => ($err = Schema::validateCustom((string) $v)) ? $fail($err) : (str_contains(strtolower((string) $v), '</script') ? $fail('Remove any script tags.') : null))
                    ->helperText('Placeholders filled on each page: {url} (the page address), {name} (its title), {description}, and {testimonials} (your visible testimonials as Review items). Google does not show review stars for reviews of your own business, but AI assistants and other search engines still read them.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort')->reorderable('sort')->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->description(fn (SchemaRule $r) => (json_decode((string) $r->json, true)['@type'] ?? 'Several items')),
            Tables\Columns\TextColumn::make('scope')->label('Added to')->formatStateUsing(fn (SchemaRule $r) => in_array($r->scope, ['path', 'prefix'], true) ? (SchemaRule::SCOPES[$r->scope]).' '.$r->path : SchemaRule::SCOPES[$r->scope] ?? $r->scope)->wrap(),
            Tables\Columns\ToggleColumn::make('active')->label('On')->disabled(fn () => ! static::allows('edit')),
            Tables\Columns\TextColumn::make('updated_at')->label('Changed')->since(),
        ])->actions([
            Tables\Actions\EditAction::make()->iconButton(),
            Tables\Actions\DeleteAction::make()->iconButton(),
        ])->emptyStateHeading('No additional schema yet')
            ->emptyStateDescription('Add JSON-LD to every page, all blog posts, all services, all case studies or one address. Each page already has automatic schema.');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListSchemaRules::route('/'), 'create' => Pages\CreateSchemaRule::route('/create'), 'edit' => Pages\EditSchemaRule::route('/{record}/edit')];
    }
}
