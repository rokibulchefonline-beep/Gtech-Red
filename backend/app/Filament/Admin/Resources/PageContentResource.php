<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PageContentResource\Pages;
use App\Filament\Support\Perms;
use App\Models\PageContent;
use App\Support\Html;
use App\Support\PageBase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

/**
 * Edit the copy of every service, industry and main page. The form always shows the current text; only
 * what differs from the built-in copy is stored, exactly like the old editor.
 */
class PageContentResource extends Resource
{
    use Perms;

    private const ITEM_FIELDS = ['cards', 'steps', 'stats', 'reviews', 'items'];
    private const ITEM_KEYS = ['title', 'text', 'value', 'label', 'name', 'role'];
    private const RICH = ['bold', 'italic', 'underline', 'link', 'undo', 'redo'];

    protected static string $perm = 'content';
    protected static ?string $model = PageContent::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Website content';
    protected static ?string $navigationLabel = 'Pages';
    protected static ?string $modelLabel = 'page';
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool { return false; }

    public static function canDelete($record): bool { return false; }

    private static function rich(string $name, string $label = ''): Forms\Components\RichEditor
    {
        $f = Forms\Components\RichEditor::make($name)->toolbarButtons(self::RICH)->disableToolbarButtons(['attachFiles']);
        return $label ? $f->label($label) : $f;
    }

    public static function form(Form $form): Form
    {
        $isPage = fn (Get $get) => $get('kind') === 'page';
        return $form->schema([
            Forms\Components\Hidden::make('kind'),
            Forms\Components\Placeholder::make('about')->hiddenLabel()->columnSpanFull()->content(fn (?PageContent $r) => new HtmlString(
                '<p style="font-size:14px">Editing <b>'.e(PageBase::get((string) $r?->key)['name'] ?? $r?->key).'</b> · <a style="color:#e8202f" target="_blank" href="'.e(self::siteUrl($r)).'">View on the website</a>'
                .'<br>Headings: put words in [[double brackets]] to colour them red, e.g. <code>Local SEO [[Agency UK]]</code>. Changes go live after you press <b>Publish site</b> on the dashboard.</p>')),
            Forms\Components\Tabs::make()->columnSpanFull()->tabs([
                Forms\Components\Tabs\Tab::make('Hero')->schema([
                    Forms\Components\TextInput::make('hero.h1')->label('Main heading (H1)')->maxLength(200)
                        ->helperText(fn (Get $get) => $get('kind') === 'page' && $get('slug') === 'home' ? 'Use | to start a new line, e.g. Digital Marketing|Agency [[for Scalable]]|[[Growth]]' : 'Use [[ ]] around the words to colour red.'),
                    Forms\Components\TextInput::make('hero.keyword')->label('Main keyword')->maxLength(80)->hidden($isPage),
                    self::rich('hero.lead', 'Opening description'),
                    Forms\Components\TagsInput::make('hero.points')->label('Highlights (short points)')->placeholder('Add a point and press Enter'),
                ]),
                Forms\Components\Tabs\Tab::make('Sections')->schema([
                    Forms\Components\Repeater::make('secs')->hiddenLabel()->addable(false)->deletable(false)->reorderable(false)->collapsible()
                        ->itemLabel(fn (array $state) => ($state['label'] ?? '').(! empty($state['heading']) ? ': '.str_replace(['[[', ']]'], '', $state['heading']) : ''))
                        ->schema([
                            Forms\Components\Hidden::make('id'), Forms\Components\Hidden::make('label'), Forms\Components\Hidden::make('has'),
                            Forms\Components\TextInput::make('heading')->maxLength(240)->visible(fn (Get $get) => in_array('heading', (array) $get('has'), true)),
                            self::rich('intro', 'Intro text')->visible(fn (Get $get) => in_array('intro', (array) $get('has'), true)),
                            self::rich('text', 'Text')->visible(fn (Get $get) => in_array('text', (array) $get('has'), true)),
                            Forms\Components\Repeater::make('paras')->label('Paragraphs')->simple(self::rich('html'))->defaultItems(0)
                                ->visible(fn (Get $get) => in_array('paras', (array) $get('has'), true))->addActionLabel('Add paragraph'),
                            Forms\Components\Repeater::make('bullets')->label('Bullet points')->simple(Forms\Components\TextInput::make('html')->maxLength(800))->defaultItems(0)
                                ->visible(fn (Get $get) => in_array('bullets', (array) $get('has'), true))->addActionLabel('Add bullet'),
                            Forms\Components\Repeater::make('items')->label('Cards, steps and numbers (text only, the layout stays the same)')
                                ->addable(false)->deletable(false)->reorderable(false)->grid(2)
                                ->visible(fn (Get $get) => in_array('items', (array) $get('has'), true))
                                ->schema([
                                    Forms\Components\Hidden::make('_f'), Forms\Components\Hidden::make('_k'),
                                    ...array_map(fn ($k) => Forms\Components\TextInput::make($k)->label(ucfirst($k))->maxLength(800)
                                        ->visible(fn (Get $get) => in_array($k, (array) $get('_k'), true)), self::ITEM_KEYS),
                                ]),
                        ]),
                ]),
                Forms\Components\Tabs\Tab::make('FAQs')->hidden(fn (Get $get) => $get('kind') === 'page' && $get('slug') === 'home')->schema([
                    Forms\Components\Placeholder::make('faqhelp')->hiddenLabel()->content('These also feed Google\'s FAQ rich results. Keep answers to 40-60 words.'),
                    Forms\Components\Repeater::make('faq_list')->hiddenLabel()->schema([
                        Forms\Components\TextInput::make('q')->label('Question')->required()->maxLength(250),
                        self::rich('a', 'Answer')->required(),
                    ])->collapsible()->itemLabel(fn (array $state) => $state['q'] ?? null)->addActionLabel('Add question'),
                ]),
                Forms\Components\Tabs\Tab::make('Search appearance')->schema([
                    Forms\Components\TextInput::make('focus_keyword')->maxLength(80),
                    Forms\Components\TextInput::make('meta_title')->label('SEO title')->maxLength(120)->helperText('Best under 60 characters.'),
                    Forms\Components\Textarea::make('meta_description')->rows(3)->maxLength(300)->helperText('Best under 160 characters.'),
                ]),
            ]),
            Forms\Components\Hidden::make('slug'),
        ]);
    }

