<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PageResource\Pages;
use App\Filament\Support\ImageField;
use App\Filament\Support\Perms;
use App\Models\Page;
use App\Support\Content;
use App\Support\Html;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

/**
 * Edit every page of the website: main pages, services, industries and legal pages. The database holds the full
 * page; this form edits its words and images while the layout (section types, icons, order) stays as designed.
 */
class PageResource extends Resource
{
    use Perms;

    private const ITEM_FIELDS = ['cards', 'steps', 'stats', 'reviews', 'items'];
    private const ITEM_KEYS = ['title', 'text', 'value', 'label', 'name', 'role'];
    private const RICH = ['bold', 'italic', 'underline', 'link', 'undo', 'redo'];
    private const KINDS = ['page' => 'Main page', 'service' => 'Service', 'industry' => 'Industry', 'legal' => 'Legal'];

    protected static string $section = 'pages';
    protected static ?string $model = Page::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Website content';
    protected static ?string $navigationLabel = 'Pages';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';

    public static function canCreate(): bool { return false; }

    public static function canDelete($record): bool { return false; }

    private static function rich(string $name, string $label = ''): Forms\Components\RichEditor
    {
        $f = Forms\Components\RichEditor::make($name)->toolbarButtons(self::RICH);
        return $label ? $f->label($label) : $f;
    }

    private static function has(string $f): \Closure
    {
        return fn (Get $get) => in_array($f, (array) $get('has'), true);
    }

    public static function form(Form $form): Form
    {
        $isHome = fn (Get $get) => $get('key') === 'page~home';
        return $form->schema([
            Forms\Components\Hidden::make('key'), Forms\Components\Hidden::make('kind'),
            Forms\Components\Placeholder::make('about')->hiddenLabel()->columnSpanFull()->content(fn (?Page $r) => new HtmlString(
                '<p style="font-size:14px">Editing <b>'.e($r?->name).'</b> <span style="color:#888">'.e($r?->path).'</span> · <a style="color:#e8202f" target="_blank" href="'.e(self::siteUrl($r)).'">View on the website</a>'
                .'<br>Headings: put words in [[double brackets]] to colour them red, e.g. <code>Local SEO [[Agency UK]]</code>.</p>')),
            Forms\Components\Tabs::make()->columnSpanFull()->persistTabInQueryString()->tabs([
                Forms\Components\Tabs\Tab::make('Hero')->schema([
                    Forms\Components\TextInput::make('hero.h1')->label('Main heading (H1)')->required()->maxLength(200)
                        ->helperText(fn (Get $get) => $get('key') === 'page~home' ? 'Use | to start a new line, e.g. Digital Marketing|Agency [[for Scalable]]|[[Growth]]' : 'Use [[ ]] around the words to colour red.'),
                    Forms\Components\TextInput::make('hero.keyword')->label('Main keyword')->maxLength(80)->visible(fn (Get $get) => in_array($get('kind'), ['service', 'industry'], true)),
                    self::rich('hero.lead', 'Opening description'),
                    Forms\Components\TagsInput::make('hero.points')->label('Highlights (short points)')->placeholder('Add a point and press Enter')->hidden($isHome)
                        ->visible(fn (Get $get) => $get('kind') !== 'legal'),
                    Forms\Components\TextInput::make('updated')->label('Last updated (shown on the page)')->maxLength(40)->visible(fn (Get $get) => $get('kind') === 'legal'),
                ]),
                Forms\Components\Tabs\Tab::make('Sections')->schema([
                    Forms\Components\Repeater::make('secs')->hiddenLabel()->addable(false)->deletable(false)->reorderable(false)->collapsible()->collapsed()
                        ->itemLabel(fn (array $state) => ($state['label'] ?? '').(! empty($state['heading']) ? ': '.str_replace(['[[', ']]'], '', $state['heading']) : ''))
                        ->schema([
                            Forms\Components\Hidden::make('idx'), Forms\Components\Hidden::make('label'), Forms\Components\Hidden::make('has'),
                            Forms\Components\TextInput::make('heading')->maxLength(240)->visible(self::has('heading')),
                            self::rich('intro', 'Intro text')->visible(self::has('intro')),
                            self::rich('text', 'Text')->visible(self::has('text')),
                            Forms\Components\Repeater::make('paras')->label('Paragraphs')->simple(self::rich('html'))->defaultItems(0)->visible(self::has('paras'))->addActionLabel('Add paragraph'),
                            Forms\Components\Repeater::make('bullets')->label('Bullet points')->simple(Forms\Components\TextInput::make('html')->maxLength(800))->defaultItems(0)->visible(self::has('bullets'))->addActionLabel('Add bullet'),
                            Forms\Components\Repeater::make('after')->label('Paragraphs after the list')->simple(self::rich('html'))->defaultItems(0)->visible(self::has('after'))->addActionLabel('Add paragraph'),
                            Forms\Components\Group::make([ImageField::make('image', 'Section image'), Forms\Components\TextInput::make('alt')->label('Image alt text')->maxLength(200)])->visible(self::has('image')),
                            Forms\Components\Repeater::make('items')->label('Cards, steps and numbers (text only, the layout stays the same)')
                                ->addable(false)->deletable(false)->reorderable(false)->grid(2)->visible(self::has('items'))
                                ->schema([
                                    Forms\Components\Hidden::make('_f'), Forms\Components\Hidden::make('_k'),
                                    ...array_map(fn ($k) => Forms\Components\TextInput::make($k)->label(ucfirst($k))->maxLength(800)
                                        ->visible(fn (Get $get) => in_array($k, (array) $get('_k'), true)), self::ITEM_KEYS),
                                ]),
                        ]),
                ]),
                Forms\Components\Tabs\Tab::make('FAQs')->visible(fn (Get $get) => in_array($get('kind'), ['service', 'industry', 'page'], true) && $get('key') !== 'page~home')->schema([
                    Forms\Components\Placeholder::make('faqhelp')->hiddenLabel()->content('These also feed Google\'s FAQ rich results. Keep answers to 40-60 words.'),
                    Forms\Components\Repeater::make('faqs')->hiddenLabel()->schema([
                        Forms\Components\TextInput::make('q')->label('Question')->required()->maxLength(250),
                        self::rich('a', 'Answer')->required(),
                    ])->collapsible()->collapsed()->itemLabel(fn (array $state) => $state['q'] ?? null)->addActionLabel('Add question')->reorderable(),
                ]),
                Forms\Components\Tabs\Tab::make('Search appearance')->schema([
                    Forms\Components\TextInput::make('focus_keyword')->maxLength(80),
                    Forms\Components\TextInput::make('meta_title')->label('SEO title')->maxLength(160)->helperText('Best under 60 characters.'),
                    Forms\Components\Textarea::make('meta_description')->rows(3)->maxLength(320)->helperText('Best under 160 characters.'),
                ]),
            ]),
        ]);
    }

