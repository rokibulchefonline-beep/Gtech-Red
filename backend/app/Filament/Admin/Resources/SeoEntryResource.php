<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SeoEntryResource\Pages;
use App\Filament\Support\ImageField;
use App\Filament\Support\Perms;
use App\Models\SeoEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class SeoEntryResource extends Resource
{
    use Perms;

    protected static string $perm = 'content';
    protected static ?string $model = SeoEntry::class;
    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';
    protected static ?string $navigationGroup = 'Website content';
    protected static ?string $navigationLabel = 'SEO overrides';
    protected static ?string $modelLabel = 'SEO override';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('path')->label('Page address')->required()->maxLength(200)->placeholder('/services/search-engine-optimization')
                ->rule('regex:#^/[a-z0-9/_-]*$#')->disabledOn('edit')->helperText('The part of the URL after the domain, starting with /.'),
            Forms\Components\TextInput::make('title')->label('SEO title')->maxLength(120)->helperText('Leave empty to keep the page\'s own title.'),
            Forms\Components\Textarea::make('description')->label('Meta description')->rows(2)->maxLength(300),
            Forms\Components\TextInput::make('focus_keyword')->maxLength(80),
            Forms\Components\TextInput::make('canonical')->url()->maxLength(500),
            ImageField::make('og_image', 'Social sharing image'),
            Forms\Components\Toggle::make('noindex')->label('Hide this page from search engines'),
            Forms\Components\Section::make('Structured data (advanced)')->collapsed()->schema([
                Forms\Components\Toggle::make('schema_off')->label('Turn off the automatic schema for this page'),
                Forms\Components\Textarea::make('schema_custom')->label('Custom JSON-LD')->rows(8)->maxLength(20000)
                    ->helperText('Optional. A JSON object or array of schema.org items, added to the page.'),
            ]),
        ])->columns(1);
    }

    public static function beforeFill(array $data): array { return $data; }

    public static function beforeSave(array $data, $record = null): array
    {
        $custom = trim((string) ($data['schema_custom'] ?? ''));
        if ($custom !== '') {
            // Same rules the pages apply before printing it (an object or list of objects, each with an @type).
            if ($err = \App\Support\Site\Schema::validateCustom($custom)) throw ValidationException::withMessages(['data.schema_custom' => $err]);
            if (str_contains(strtolower($custom), '</script')) throw ValidationException::withMessages(['data.schema_custom' => 'Remove any script tags.']);
        }
        $data['schema_custom'] = $custom;
        if (! $record) {
            $data['path'] = '/'.trim((string) $data['path'], '/');
            $data['key'] = SeoEntry::keyFor($data['path']);
            if (SeoEntry::query()->whereKey($data['key'])->exists()) throw ValidationException::withMessages(['data.path' => 'This page already has an SEO override. Edit that one instead.']);
        }
        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('path')->columns([
            Tables\Columns\TextColumn::make('path')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('title')->limit(50)->placeholder('page default'),
            Tables\Columns\IconColumn::make('noindex')->label('Hidden')->boolean(),
            Tables\Columns\IconColumn::make('schema_custom')->label('Custom schema')->getStateUsing(fn (SeoEntry $r) => (bool) $r->schema_custom)->boolean(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeoEntry::route('/'),
            'create' => Pages\CreateSeoEntry::route('/create'),
            'edit' => Pages\EditSeoEntry::route('/{record}/edit'),
        ];
    }
}
