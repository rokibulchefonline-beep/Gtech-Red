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
 * Every page of the website. Service, industry and landing pages are built from sections (add, move, copy or
 * remove them: App\Filament\Support\PageBlocks). Main and legal pages have a designed layout, so their form edits
 * the words and images only. Changes are saved as a draft, previewed, and published now or at a set time; every
 * published version is kept (Version history).
 */
class PageResource extends Resource
{
    use Perms;

    private const ITEM_FIELDS = ['cards', 'steps', 'stats', 'reviews', 'items'];
    private const ITEM_KEYS = ['title', 'text', 'value', 'label', 'name', 'role'];
    private const RICH = ['bold', 'italic', 'underline', 'link', 'undo', 'redo'];
    private const KINDS = Page::KINDS;

    protected static string $section = 'pages';
    protected static ?string $model = Page::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Website content';
    protected static ?string $navigationLabel = 'Pages';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';

    /** New pages are landing pages; the built-in pages cannot be deleted. */
    public static function canDelete($record): bool { return $record->kind === 'landing' && static::allows('delete'); }
    public static function canDeleteAny(): bool { return false; }

    public static function canPublish(): bool { return static::allows('publish'); }

    /** Addresses a landing page cannot take (they belong to the website or the server). */
    public const RESERVED = ['admin', 'api', 'livewire', 'storage', 'build', 'js', 'css', 'images', 'fonts', 'media', 'filament', 'vendor', 'services', 'industries',
        'about', 'contact', 'case-studies', 'blogs', 'blog', 'quote', 'terms', 'privacy-policy', 'cookie-policy', 'sitemap', 'robots', 'up', 'login', 'logout', 'preview', 'blade-preview'];

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
        $record = $form->getRecord();
        return ($record instanceof Page && ! $record->usesBuilder()) ? self::fixedForm($form) : self::builderForm($form);
    }

    /** Service, industry and landing pages: hero, sections built from the library, FAQs, related services, SEO. */
    public static function builderForm(Form $form): Form
    {
        $landing = fn (Get $get) => $get('kind') === 'landing';
        return $form->schema([
            Forms\Components\Hidden::make('key'), Forms\Components\Hidden::make('kind'),
            Forms\Components\Placeholder::make('about')->hiddenLabel()->columnSpanFull()->content(fn (?Page $r) => $r ? self::banner($r) : ''),
            Forms\Components\Tabs::make()->columnSpanFull()->persistTabInQueryString()->tabs([
                Forms\Components\Tabs\Tab::make('Hero')->schema([
                    Forms\Components\TextInput::make('name')->label('Page name')->required()->maxLength(120)->visible($landing)
                        ->helperText('Used in the breadcrumb, the hero badge and the default page title.'),
                    Forms\Components\TextInput::make('hero.h1')->label('Main heading (H1)')->required()->maxLength(200)->helperText('Use [[ ]] around the words to colour red.'),
                    Forms\Components\TextInput::make('hero.keyword')->label('Main keyword')->maxLength(80)->hidden($landing),
                    self::rich('hero.lead', 'Opening description'),
                    Forms\Components\TagsInput::make('hero.points')->label('Highlights (short points)')->placeholder('Add a point and press Enter')->reorderable(),
                    ImageField::make('hero.motion', 'Hero image'),
                    Forms\Components\TextInput::make('hero.alt')->label('Hero image alt text')->maxLength(200)->visible($landing),
                    Forms\Components\Fieldset::make('Buttons and contact form')->visible($landing)->schema([
                        Forms\Components\TextInput::make('data.cta')->label('Main button')->placeholder('Book a Free Audit')->maxLength(40),
                        Forms\Components\TextInput::make('data.service')->label('Service on the contact form')->maxLength(80)
                            ->helperText('Pre-selected when people click the button. Leave empty to use the page name.'),
                        Forms\Components\TextInput::make('data.secondLabel')->label('Second button')->placeholder('Get a Free Proposal')->maxLength(40),
                        Forms\Components\TextInput::make('data.secondHref')->label('Second button link')->placeholder('#inquiry')->maxLength(200)
                            ->helperText('A section anchor such as #inquiry, or an address such as /case-studies.'),
                        Forms\Components\Select::make('data.icon')->label('Badge icon')->options(fn () => \App\Filament\Support\PageBlocks::iconOptions())->searchable(),
                    ])->columns(2),
                    Forms\Components\Fieldset::make('Landing page options')->visible($landing)->schema([
                        Forms\Components\Toggle::make('data.focus')->label('Focus page: hide the site menu and footer')
                            ->helperText('For adverts and email campaigns: visitors can only read the page and send the form, so nothing takes them away.'),
                        Forms\Components\Toggle::make('data.stickyCta')->label('Show a call and enquiry bar at the bottom on phones')
                            ->helperText('Keeps the main button and a Call button on screen while people scroll on a phone.'),
                    ])->columns(2),
                ]),
                Forms\Components\Tabs\Tab::make('Sections')->schema([
                    Forms\Components\Builder::make('sections')->hiddenLabel()->blocks(\App\Filament\Support\PageBlocks::blocks())
                        ->addActionLabel('Add a section')->addBetweenActionLabel('Insert a section here')->blockNumbers(false)
                        // Saved sections start collapsed; a section you have just added stays open.
                        ->collapsible()->collapsed(fn (?\Filament\Forms\ComponentContainer $item) => ! $item || filled($item->getRawState()['id'] ?? null))
                        ->cloneable()->reorderableWithButtons()->blockPickerColumns(3)->blockPickerWidth('3xl')
                        ->blockPreviews(false)
                        ->deleteAction(fn ($action) => $action->requiresConfirmation()->modalDescription('Remove this section from the page? You can undo it with Discard draft or from the Version history.')),
                ]),
                Forms\Components\Tabs\Tab::make('FAQs')->schema([
                    Forms\Components\Placeholder::make('faqhelp')->hiddenLabel()->content('These also feed Google\'s FAQ rich results. Keep answers to 40-60 words.'),
                    Forms\Components\TextInput::make('data.faqTitle')->label('FAQ heading')->placeholder('Frequently Asked Questions About ...')->maxLength(160)->visible($landing),
                    Forms\Components\Repeater::make('faqs')->hiddenLabel()->schema([
                        Forms\Components\TextInput::make('q')->label('Question')->required()->maxLength(250),
                        self::rich('a', 'Answer')->required(),
                    ])->collapsible()->collapsed()->itemLabel(fn (array $state) => $state['q'] ?? null)->addActionLabel('Add question')->reorderable()->cloneable()->defaultItems(0),
                ]),
                Forms\Components\Tabs\Tab::make('Related services')->schema([
                    Forms\Components\TextInput::make('data.relatedTitle')->label('Heading')->placeholder('Related Services')->maxLength(160)->visible($landing),
                    Forms\Components\Select::make('related')->hiddenLabel()->multiple()->searchable()
                        ->options(fn () => \App\Models\ServiceItem::query()->orderBy('name')->pluck('name', 'slug')->all())
                        ->helperText('Shown as cards near the end of the page, in this order.'),
                ]),
                Forms\Components\Tabs\Tab::make('Search appearance')->schema([
                    Forms\Components\TextInput::make('focus_keyword')->maxLength(80),
                    Forms\Components\TextInput::make('meta_title')->label('SEO title')->maxLength(160)->helperText('Best under 60 characters.'),
                    Forms\Components\Textarea::make('meta_description')->rows(3)->maxLength(320)->helperText('Best under 160 characters.'),
                    Forms\Components\Toggle::make('data.noindex')->label('Hide from search engines (noindex)')->visible($landing)
                        ->helperText('For pages only meant for ads or emails.'),
                    \App\Filament\Support\SchemaPanel::section(fn (Get $get) => Page::query()->find($get('key'))?->path ?? '/', fn (Get $get) => Page::query()->find($get('key'))?->name ?? '',
                        fn (Get $get) => \App\Filament\Support\SchemaPanel::autoTypes(Page::query()->find($get('key')))),
                ]),
            ]),
        ]);
    }

    /** "Editing <name> at <path>": status of the page and its draft. */
    public static function banner(Page $r): HtmlString
    {
        $state = ! $r->published ? '<b style="color:#b45309">Not published yet</b>' : 'Live';
        if ($r->hasDraft()) {
            $state .= ' · <b style="color:#b45309">Unpublished draft</b> saved '.e($r->draft_at?->diffForHumans()).($r->draftAuthor ? ' by '.e($r->draftAuthor->name) : '');
        }
        if ($r->publish_at) $state .= ' · <b>Goes live '.e($r->publish_at->format('D j M Y, H:i')).'</b>';
        return new HtmlString('<p style="font-size:14px">Editing <b>'.e($r->name).'</b> <span style="color:#888">'.e($r->path).'</span> · '.$state
            .($r->published ? ' · <a style="color:#e8202f" target="_blank" href="'.e(self::siteUrl($r)).'">View on the website</a>' : '').'</p>');
    }

    /** Main and legal pages: the designed layout stays, the words and images change. */
    public static function fixedForm(Form $form): Form
    {
        $isHome = fn (Get $get) => $get('key') === 'page~home';
        return $form->schema([
            Forms\Components\Hidden::make('key'), Forms\Components\Hidden::make('kind'),
            Forms\Components\Placeholder::make('about')->hiddenLabel()->columnSpanFull()->content(fn (?Page $r) => $r ? new HtmlString(self::banner($r)
                .'<p style="font-size:14px">This page has a designed layout, so you edit its words and images. Headings: put words in [[double brackets]] to colour them red, e.g. <code>Local SEO [[Agency UK]]</code>.</p>') : ''),
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
                Forms\Components\Tabs\Tab::make('Section order')->schema([
                    Forms\Components\Placeholder::make('orderhelp')->hiddenLabel()->content('Move sections up or down, remove them, or insert a new section from the widget library between them. Designed sections keep their look; edit their words in the Sections tab.'),
                    Forms\Components\Builder::make('layout')->hiddenLabel()->blocks(fn (Get $get) => [\App\Filament\Support\PageBlocks::designedBlock(self::designedFor($get('key'))), ...\App\Filament\Support\PageBlocks::blocks()])
                        ->addActionLabel('Add a section')->addBetweenActionLabel('Insert a section here')->blockNumbers(false)
                        ->collapsible()->collapsed(fn (?\Filament\Forms\ComponentContainer $item) => ! $item || filled($item->getRawState()['id'] ?? $item->getRawState()['key'] ?? null))
                        ->cloneable()->reorderableWithButtons()->blockPickerColumns(3)->blockPickerWidth('3xl')->blockPreviews(false)
                        ->deleteAction(fn ($action) => $action->requiresConfirmation()->modalDescription('Remove this section from the page? A designed section can be added back with Add a section > Designed section.')),
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
                    \App\Filament\Support\SchemaPanel::section(fn (Get $get) => Page::query()->find($get('key'))?->path ?? '/', fn (Get $get) => Page::query()->find($get('key'))?->name ?? '',
                        fn (Get $get) => \App\Filament\Support\SchemaPanel::autoTypes(Page::query()->find($get('key')))),
                ]),
            ]),
        ]);
    }

    /** Designed parts of a page, by its key. */
    private static function designedFor(?string $key): array
    {
        $p = $key ? Page::query()->find($key) : null;
        return $p ? \App\Support\Site\Layout::designed($p) : [];
    }

    private static function siteUrl(?Page $r): string
    {
        return \App\Filament\Support\SiteLink::to($r?->path ?? '/');
    }

    /** Database row (with its draft applied) -> form state. */
    public static function beforeFill(array $data): array
    {
        if (in_array($data['kind'], Page::BUILDER_KINDS, true)) {
            return [
                'key' => $data['key'], 'kind' => $data['kind'], 'name' => $data['name'], 'hero' => (array) $data['hero'], 'data' => (array) $data['data'],
                'sections' => \App\Filament\Support\PageBlocks::toBuilder((array) $data['sections']), 'faqs' => (array) $data['faqs'], 'related' => (array) $data['related'],
                'meta_title' => $data['meta_title'], 'meta_description' => $data['meta_description'], 'focus_keyword' => $data['focus_keyword'],
            ];
        }
        $state = [
            'key' => $data['key'], 'kind' => $data['kind'], 'meta_title' => $data['meta_title'], 'meta_description' => $data['meta_description'],
            'focus_keyword' => $data['focus_keyword'], 'hero' => (array) $data['hero'], 'faqs' => (array) $data['faqs'],
            'updated' => $data['data']['updated'] ?? '', 'secs' => [],
        ];
        $layoutPage = (new Page)->forceFill(['key' => $data['key'], 'kind' => $data['kind'], 'sections' => (array) $data['sections'], 'data' => (array) $data['data']]);
        $state['layout'] = \App\Filament\Support\PageBlocks::layoutToBuilder(\App\Support\Site\Layout::items($layoutPage));
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

    /** Form state -> database row. $record is the page with its draft applied. */
    public static function beforeSave(array $data, Page $record): array
    {
        unset($data['schema']);
        return $record->usesBuilder() ? self::builderRow($data, $record) : self::fixedRow($data, $record);
    }

    private static function builderRow(array $data, Page $record): array
    {
        $h = (array) ($data['hero'] ?? []);
        // Keep hero values the form does not show (older fields the website may still read).
        $hero = array_merge((array) $record->hero, [
            'h1' => trim((string) ($h['h1'] ?? '')), 'lead' => Html::inline($h['lead'] ?? ''),
            'points' => array_values(array_filter(array_map('trim', (array) ($h['points'] ?? [])))), 'motion' => trim((string) ($h['motion'] ?? '')),
        ]);
        if (array_key_exists('keyword', $h)) $hero['keyword'] = trim((string) $h['keyword']);
        if ($record->kind === 'landing') $hero['alt'] = trim((string) ($h['alt'] ?? ''));
        $d = array_merge((array) $record->data, array_map(fn ($v) => is_string($v) ? trim($v) : $v, (array) ($data['data'] ?? [])));
        return [
            'name' => $record->kind === 'landing' ? trim((string) ($data['name'] ?? $record->name)) : $record->name,
            'meta_title' => (string) ($data['meta_title'] ?? ''), 'meta_description' => (string) ($data['meta_description'] ?? ''), 'focus_keyword' => (string) ($data['focus_keyword'] ?? ''),
            'hero' => $hero, 'sections' => \App\Filament\Support\PageBlocks::fromBuilder((array) ($data['sections'] ?? [])), 'data' => $d,
            'related' => array_values((array) ($data['related'] ?? [])),
            'faqs' => array_values(array_map(fn ($f) => ['q' => trim((string) $f['q']), 'a' => Html::inline($f['a'] ?? '')], array_filter((array) ($data['faqs'] ?? []), fn ($f) => ! empty($f['q'])))),
        ];
    }

    /** Fixed layouts: only the words and images change; the layout fields are kept. */
    private static function fixedRow(array $data, Page $record): array
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
        if (array_key_exists('layout', $data)) {
            $designed = \App\Support\Site\Layout::designed($record->forceFill(['sections' => $sections]));
            $layout = \App\Filament\Support\PageBlocks::layoutFromBuilder((array) $data['layout'], $designed);
            // The original order is not stored, so designed sections added to the page later still show.
            if ($layout === array_map(fn ($k) => ['type' => 'designed', 'key' => $k], array_keys($designed))) unset($data_['layout']);
            else $data_['layout'] = $layout;
        }

        return [
            'name' => $record->name, 'related' => (array) $record->related,
            'meta_title' => (string) ($data['meta_title'] ?? ''), 'meta_description' => (string) ($data['meta_description'] ?? ''),
            'focus_keyword' => (string) ($data['focus_keyword'] ?? ''), 'hero' => $hero, 'sections' => $sections, 'data' => $data_,
            'faqs' => array_values(array_map(fn ($f) => ['q' => trim((string) $f['q']), 'a' => Html::inline($f['a'] ?? '')], array_filter((array) ($data['faqs'] ?? []), fn ($f) => ! empty($f['q'])))),
        ];
    }

    /** Loads the copy the page shipped with into its draft, to check and publish. */
    public static function restore(Page $page): bool
    {
        $orig = collect(Content::get('pages'))->firstWhere('key', $page->key);
        if (! $orig) return false;
        $page->saveDraft(['name' => $page->name, 'related' => $orig['related'] ?? $page->related, 'focus_keyword' => $page->focus_keyword,
            'meta_title' => $orig['metaTitle'] ?? '', 'meta_description' => $orig['metaDescription'] ?? '', 'hero' => $orig['hero'], 'sections' => $orig['sections'], 'faqs' => $orig['faqs'], 'data' => $orig['data'] ?? []]);
        return true;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) { $query->orderByRaw("CASE kind WHEN 'page' THEN 0 WHEN 'landing' THEN 1 WHEN 'service' THEN 2 WHEN 'industry' THEN 3 ELSE 4 END")->orderBy('sort')->orderBy('name'); })
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Page')->searchable(['name', 'path'])->description(fn (Page $r) => $r->path),
                Tables\Columns\TextColumn::make('kind')->label('Type')->badge()->formatStateUsing(fn ($state) => self::KINDS[$state] ?? $state)->visibleFrom('md'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->state(fn (Page $r) => ! $r->published ? ($r->publish_at ? 'Scheduled' : 'Not published') : ($r->publish_at ? 'Draft scheduled' : ($r->hasDraft() ? 'Live + draft' : 'Live')))
                    ->color(fn (string $state) => match ($state) { 'Live' => 'success', 'Not published' => 'gray', default => 'warning' })
                    ->tooltip(fn (Page $r) => $r->publish_at ? 'Goes live '.$r->publish_at->format('D j M Y, H:i') : ($r->hasDraft() ? 'Draft saved '.$r->draft_at?->diffForHumans() : null)),
                Tables\Columns\TextColumn::make('updated_at')->label('Last published')->since()->sortable()->visibleFrom('lg'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kind')->label('Type')->options(self::KINDS),
                Tables\Filters\Filter::make('drafts')->label('With unpublished changes')->toggle()
                    ->query(fn (Builder $query) => $query->where(fn (Builder $q2) => $q2->whereNotNull('draft')->orWhere('published', false))),
            ])
            ->actions([
                Tables\Actions\Action::make('view')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')->iconButton()->tooltip('View on the website')
                    ->visible(fn (Page $r) => $r->published)->url(fn (Page $r) => self::siteUrl($r), shouldOpenInNewTab: true),
                Tables\Actions\EditAction::make()->iconButton()->tooltip('Edit'),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('copy')->label('Copy as a new landing page')->icon('heroicon-o-document-duplicate')->visible(fn () => static::allows('create'))
                        ->url(fn (Page $r) => self::getUrl('create', ['from' => $r->key])),
                    Tables\Actions\Action::make('history')->label('Version history')->icon('heroicon-o-clock')
                        ->url(fn (Page $r) => \App\Filament\Admin\Pages\VersionHistory::urlFor($r)),
                    Tables\Actions\Action::make('restore')->label('Load the original copy')->icon('heroicon-o-arrow-uturn-left')->color('danger')->requiresConfirmation()
                        ->visible(fn (Page $r) => $r->kind !== 'landing' && static::allows('edit'))
                        ->modalDescription('Load the copy this page launched with into a draft? Nothing changes on the website until you publish the draft.')
                        ->action(fn (Page $r) => self::restore($r)),
                    Tables\Actions\DeleteAction::make()->modalDescription('Delete this landing page? Its address stops working (add a redirect if it was shared).'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPage::route('/'), 'create' => Pages\CreatePage::route('/create'), 'edit' => Pages\EditPage::route('/{record}/edit')];
    }
}