    private static function siteUrl(?Page $r): string
    {
        return \App\Filament\Support\SiteLink::to($r?->path ?? '/');
    }

    /** Database row -> form state. */
    public static function beforeFill(array $data): array
    {
        $state = [
            'key' => $data['key'], 'kind' => $data['kind'], 'meta_title' => $data['meta_title'], 'meta_description' => $data['meta_description'],
            'focus_keyword' => $data['focus_keyword'], 'hero' => (array) $data['hero'], 'faqs' => (array) $data['faqs'],
            'updated' => $data['data']['updated'] ?? '', 'secs' => [],
        ];
        foreach ((array) $data['sections'] as $i => $s) {
            $has = array_values(array_filter(['heading', 'intro', 'text', 'paras', 'bullets', 'after', 'image'], fn ($f) => array_key_exists($f, $s)));
            $items = [];
            foreach (self::ITEM_FIELDS as $f) {
                foreach ((array) ($s[$f] ?? []) as $j => $it) {
                    $keys = array_values(array_intersect(self::ITEM_KEYS, array_keys((array) $it)));
                    if ($keys) $items[] = ['_f' => "$f:$j", '_k' => $keys] + array_intersect_key((array) $it, array_flip($keys));
                }
            }
            if ($items) $has[] = 'items';
            if (! $has) continue;
            $state['secs'][] = ['idx' => $i, 'label' => ($s['nav'] ?? '') ?: ucfirst((string) ($s['type'] ?? 'Section')), 'has' => $has, 'items' => $items]
                + array_intersect_key($s, array_flip(['heading', 'intro', 'text', 'paras', 'bullets', 'after', 'image', 'alt']));
        }
        return $state;
    }

