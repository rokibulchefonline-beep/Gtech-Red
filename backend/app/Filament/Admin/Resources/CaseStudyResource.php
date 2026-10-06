<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CaseStudyResource\Pages;
use App\Filament\Support\HooksDefault;
use App\Filament\Support\ImageField;
use App\Filament\Support\Perms;
use App\Models\CaseStudy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CaseStudyResource extends Resource
{
    use HooksDefault, Perms;

    protected static string $perm = 'content';
    protected static ?string $model = CaseStudy::class;
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationGroup = 'Website content';
    protected static ?string $navigationLabel = 'Case studies';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make([
                Forms\Components\Section::make('Case study')->schema([
                    Forms\Components\TextInput::make('title')->required()->maxLength(120)->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set, ?string $state) => $get('slug') ? null : $set('slug', Str::slug((string) $state))),
                    Forms\Components\TextInput::make('slug')->label('URL slug')->required()->maxLength(120)->prefix('/case-studies/')->unique(ignoreRecord: true)->rule('regex:/^[a-z0-9-]+$/')
                        ->helperText('If you change it later, the old address keeps working (a redirect is added).'),
                    Forms\Components\Textarea::make('excerpt')->label('Short summary')->rows(2)->maxLength(300),
                    Forms\Components\Repeater::make('metrics')->label('Headline numbers')->schema([
                        Forms\Components\TextInput::make('value')->placeholder('+150%')->required()->maxLength(24),
                        Forms\Components\TextInput::make('label')->placeholder('Organic traffic')->required()->maxLength(40),
                    ])->columns(2)->maxItems(4)->defaultItems(0)->reorderable()->helperText('Shown on the case study card and page banner.'),
                    Forms\Components\Textarea::make('challenge')->rows(4)->maxLength(5000),
                    Forms\Components\Textarea::make('solution')->rows(4)->maxLength(5000),
                    Forms\Components\TagsInput::make('results')->label('Results (one per line item)')->placeholder('Add a result and press Enter'),
                    Forms\Components\Fieldset::make('Client quote')->schema([
                        Forms\Components\Textarea::make('quote.text')->label('Quote')->rows(2)->maxLength(500)->columnSpanFull(),
                        Forms\Components\TextInput::make('quote.name')->label('Name')->maxLength(80),
                        Forms\Components\TextInput::make('quote.role')->label('Role')->maxLength(120),
                    ]),
                ]),
                Forms\Components\Section::make('Search engines')->collapsible()->collapsed()->schema([
                    Forms\Components\TextInput::make('focus_keyword')->maxLength(80),
                    Forms\Components\TextInput::make('meta_title')->label('SEO title')->maxLength(120),
                    Forms\Components\Textarea::make('meta_description')->rows(2)->maxLength(300),
                ]),
            ])->columnSpan(['lg' => 2]),
            Forms\Components\Group::make([
                Forms\Components\Section::make('Publish')->schema([
                    Forms\Components\Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published'])->default('draft')->required(),
                    Forms\Components\TextInput::make('order')->numeric()->default(100)->helperText('Lower numbers show first.'),
                ]),
                Forms\Components\Section::make('Client')->schema([
                    Forms\Components\TextInput::make('client')->maxLength(120),
                    Forms\Components\TextInput::make('industry')->maxLength(80),
                    Forms\Components\TextInput::make('duration')->maxLength(60)->placeholder('6 months'),
                    Forms\Components\TextInput::make('website')->url()->maxLength(500),
                    Forms\Components\TagsInput::make('services')->placeholder('e.g. Search Engine Optimization'),
                ]),
                Forms\Components\Section::make('Images')->schema([
                    ImageField::make('image', 'Banner image'),
                    Forms\Components\TextInput::make('image_alt')->label('Alt text')->maxLength(200),
                    ImageField::make('logo', 'Client logo'),
                ]),
            ])->columnSpan(['lg' => 1]),
        ])->columns(3);
    }

    public static function beforeSave(array $data, $record = null): array
    {
        $data['client'] = $data['client'] ?: $data['title'];
        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->reorderable('order')
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('')->getStateUsing(fn (CaseStudy $r) => $r->image ? ImageField::preview($r->image) : null)->width(64)->height(40)->visibleFrom('md'),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()->description(fn (CaseStudy $r) => $r->industry),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state) => $state === 'published' ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('metrics')->label('Numbers')->getStateUsing(fn (CaseStudy $r) => collect($r->metrics ?? [])->map(fn ($m) => ($m['value'] ?? '').' '.($m['label'] ?? ''))->implode(' · '))->wrap()->visibleFrom('lg'),
                Tables\Columns\TextColumn::make('order')->sortable()->visibleFrom('md'),
            ])
            ->filters([Tables\Filters\SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published'])])
            ->actions([
                Tables\Actions\Action::make('view')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')->iconButton()->tooltip('View on the website')
                    ->url(fn (CaseStudy $r) => \App\Filament\Support\SiteLink::to('/case-studies/'.$r->slug), shouldOpenInNewTab: true),
                Tables\Actions\EditAction::make()->iconButton()->tooltip('Edit'),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCaseStudy::route('/'),
            'create' => Pages\CreateCaseStudy::route('/create'),
            'edit' => Pages\EditCaseStudy::route('/{record}/edit'),
        ];
    }
}
