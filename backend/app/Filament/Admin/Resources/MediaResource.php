<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MediaResource\Pages;
use App\Filament\Support\HooksDefault;
use App\Filament\Support\Perms;
use App\Models\Media;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class MediaResource extends Resource
{
    use HooksDefault, Perms;

    protected static string $perm = 'content';
    protected static ?string $model = Media::class;
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Website content';
    protected static ?string $navigationLabel = 'Media library';
    protected static ?string $modelLabel = 'image';
    protected static ?int $navigationSort = 6;

    public static function canCreate(): bool { return false; }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(160),
            Forms\Components\Placeholder::make('address')->content(fn (?Media $r) => $r?->url),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->contentGrid(['md' => 3, 'xl' => 5])->columns([
            Tables\Columns\Layout\Stack::make([
                Tables\Columns\ImageColumn::make('url')->label('')->getStateUsing(fn (Media $r) => $r->url)->height(120)->extraImgAttributes(['style' => 'object-fit:contain;width:100%']),
                Tables\Columns\TextColumn::make('name')->searchable()->limit(30),
                Tables\Columns\TextColumn::make('size')->formatStateUsing(fn ($state) => number_format($state / 1024, 0).' KB')->color('gray')->size('xs'),
            ]),
        ])->actions([
            Tables\Actions\Action::make('copy')->label('Copy address')->icon('heroicon-o-clipboard')->color('gray')
                ->action(fn () => null)->extraAttributes(fn (Media $r) => ['x-on:click' => 'window.navigator.clipboard.writeText('.json_encode($r->url).'); $tooltip("Copied")']),
            Tables\Actions\DeleteAction::make()->after(fn (Media $r) => Storage::disk('public')->delete($r->path)),
        ]);
    }

    public static function uploadAction(): Action
    {
        return Action::make('upload')->label('Upload images')->icon('heroicon-o-arrow-up-tray')
            ->form([Forms\Components\FileUpload::make('files')->multiple()->image()->maxSize(4096)->disk('public')->directory('media')->storeFileNamesIn('names')
                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/gif', 'image/svg+xml'])->required()])
            ->action(function (array $data) {
                foreach ($data['files'] as $path) {
                    Media::create(['name' => $data['names'][$path] ?? basename($path), 'type' => Storage::disk('public')->mimeType($path) ?: 'image/png',
                        'size' => Storage::disk('public')->size($path), 'path' => $path, 'uploaded_by' => (string) auth()->user()?->email]);
                }
                Notification::make()->title(count($data['files']).' image(s) uploaded')->success()->send();
            });
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListMedia::route('/'), 'edit' => Pages\EditMedia::route('/{record}/edit')];
    }
}