    /** Form state -> database row (only the words and images change; the layout fields are kept). */
    public static function beforeSave(array $data, Page $record): array
    {
        $hero = (array) $record->hero;
        $h = (array) ($data['hero'] ?? []);
        $hero['h1'] = trim((string) ($h['h1'] ?? ''));
        if (array_key_exists('keyword', $h)) $hero['keyword'] = trim((string) $h['keyword']);
        $hero['lead'] = Html::inline($h['lead'] ?? '');
        if (array_key_exists('points', $h)) $hero['points'] = array_values(array_filter(array_map('trim', (array) $h['points'])));

        $sections = (array) $record->sections;
        foreach ($data['secs'] ?? [] as $s) {
            $i = (int) $s['idx'];
            if (! isset($sections[$i])) continue;
            $orig = $sections[$i];
            if (array_key_exists('heading', $orig)) $sections[$i]['heading'] = trim((string) ($s['heading'] ?? ''));
            foreach (['intro', 'text'] as $f) if (array_key_exists($f, $orig)) $sections[$i][$f] = Html::inline($s[$f] ?? '');
            foreach (['paras', 'bullets', 'after'] as $f) {
                if (array_key_exists($f, $orig)) $sections[$i][$f] = array_values(array_filter(array_map(fn ($x) => Html::inline(is_array($x) ? ($x['html'] ?? '') : $x), (array) ($s[$f] ?? []))));
            }
            if (array_key_exists('image', $orig)) { $sections[$i]['image'] = trim((string) ($s['image'] ?? '')) ?: $orig['image']; $sections[$i]['alt'] = trim((string) ($s['alt'] ?? '')); }
            foreach ((array) ($s['items'] ?? []) as $it) {
                [$f, $j] = explode(':', $it['_f']);
                foreach ((array) $it['_k'] as $k) $sections[$i][$f][(int) $j][$k] = trim((string) ($it[$k] ?? ''));
            }
        }

        $data_ = (array) $record->data;
        if ($record->kind === 'legal') $data_['updated'] = trim((string) ($data['updated'] ?? ''));

        return [
            'meta_title' => (string) ($data['meta_title'] ?? ''), 'meta_description' => (string) ($data['meta_description'] ?? ''),
            'focus_keyword' => (string) ($data['focus_keyword'] ?? ''), 'hero' => $hero, 'sections' => $sections, 'data' => $data_,
            'faqs' => array_values(array_map(fn ($f) => ['q' => trim((string) $f['q']), 'a' => Html::inline($f['a'] ?? '')], array_filter((array) ($data['faqs'] ?? []), fn ($f) => ! empty($f['q'])))),
        ];
    }

    /** Puts a page back to the copy that shipped with the website. */
    public static function restore(Page $page): bool
    {
        $orig = collect(Content::get('pages'))->firstWhere('key', $page->key);
        if (! $orig) return false;
        $page->update(['meta_title' => $orig['metaTitle'] ?? '', 'meta_description' => $orig['metaDescription'] ?? '', 'hero' => $orig['hero'], 'sections' => $orig['sections'], 'faqs' => $orig['faqs'], 'data' => $orig['data'] ?? []]);
        return true;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $q) { $q->orderByRaw("CASE kind WHEN 'page' THEN 0 WHEN 'service' THEN 1 WHEN 'industry' THEN 2 ELSE 3 END")->orderBy('sort'); })
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Page')->searchable()->description(fn (Page $r) => $r->path),
                Tables\Columns\TextColumn::make('kind')->label('Type')->badge()->formatStateUsing(fn ($state) => self::KINDS[$state] ?? $state),
                Tables\Columns\TextColumn::make('updated_at')->label('Last change')->since()->sortable()->visibleFrom('md'),
            ])
            ->filters([Tables\Filters\SelectFilter::make('kind')->label('Type')->options(self::KINDS)])
            ->actions([
                Tables\Actions\Action::make('view')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')->iconButton()->tooltip('View on the website')->url(fn (Page $r) => self::siteUrl($r), shouldOpenInNewTab: true),
                Tables\Actions\EditAction::make()->iconButton()->tooltip('Edit'),
                Tables\Actions\Action::make('restore')->label('Restore original')->icon('heroicon-o-arrow-uturn-left')->color('danger')->requiresConfirmation()
                    ->modalDescription('Replace this page\'s text with the original copy it launched with? Your edits to this page are lost.')
                    ->action(fn (Page $r) => self::restore($r)),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPage::route('/'), 'edit' => Pages\EditPage::route('/{record}/edit')];
    }
}
