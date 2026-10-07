<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PostResource\Pages;
use App\Filament\Support\HooksDefault;
use App\Filament\Support\ImageField;
use App\Filament\Support\Perms;
use App\Models\Category;
use App\Models\Post;
use App\Support\Site\Blog;
use Filament\Forms;
use FilamentTiptapEditor\Enums\TiptapOutput;
use FilamentTiptapEditor\TiptapEditor;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    use HooksDefault, Perms;

    protected static string $section = 'posts';

    /** "Edit any" edits every post; otherwise a writer edits only the posts they created. */
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $u = auth()->user();
        return (bool) $u && ($u->hasPerm('posts.edit') || ($u->hasPerm('posts.create') && $record->created_by === $u->id));
    }

    public static function canPublish(): bool
    {
        return (bool) auth()->user()?->hasPerm('posts.publish');
    }
    protected static ?string $model = Post::class;
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationGroup = 'Blog';
    protected static ?string $navigationLabel = 'Blog posts';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make([
                Forms\Components\Section::make('Post')->schema([
                    Forms\Components\TextInput::make('title')->required()->maxLength(160)->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set, ?string $state) => $get('slug') ? null : $set('slug', Str::slug((string) $state))),
                    Forms\Components\TextInput::make('slug')->label('URL slug')->required()->maxLength(160)->prefix('/blogs/')
                        ->unique(ignoreRecord: true)->rule('regex:/^[a-z0-9-]+$/')->helperText('Lowercase words separated by hyphens. If you change it later, the old address keeps working (a redirect is added).'),
                    Forms\Components\Textarea::make('excerpt')->rows(2)->maxLength(400)->helperText('One or two sentences shown on the blog list.'),
                    // Images get alt text (click an image, then the edit button); links can be edited in place.
                    TiptapEditor::make('body')->label('Article')->required()->columnSpanFull()
                        ->profile('blog')->disk('public')->directory('media')->output(TiptapOutput::Html)
                        ->maxContentWidth('3xl')->extraInputAttributes(['style' => 'min-height: 24rem;']),
                ]),
                Forms\Components\Section::make('Search engines')->collapsible()->schema([
                    Forms\Components\TextInput::make('focus_keyword')->maxLength(80)->live(onBlur: true),
                    Forms\Components\TextInput::make('meta_title')->label('SEO title')->maxLength(120)->live(onBlur: true)->helperText('Best under 60 characters. Leave empty to use the post title.'),
                    Forms\Components\Textarea::make('meta_description')->rows(2)->maxLength(300)->live(onBlur: true)->helperText('Best under 160 characters.'),
                    Forms\Components\TextInput::make('canonical')->url()->maxLength(500),
                    Forms\Components\Toggle::make('noindex')->label('Hide from search engines'),
                ]),
            ])->columnSpan(['lg' => 2]),
            Forms\Components\Group::make([
                Forms\Components\Section::make('SEO check')->schema([
                    Forms\Components\Placeholder::make('seo_check')->hiddenLabel()->content(fn (Forms\Get $get, ?Post $record) => self::seoCheck($get, $record)),
                ])->description('Updates as you edit the search fields, and fully on save.')->collapsible(),
                Forms\Components\Section::make('Publish')->schema([
                    Forms\Components\Select::make('status')->options(fn (?Post $record) => self::canPublish() || ($record && $record->status !== 'draft') ? ['draft' => 'Draft', 'published' => 'Published', 'scheduled' => 'Scheduled'] : ['draft' => 'Draft'])->default('draft')->required()->selectablePlaceholder(false)
                        ->disabled(fn (?Post $record) => ! self::canPublish() && $record && $record->status !== 'draft')->dehydrated(fn (?Post $record) => self::canPublish() || ! $record || $record->status === 'draft')
                        ->helperText(fn () => self::canPublish() ? null : 'Save as a draft; an editor publishes it.'),
                    Forms\Components\DateTimePicker::make('date')->label('Publish date')->helperText('Scheduled posts go live after this date.'),
                    Forms\Components\Select::make('visibility')->options(['public' => 'Public', 'private' => 'Private'])->default('public'),
                    Forms\Components\Toggle::make('featured')->label('Feature on the blog page'),
                    Forms\Components\TextInput::make('author')->default('GTech Editorial Team')->maxLength(80),
                ]),
                Forms\Components\Section::make('Categories and tags')->schema([
                    Forms\Components\Select::make('categories')->multiple()->options(fn () => Category::query()->orderBy('name')->pluck('name', 'name'))
                        ->createOptionForm([Forms\Components\TextInput::make('name')->required()->maxLength(60)])
                        ->createOptionUsing(fn (array $data) => Category::firstOrCreate(['slug' => Str::slug($data['name'])], ['name' => $data['name']])->name),
                    Forms\Components\TagsInput::make('tags')->separator(','),
                ]),
                Forms\Components\Section::make('Featured image')->schema([
                    ImageField::make('image'),
                    Forms\Components\TextInput::make('image_alt')->label('Alt text')->maxLength(200)->helperText('Describe the image for screen readers and Google.'),
                ]),
            ])->columnSpan(['lg' => 1]),
        ])->columns(3);
    }

    /** The editor's SEO score and checks for the post as it is in the form (SEO > SEO audit uses the same checks). */
    public static function seoCheck(Forms\Get $get, ?Post $record): \Illuminate\Support\HtmlString
    {
        // Form values are normally strings; the editor's state can briefly be its JSON document, so fall back to the saved post.
        $v = fn (string $k) => is_scalar($x = $get($k)) ? (string) $x : (string) ($record?->{$k} ?? '');
        $p = new Post(['title' => $v('title'), 'slug' => $v('slug'), 'body' => $v('body'), 'excerpt' => $v('excerpt'),
            'focus_keyword' => $v('focus_keyword'), 'meta_title' => $v('meta_title'), 'meta_description' => $v('meta_description'),
            'image' => $v('image'), 'image_alt' => $v('image_alt'), 'noindex' => (bool) $get('noindex')]);
        $p->format = 'html';
        $p->id = $record?->id;
        $r = \App\Support\Seo\Audit::post($p);
        $col = fn (string $l) => ['pass' => '#16a34a', 'warn' => '#d97706', 'fail' => '#dc2626'][$l];
        $mark = ['pass' => '✓', 'warn' => '!', 'fail' => '✕'];
        $tone = $r['score'] >= 85 ? 'pass' : ($r['score'] >= 65 ? 'warn' : 'fail');
        $html = '<p style="font-size:28px;font-weight:600;color:'.$col($tone).'">'.$r['score'].'<span style="font-size:14px;color:#888">/100</span></p><ul style="font-size:13px;line-height:1.45;margin-top:6px">';
        foreach ($r['checks'] as $c) {
            $html .= '<li style="display:flex;gap:6px;margin:5px 0"><b style="color:'.$col($c['level']).';width:12px;flex:none">'.$mark[$c['level']].'</b><span>'.e($c['label'])
                .($c['level'] !== 'pass' && $c['hint'] ? '<br><small style="opacity:.7">'.e($c['hint']).'</small>' : '').'</span></li>';
        }
        return new \Illuminate\Support\HtmlString($html.'</ul>');
    }

    /** Markdown posts (imported or seeded) open as HTML, so the editor shows real headings and paragraphs. */
    public static function beforeFill(array $data): array
    {
        if (($data['format'] ?? 'html') !== 'html') $data['body'] = Blog::markdownToHtml((string) ($data['body'] ?? ''));
        $data['body'] = \App\Support\Html::listsForEditor($data['body'] ?? '');
        return $data;
    }

    public static function beforeSave(array $data, $record = null): array
    {
        $data['format'] = 'html';
        if (is_string($data['body'] ?? null)) $data['body'] = \App\Support\Html::listsForSite($data['body']);
        $data['categories'] = array_values($data['categories'] ?? []);
        $data['category'] = $data['categories'][0] ?? 'Insights';
        if (! self::canPublish()) {
            // Writers without "Publish" keep new posts as drafts and cannot change a live post's status.
            if (! $record) $data['status'] = 'draft';
            else unset($data['status']);
        }
        if (! $record) $data['created_by'] = auth()->id();
        if (($data['status'] ?? $record?->status) === 'published' && empty($data['date']) && empty($record?->date)) $data['date'] = now();
        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('')->getStateUsing(fn (Post $r) => $r->image ? ImageField::preview($r->image) : null)->width(64)->height(40)->visibleFrom('md'),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()->wrap()->description(fn (Post $r) => '/blogs/'.$r->slug),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state) => ['published' => 'success', 'scheduled' => 'info'][$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('category')->searchable()->toggleable()->visibleFrom('lg'),
                Tables\Columns\IconColumn::make('featured')->boolean()->toggleable()->visibleFrom('lg'),
                Tables\Columns\TextColumn::make('date')->dateTime('d M Y')->sortable()->visibleFrom('md'),
                Tables\Columns\TextColumn::make('updated_at')->since()->label('Edited')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published', 'scheduled' => 'Scheduled']),
                Tables\Filters\TernaryFilter::make('featured'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')->iconButton()->tooltip('View on the website')
                    ->url(fn (Post $r) => \App\Filament\Support\SiteLink::to('/blogs/'.$r->slug), shouldOpenInNewTab: true),
                Tables\Actions\EditAction::make()->iconButton()->tooltip('Edit'),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPost::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
