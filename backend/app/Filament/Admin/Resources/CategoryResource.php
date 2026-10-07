<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CategoryResource\Pages;
use App\Filament\Support\HooksDefault;
use App\Filament\Support\Perms;
use App\Models\Category;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    use HooksDefault, Perms;

    protected static string $section = 'structure';
    protected static ?string $model = Category::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Blog';
    protected static ?string $navigationLabel = 'Categories';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(60),
        ]);
    }

    public static function beforeSave(array $data, $record = null): array
    {
        $data['slug'] = Str::slug($data['name']);
        if (Category::query()->where('slug', $data['slug'])->when($record, fn ($q) => $q->whereKeyNot($record->getKey()))->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['data.name' => 'That category already exists.']);
        }
        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('name')->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('posts')->label('Posts')
                ->getStateUsing(fn (Category $r) => Post::query()->where('category', $r->name)->orWhereJsonContains('categories', $r->name)->count()),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategory::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
