<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SiteIconResource\Pages;
use App\Filament\Support\Perms;
use App\Models\SiteIcon;
use App\Support\Site\Icons;
use App\Support\Site\SvgIcon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Website content > Icons: upload your own SVG icons (logos, partner badges, special symbols). They appear in
 * every icon picker next to the ~2,100 built-in line icons and ~3,700 brand logos.
 */
class SiteIconResource extends Resource
{
    use Perms;

    protected static string $section = 'media';
    protected static ?string $model = SiteIcon::class;
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Website content';
    protected static ?string $navigationLabel = 'Icons';
    protected static ?string $modelLabel = 'icon';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(120)->live(onBlur: true)
                    ->afterStateUpdated(fn (?string $state, Get $get, Set $set, ?SiteIcon $record) => $record ? null : $set('slug', Str::slug((string) $state))),
                Forms\Components\TextInput::make('slug')->label('Icon code')->prefix('custom:')->required()->maxLength(80)->rule('regex:/^[a-z0-9-]+$/')
                    ->unique(ignoreRecord: true)->disabledOn('edit')->helperText('Used to choose it; cannot change later.'),
                Forms\Components\FileUpload::make('file')->label('SVG file')->columnSpanFull()->required(fn (?SiteIcon $record) => ! $record)
                    ->acceptedFileTypes(['image/svg+xml'])->maxSize(Icons::MAX_UPLOAD_KB)->storeFiles(false)->dehydrated(true)
                    ->helperText('Format: SVG only, at most '.Icons::MAX_UPLOAD_KB.' KB, with a viewBox. A square drawing (e.g. 24 x 24) looks best. Scripts, links and embedded pictures are removed or refused.'),
                Forms\Components\Toggle::make('mono')->label('Use the text colour (like the built-in icons)')->default(true)->columnSpanFull()
                    ->helperText('On: the icon takes the colour of the text around it (red, white...). Off: it keeps its own colours (for multicolour logos).'),
                Forms\Components\Placeholder::make('preview')->visibleOn('edit')->content(fn (?SiteIcon $record) => $record ? new HtmlString(
                    '<div style="display:flex;gap:16px;align-items:center"><span style="color:#e8202f">'.Icons::svg($record->key(), 48).'</span><span style="color:#111;background:#f3f4f6;padding:8px;border-radius:8px">'.Icons::svg($record->key(), 32).'</span><span style="color:#fff;background:#111;padding:8px;border-radius:8px">'.Icons::svg($record->key(), 32).'</span><code>'.e($record->key()).'</code></div>') : ''),
            ]),
        ]);
    }

    /** Form data -> row: the uploaded file is checked, cleaned and stored as the icon's drawing. */
    public static function prepare(array $data, ?SiteIcon $record = null): array
    {
        $file = $data['file'] ?? null;
        if (is_array($file)) $file = reset($file) ?: null;
        unset($data['file']);
        $slug = $record?->slug ?? Str::slug((string) ($data['slug'] ?? ''));
        $mono = (bool) ($data['mono'] ?? true);
        $raw = $file instanceof TemporaryUploadedFile ? (string) file_get_contents($file->getRealPath()) : null;
        if ($raw === null && $record && $mono !== $record->mono) {
            throw ValidationException::withMessages(['data.file' => 'Upload the SVG again to change how it is coloured.']);
        }
        if ($raw !== null) {
            if ($file->getClientOriginalExtension() !== '' && strtolower($file->getClientOriginalExtension()) !== 'svg') throw ValidationException::withMessages(['data.file' => 'Upload an .svg file.']);
            try {
                $data += SvgIcon::parse($raw, $slug, $mono);
            } catch (\RuntimeException $e) {
                throw ValidationException::withMessages(['data.file' => $e->getMessage()]);
            }
        }
        $data['uploaded_by'] = (string) auth()->user()?->email;
        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('name')->contentGrid(['md' => 3, 'xl' => 6])
            ->description(new HtmlString('Your own icons, shown in every icon picker as well as the built-in <b>'.number_format(Icons::count('lucide')).' line icons</b> and <b>'.number_format(Icons::count('simple-icons')).' brand logos</b>.'))
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('preview')->label('')->state(fn (SiteIcon $r) => new HtmlString('<span style="color:#e8202f;display:block;padding:12px 0">'.Icons::svg($r->key(), 40).'</span>'))->html(),
                    Tables\Columns\TextColumn::make('name')->searchable()->weight('medium'),
                    Tables\Columns\TextColumn::make('slug')->formatStateUsing(fn ($state) => 'custom:'.$state)->color('gray')->size('xs')->searchable(),
                    Tables\Columns\TextColumn::make('size')->formatStateUsing(fn ($state) => number_format($state / 1024, 1).' KB')->color('gray')->size('xs'),
                ]),
            ])
            ->actions([Tables\Actions\EditAction::make()->iconButton(), Tables\Actions\DeleteAction::make()->iconButton()])
            ->emptyStateHeading('No uploaded icons yet')->emptyStateDescription('Upload an SVG to use it anywhere you can choose an icon.');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListSiteIcons::route('/'), 'create' => Pages\CreateSiteIcon::route('/create'), 'edit' => Pages\EditSiteIcon::route('/{record}/edit')];
    }
}