    private static function siteUrl(?PageContent $r): string
    {
        return rtrim(config('gtech.site_url'), '/').(PageBase::get((string) $r?->key)['path'] ?? '/');
    }

    /** Built-in copy + saved changes -> form state. */
    public static function beforeFill(array $data): array
    {
        $base = PageBase::get($data['key']) ?? ['hero' => [], 'sections' => [], 'faqs' => [], 'metaTitle' => '', 'metaDescription' => ''];
        $hero = (array) ($data['hero'] ?? []);
        $over = (array) ($data['sections'] ?? []);
        $state = [
            'kind' => $data['kind'], 'slug' => $data['slug'],
            'meta_title' => $data['meta_title'] ?: ($base['metaTitle'] ?? ''), 'meta_description' => $data['meta_description'] ?: ($base['metaDescription'] ?? ''),
            'focus_keyword' => $data['focus_keyword'] ?? '',
            'hero' => [
                'h1' => $hero['h1'] ?? ($base['hero']['h1'] ?? ''), 'keyword' => $hero['keyword'] ?? ($base['hero']['keyword'] ?? ''),
                'lead' => $hero['lead'] ?? ($base['hero']['lead'] ?? ''), 'points' => ! empty($hero['points']) ? $hero['points'] : ($base['hero']['points'] ?? []),
            ],
            'faq_list' => ! empty($data['faqs']) ? $data['faqs'] : ($base['faqs'] ?? []),
            'secs' => [],
        ];
        foreach ($base['sections'] ?? [] as $s) {
            $o = (array) ($over[$s['id']] ?? []);
            $has = array_values(array_filter(['heading', 'intro', 'text', 'paras', 'bullets'], fn ($f) => array_key_exists($f, $s)));
            $items = [];
            foreach (self::ITEM_FIELDS as $f) {
                foreach ((array) ($s[$f] ?? []) as $i => $it) {
                    $keys = array_values(array_intersect(self::ITEM_KEYS, array_keys((array) $it)));
                    if (! $keys) continue;
                    $items[] = ['_f' => "$f:$i", '_k' => $keys] + array_merge(array_intersect_key((array) $it, array_flip($keys)), (array) ($o[$f][$i] ?? []));
                }
            }
            if ($items) $has[] = 'items';
            $state['secs'][] = [
                'id' => $s['id'], 'label' => ($s['nav'] ?? '') ?: ucfirst((string) $s['type']), 'has' => $has,
                'heading' => $o['heading'] ?? ($s['heading'] ?? null), 'intro' => $o['intro'] ?? ($s['intro'] ?? null), 'text' => $o['text'] ?? ($s['text'] ?? null),
                'paras' => ! empty($o['paras']) ? $o['paras'] : ($s['paras'] ?? []), 'bullets' => ! empty($o['bullets']) ? $o['bullets'] : ($s['bullets'] ?? []),
                'items' => $items,
            ];
        }
        return $state;
    }

