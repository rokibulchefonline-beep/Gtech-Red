<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AuthorResource\Pages;
use App\Filament\Support\HooksDefault;
use App\Filament\Support\ImageField;
use App\Filament\Support\Perms;
use App\Models\Author;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/** Blog authors: name, role, bio, photo and profiles. Shown on their posts and on their author page. */
class AuthorResource extends Resource
{
    use HooksDefault, Perms;

    protected static string $section = 'structure';
    protected static ?string $model = Author::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Blog';
    protected static ?string $navigationLabel = 'Authors';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->description('A real person with a short bio and profile links helps Google and AI assistants trust the article (E-E-A-T).')->columns(2)->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(80)->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('job_title')->label('Job title')->maxLength(100)->placeholder('e.g. Head of SEO'),
                Forms\Components\Textarea::make('bio')->rows(4)->maxLength(800)->columnSpanFull()
                    ->helperText('2-4 sentences: experience, what they specialise in, results or qualifications.'),
                Forms\Components\TagsInput::make('expertise')->label('Topics they know')->placeholder('Add a topic')->columnSpanFull(),
                Forms\Components\Group::make([ImageField::make('photo', 'Photo', preset: 'avatar')])->columnSpanFull(),
                Forms\Components\TextInput::make('linkedin')->label('LinkedIn profile')->url()->maxLength(300)->placeholder('https://www.linkedin.com/in/...'),
                Forms\Components\TextInput::make('x')->label('X (Twitter) profile')->url()->maxLength(300),
                Forms\Components\TextInput::make('website')->label('Personal website')->url()->maxLength(300),
                Forms\Components\TextInput::make('sort')->numeric()->default(0)->label('Order'),
            ]),
            \App\Filament\Support\SchemaPanel::section(fn (Forms\Get $get) => '/blogs/author/'.($get('slug') ?: \Illuminate\Support\Str::slug((string) $get('name'))), fn (Forms\Get $get) => (string) $get('name'), 'ProfilePage, Person, BreadcrumbList'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort')->columns([
            Tables\Columns\ImageColumn::make('photo')->circular()->defaultImageUrl(fn (Author $r) => (new \App\Filament\Support\InitialsAvatar)->get($r)),
            Tables\Columns\TextColumn::make('name')->searchable()->sortable()->description(fn (Author $r) => $r->job_title),
            Tables\Columns\TextColumn::make('posts')->label('Posts')->getStateUsing(fn (Author $r) => Post::query()->where('author', $r->name)->count()),
            Tables\Columns\TextColumn::make('slug')->label('Page')->formatStateUsing(fn ($state) => '/blogs/author/'.$state)->color('gray'),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuthors::route('/'),
            'create' => Pages\CreateAuthor::route('/create'),
            'edit' => Pages\EditAuthor::route('/{record}/edit'),
        ];
    }
}