    /** Form state -> only what differs from the built-in copy. */
    public static function beforeSave(array $data, $record): array
    {
        $base = PageBase::get($record->key) ?? ['hero' => [], 'sections' => [], 'faqs' => []];
        $same = fn ($a, $b) => json_encode($a) === json_encode($b);
        $h = (array) ($data['hero'] ?? []);
        $hero = [];
        if (($h['h1'] ?? '') !== ($base['hero']['h1'] ?? '')) $hero['h1'] = trim((string) ($h['h1'] ?? ''));
        if (($h['keyword'] ?? '') !== ($base['hero']['keyword'] ?? '') && $record->kind !== 'page') $hero['keyword'] = trim((string) ($h['keyword'] ?? ''));
        $lead = Html::inline($h['lead'] ?? '');
        if ($lead !== Html::inline($base['hero']['lead'] ?? '')) $hero['lead'] = $lead;
        $points = array_values(array_filter(array_map('trim', (array) ($h['points'] ?? []))));
        if (! $same($points, $base['hero']['points'] ?? [])) $hero['points'] = $points;

        $baseById = collect($base['sections'] ?? [])->keyBy('id');
        $sections = [];
        foreach ($data['secs'] ?? [] as $s) {
            $b = $baseById[$s['id']] ?? null;
            if (! $b) continue;
            $d = [];
            if (array_key_exists('heading', $b) && trim((string) $s['heading']) !== $b['heading']) $d['heading'] = trim((string) $s['heading']);
            foreach (['intro', 'text'] as $f) {
                if (array_key_exists($f, $b) && Html::inline($s[$f] ?? '') !== Html::inline($b[$f])) $d[$f] = Html::inline($s[$f] ?? '');
            }
            foreach (['paras', 'bullets'] as $f) {
                if (! array_key_exists($f, $b)) continue;
                $list = array_values(array_filter(array_map(fn ($x) => Html::inline(is_array($x) ? ($x['html'] ?? '') : $x), (array) ($s[$f] ?? []))));
                if (! $same($list, array_map(fn ($x) => Html::inline($x), $b[$f]))) $d[$f] = $list;
            }
            foreach ((array) ($s['items'] ?? []) as $it) {
                [$f, $i] = explode(':', $it['_f']);
                $orig = (array) ($b[$f][(int) $i] ?? []);
                foreach ((array) $it['_k'] as $k) {
                    $v = trim((string) ($it[$k] ?? ''));
                    if ($v !== (string) ($orig[$k] ?? '')) $d[$f][(int) $i][$k] = $v;
                }
            }
            foreach (self::ITEM_FIELDS as $f) {
                if (isset($d[$f])) { $full = []; foreach (array_keys($b[$f]) as $i) $full[$i] = $d[$f][$i] ?? (object) []; $d[$f] = array_values($full); }
            }
            if ($d) $sections[$s['id']] = $d;
        }

        $faqs = array_values(array_map(fn ($f) => ['q' => trim((string) $f['q']), 'a' => Html::inline($f['a'] ?? '')], array_filter((array) ($data['faq_list'] ?? []), fn ($f) => ! empty($f['q']))));
        $baseFaqs = array_map(fn ($f) => ['q' => $f['q'], 'a' => Html::inline($f['a'])], $base['faqs'] ?? []);

        return [
            'meta_title' => ($data['meta_title'] ?? '') === ($base['metaTitle'] ?? '') ? '' : (string) $data['meta_title'],
            'meta_description' => ($data['meta_description'] ?? '') === ($base['metaDescription'] ?? '') ? '' : (string) $data['meta_description'],
            'focus_keyword' => (string) ($data['focus_keyword'] ?? ''),
            'hero' => $hero, 'sections' => $sections, 'faqs' => $same($faqs, $baseFaqs) ? [] : $faqs,
        ];
    }

    public static function table(Table $table): Table
    {
        $edited = fn (PageContent $r) => ! empty($r->hero) || ! empty($r->sections) || ! empty($r->faqs) || $r->meta_title || $r->meta_description;
        return $table
            ->modifyQueryUsing(function (Builder $q) { $q->orderByRaw("CASE kind WHEN 'page' THEN 0 WHEN 'service' THEN 1 ELSE 2 END")->orderBy('slug'); })
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Page')->getStateUsing(fn (PageContent $r) => PageBase::get($r->key)['name'] ?? $r->slug)
                    ->description(fn (PageContent $r) => PageBase::get($r->key)['path'] ?? ''),
                Tables\Columns\TextColumn::make('slug')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('kind')->label('Type')->badge()->formatStateUsing(fn ($state) => ['page' => 'Main page', 'service' => 'Service', 'industry' => 'Industry'][$state] ?? $state),
                Tables\Columns\TextColumn::make('status')->getStateUsing(fn (PageContent $r) => $edited($r) ? 'Edited' : 'Original')->badge()
                    ->color(fn ($state) => $state === 'Edited' ? 'info' : 'gray'),
                Tables\Columns\TextColumn::make('updated_at')->label('Last change')->since(),
            ])
            ->filters([Tables\Filters\SelectFilter::make('kind')->label('Type')->options(['page' => 'Main pages', 'service' => 'Services', 'industry' => 'Industries'])])
            ->actions([
                Tables\Actions\Action::make('view')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')->url(fn (PageContent $r) => self::siteUrl($r), shouldOpenInNewTab: true),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('reset')->label('Reset')->icon('heroicon-o-arrow-uturn-left')->color('danger')->requiresConfirmation()
                    ->modalDescription('Remove all edits and go back to the original text for this page?')->visible($edited)
                    ->action(fn (PageContent $r) => $r->update(['hero' => [], 'sections' => [], 'faqs' => [], 'meta_title' => '', 'meta_description' => '', 'focus_keyword' => ''])),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPageContent::route('/'), 'edit' => Pages\EditPageContent::route('/{record}/edit')];
    }
}
