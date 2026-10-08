<?php

namespace App\Filament\Support;

use App\Models\Industry;
use App\Models\ServiceItem;
use App\Support\Html;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;
use Illuminate\Support\Str;

/**
 * The section library of the page builder. Each block is one of the section types the website already renders
 * (resources/views/site/c/block.blade.php), so a new or moved section looks right straight away.
 *
 * toBuilder() and fromBuilder() convert between the stored page sections and the builder's state.
 */
class PageBlocks
{
    private const RICH = ['bold', 'italic', 'underline', 'link', 'undo', 'redo'];

    public const LABELS = [
        'text' => 'Text', 'media' => 'Image and text', 'cards' => 'Cards', 'features' => 'Features', 'steps' => 'Steps',
        'table' => 'Table', 'metrics' => 'Results in numbers', 'impact' => 'Impact (headline numbers)', 'reviews' => 'Reviews',
        'cases' => 'Case studies', 'industries' => 'Industries', 'logos' => 'Client logos',
        'cta' => 'Call to action banner', 'faq' => 'FAQ accordion', 'video' => 'Video', 'pricing' => 'Pricing plans', 'form' => 'Enquiry form',
    ];

    /** A link a section may use: an anchor (#inquiry), a site address (/contact) or a secure web address. */
    public static function link(mixed $v, string $fallback = '/contact'): string
    {
        $v = trim((string) $v);
        return preg_match('~^(#|/(?!/)|https://)~', $v) ? $v : $fallback;
    }

    /** "youtube:ID" or "vimeo:ID" from a video address, or '' when it is not a YouTube or Vimeo link. */
    public static function videoId(string $url): string
    {
        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) return 'youtube:'.$m[1];
        if (preg_match('~vimeo\.com/(?:video/)?(\d{6,12})~', $url, $m)) return 'vimeo:'.$m[1];
        return '';
    }

    public static function iconOptions(): array
    {
        static $o = null;
        if ($o === null) {
            $keys = array_keys(json_decode((string) file_get_contents(resource_path('data/icons.json')), true) ?: []);
            $o = array_combine($keys, array_map(fn ($k) => Str::of($k)->after(':')->replace('-', ' ')->ucfirst()->toString(), $keys));
        }
        return $o;
    }

    private static function heading(bool $required = true): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('heading')->maxLength(240)->required($required)
            ->helperText('Put words in [[double brackets]] to colour them red.');
    }

    private static function nav(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('nav')->label('Label in the "On this page" bar')->maxLength(40)
            ->helperText('Optional. Leave empty to keep this section out of the bar.');
    }

    private static function intro(): Forms\Components\RichEditor
    {
        return Forms\Components\RichEditor::make('intro')->label('Intro text')->toolbarButtons(self::RICH);
    }

    private static function paras(string $label = 'Paragraphs'): Forms\Components\Repeater
    {
        return Forms\Components\Repeater::make('paras')->label($label)->simple(Forms\Components\RichEditor::make('html')->toolbarButtons(self::RICH)->required())
            ->defaultItems(1)->reorderable()->addActionLabel('Add paragraph');
    }

    private static function bullets(): Forms\Components\Repeater
    {
        return Forms\Components\Repeater::make('bullets')->label('Bullet points (optional)')->simple(Forms\Components\TextInput::make('html')->maxLength(800)->required())
            ->defaultItems(0)->reorderable()->addActionLabel('Add bullet point');
    }

    private static function iconSelect(): Forms\Components\Select
    {
        return Forms\Components\Select::make('icon')->options(fn () => self::iconOptions())->searchable()->placeholder('No icon')
            ->allowHtml()->getOptionLabelUsing(fn ($value) => self::iconOptions()[$value] ?? $value);
    }

    /** @return Block[] */
    public static function blocks(): array
    {
        $grid = fn (string $name, string $label, array $schema, string $add, int $min = 1) => Forms\Components\Repeater::make($name)->label($label)
            ->schema($schema)->columns(2)->minItems($min)->defaultItems($min)->reorderable()->cloneable()->collapsible()
            ->collapsed(fn (?\Filament\Forms\ComponentContainer $item) => ! $item || filled(($st = $item->getRawState())['title'] ?? $st['name'] ?? $st['label'] ?? null))
            ->itemLabel(fn (array $state) => $state['title'] ?? $state['name'] ?? $state['label'] ?? null)->addActionLabel($add);

        $blocks = [
            Block::make('text')->label(self::LABELS['text'])->icon('heroicon-o-bars-3-bottom-left')->schema([
                self::heading(), self::paras(), self::bullets(), self::nav(),
            ]),
            Block::make('media')->label(self::LABELS['media'])->icon('heroicon-o-photo')->schema([
                self::heading(), self::paras(), self::bullets(),
                ImageField::make('image', 'Image'),
                Forms\Components\TextInput::make('alt')->label('Image alt text')->maxLength(200)->required()->helperText('Describe the image for search engines and screen readers.'),
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Toggle::make('flip')->label('Image on the left'),
                    Forms\Components\Select::make('tone')->label('Background')->options(['' => 'White', 'grey' => 'Light grey'])->selectablePlaceholder(false)->default(''),
                ]),
                self::nav(),
            ]),
            Block::make('cards')->label(self::LABELS['cards'])->icon('heroicon-o-squares-2x2')->schema([
                self::heading(), self::intro(),
                $grid('cards', 'Cards (5 or more show on a dark background)', [self::iconSelect(), Forms\Components\TextInput::make('title')->required()->maxLength(120),
                    Forms\Components\Textarea::make('text')->required()->rows(2)->maxLength(600)->columnSpanFull()], 'Add card', 2),
                self::nav(),
            ]),
            Block::make('features')->label(self::LABELS['features'])->icon('heroicon-o-sparkles')->schema([
                self::heading(), self::intro(), self::paras('Paragraphs (left column)')->defaultItems(0),
                $grid('cards', 'Feature cards', [self::iconSelect(), Forms\Components\TextInput::make('title')->required()->maxLength(120),
                    Forms\Components\Textarea::make('text')->required()->rows(2)->maxLength(600)->columnSpanFull()], 'Add feature', 2),
                self::nav(),
            ]),
            Block::make('steps')->label(self::LABELS['steps'])->icon('heroicon-o-list-bullet')->schema([
                self::heading(), self::intro(),
                $grid('steps', 'Steps (numbered in order)', [Forms\Components\TextInput::make('title')->required()->maxLength(120)->columnSpanFull(),
                    Forms\Components\Textarea::make('text')->required()->rows(2)->maxLength(600)->columnSpanFull()], 'Add step', 2),
                self::nav(),
            ]),
            Block::make('table')->label(self::LABELS['table'])->icon('heroicon-o-table-cells')->schema([
                self::heading(), self::intro(),
                Forms\Components\TextInput::make('columns_text')->label('Column headings')->required()->placeholder('Feature | Basic | Pro')->helperText('Separate the columns with |'),
                Forms\Components\Textarea::make('rows_text')->label('Rows')->required()->rows(5)->placeholder("Keyword research | Yes | Yes\nMonthly report | No | Yes")
                    ->helperText('One row per line, cells separated by |. The first cell is the row heading.'),
                Forms\Components\TextInput::make('note')->label('Tip under the table (optional)')->maxLength(400),
                self::nav(),
            ]),
            Block::make('metrics')->label(self::LABELS['metrics'])->icon('heroicon-o-chart-bar')->schema([
                self::heading(), self::intro(),
                $grid('metrics', 'Numbers', [Forms\Components\TextInput::make('value')->required()->maxLength(30)->placeholder('+180%'),
                    Forms\Components\TextInput::make('label')->required()->maxLength(80), Forms\Components\Textarea::make('text')->rows(2)->maxLength(300)->columnSpanFull()], 'Add number', 2),
                self::nav(),
            ]),
            Block::make('impact')->label(self::LABELS['impact'])->icon('heroicon-o-presentation-chart-line')->schema([
                self::heading(), Forms\Components\RichEditor::make('text')->label('Text')->toolbarButtons(self::RICH),
                $grid('stats', 'Headline numbers', [Forms\Components\TextInput::make('value')->required()->maxLength(30), Forms\Components\TextInput::make('label')->required()->maxLength(80)], 'Add number', 0),
            ]),
            Block::make('reviews')->label(self::LABELS['reviews'])->icon('heroicon-o-chat-bubble-left-right')->schema([
                self::heading(), self::intro(),
                Forms\Components\Placeholder::make('reviews_help')->hiddenLabel()
                    ->content('Shows the client testimonials slider from the home page. Edit the testimonials in Website content > Testimonials; the reviews below are not shown.'),
                $grid('reviews', 'Fallback reviews', [Forms\Components\TextInput::make('name')->required()->maxLength(80), Forms\Components\TextInput::make('role')->label('Role and company')->maxLength(120),
                    Forms\Components\Textarea::make('text')->label('Review')->required()->rows(3)->maxLength(800)->columnSpanFull()], 'Add review', 0),
                self::nav(),
            ]),
            Block::make('cases')->label(self::LABELS['cases'])->icon('heroicon-o-trophy')->schema([
                self::heading(), self::intro(),
                Forms\Components\Select::make('service')->label('Which case studies')->placeholder('The ones tagged with this page\'s service')
                    ->options(fn () => ['*' => 'The latest case studies'] + ServiceItem::query()->orderBy('name')->pluck('name', 'slug')->all())
                    ->helperText('The section is hidden when no published case study matches.'),
                self::nav(),
            ]),
            Block::make('industries')->label(self::LABELS['industries'])->icon('heroicon-o-building-office-2')->schema([
                self::heading(), self::intro(),
                $grid('items', 'Industries', [Forms\Components\Select::make('slug')->label('Industry')->required()->options(fn () => Industry::query()->orderBy('sort')->pluck('name', 'slug')->all()),
                    Forms\Components\Textarea::make('text')->required()->rows(2)->maxLength(400)->columnSpanFull()], 'Add industry', 1),
                self::nav(),
            ]),
            Block::make('logos')->label(self::LABELS['logos'])->icon('heroicon-o-building-storefront')->schema([
                Forms\Components\Placeholder::make('logos_help')->hiddenLabel()->content('Shows six client logos from Website content > Client logos. Nothing to fill in.'),
            ]),
            Block::make('cta')->label(self::LABELS['cta'])->icon('heroicon-o-megaphone')->schema([
                self::heading(), Forms\Components\Textarea::make('text')->label('Supporting line (optional)')->rows(2)->maxLength(300),
                Forms\Components\TextInput::make('button')->label('Button text')->required()->maxLength(40)->default('Get a Free Proposal'),
                Forms\Components\TextInput::make('link')->label('Button link')->required()->maxLength(200)->default('#inquiry')
                    ->helperText('An anchor such as #inquiry, or an address such as /contact or https://...'),
                Forms\Components\Select::make('tone')->label('Colour')->options(['red' => 'Red', 'dark' => 'Dark'])->default('red')->selectablePlaceholder(false),
                self::nav(),
            ]),
            Block::make('faq')->label(self::LABELS['faq'])->icon('heroicon-o-question-mark-circle')->schema([
                self::heading(), self::intro(),
                $grid('items', 'Questions and answers', [Forms\Components\TextInput::make('title')->label('Question')->required()->maxLength(250)->columnSpanFull(),
                    Forms\Components\Textarea::make('text')->label('Answer')->required()->rows(3)->maxLength(1000)->columnSpanFull()], 'Add question', 1),
                self::nav(),
            ]),
            Block::make('video')->label(self::LABELS['video'])->icon('heroicon-o-play-circle')->schema([
                self::heading(), self::intro(),
                Forms\Components\TextInput::make('url')->label('YouTube or Vimeo link')->required()->url()->maxLength(300)
                    ->placeholder('https://www.youtube.com/watch?v=...')
                    ->helperText('Plays inside the page. Only YouTube and Vimeo links are accepted.'),
                Forms\Components\TextInput::make('caption')->label('Caption (optional)')->maxLength(200),
                self::nav(),
            ]),
            Block::make('pricing')->label(self::LABELS['pricing'])->icon('heroicon-o-currency-pound')->schema([
                self::heading(), self::intro(),
                $grid('plans', 'Plans', [Forms\Components\TextInput::make('name')->label('Plan name')->required()->maxLength(60),
                    Forms\Components\TextInput::make('price')->required()->maxLength(30)->placeholder('£499'),
                    Forms\Components\TextInput::make('period')->maxLength(30)->placeholder('per month'),
                    Forms\Components\Textarea::make('text')->label('Short description')->rows(2)->maxLength(300)->columnSpanFull(),
                    Forms\Components\Repeater::make('features')->label('What is included')->simple(Forms\Components\TextInput::make('html')->maxLength(200)->required())
                        ->defaultItems(0)->reorderable()->addActionLabel('Add item')->columnSpanFull(),
                    Forms\Components\Toggle::make('highlight')->label('Highlight as most popular'),
                    Forms\Components\TextInput::make('button')->label('Button text')->maxLength(40)->default('Get started'),
                    Forms\Components\TextInput::make('link')->label('Button link')->maxLength(200)->default('#inquiry'),
                ], 'Add plan', 1),
                self::nav(),
            ]),
            Block::make('form')->label(self::LABELS['form'])->icon('heroicon-o-envelope-open')->schema([
                self::heading(), self::intro(),
                Forms\Components\Placeholder::make('form_help')->hiddenLabel()
                    ->content('Shows the enquiry form used across the site, with this page\'s service already chosen.'),
            ]),
        ];
        // Each section keeps its id (the #anchor links and the "On this page" bar point to it).
        // The collapsed list shows each section's type and heading.
        return array_map(function (Block $b) {
            $type = self::LABELS[$b->getName()];
            return $b->schema([Forms\Components\Hidden::make('id'), ...$b->getChildComponents()])
                ->label(fn (?array $state) => $state === null || blank($state['heading'] ?? null) ? $type : $type.': '.Str::limit(str_replace(['[[', ']]'], '', strip_tags((string) $state['heading'])), 70));
        }, $blocks);
    }

    /** Stored sections -> builder state. */
    public static function toBuilder(array $sections): array
    {
        $out = [];
        foreach ($sections as $s) {
            $type = $s['type'] ?? '';
            if (! isset(self::LABELS[$type])) continue;
            $d = $s;
            unset($d['type']);
            foreach (['paras', 'bullets'] as $f) if (isset($d[$f])) $d[$f] = array_values((array) $d[$f]); // simple repeaters take plain strings
            if ($type === 'table') {
                $d['columns_text'] = implode(' | ', (array) ($d['columns'] ?? []));
                $d['rows_text'] = implode("\n", array_map(fn ($r) => implode(' | ', (array) $r), (array) ($d['rows'] ?? [])));
                unset($d['columns'], $d['rows']);
            }
            $out[(string) Str::uuid()] = ['type' => $type, 'data' => $d];
        }
        return $out;
    }

    /** A simple repeater item: a string, or (in the raw form state) ['html' => string]. */
    private static function scalar(mixed $x): string
    {
        while (is_array($x)) $x = $x['html'] ?? reset($x);
        return (string) $x;
    }

    /** Builder state -> stored sections. Every section gets a unique id (its anchor, e.g. #local-seo-benefits). */
    public static function fromBuilder(array $state): array
    {
        $out = [];
        $ids = [];
        foreach (array_values($state) as $n => $b) {
            $type = $b['type'] ?? '';
            if (! isset(self::LABELS[$type])) continue;
            $d = (array) ($b['data'] ?? []);
            $s = ['type' => $type];

            $id = Str::slug((string) ($d['id'] ?? '')) ?: Str::slug(Str::limit(str_replace(['[[', ']]'], '', (string) ($d['nav'] ?? '') ?: ($d['heading'] ?? '') ?: $type), 40, '')) ?: "section-$n";
            $base = $id;
            for ($k = 2; in_array($id, $ids, true) || in_array($id, ['faq', 'inquiry', 'contact'], true); $k++) $id = "$base-$k";
            $ids[] = $id;
            $s['id'] = $id;

            if (filled($d['nav'] ?? null)) $s['nav'] = trim((string) $d['nav']);
            if ($type !== 'logos') $s['heading'] = trim((string) ($d['heading'] ?? ''));
            if (filled(strip_tags((string) ($d['intro'] ?? '')))) $s['intro'] = Html::inline($d['intro']);
            foreach (['paras', 'bullets'] as $f) {
                $list = array_values(array_filter(array_map(fn ($x) => Html::inline(self::scalar($x)), (array) ($d[$f] ?? []))));
                if ($list || ($f === 'paras' && in_array($type, ['text', 'media'], true))) $s[$f] = $list;
            }
            $clean = fn (array $items, array $keys) => array_values(array_map(fn ($it) => array_map(fn ($k) => trim((string) ($it[$k] ?? '')), array_combine($keys, $keys)), $items));

            switch ($type) {
                case 'media':
                    $s['image'] = trim((string) ($d['image'] ?? ''));
                    $s['alt'] = trim((string) ($d['alt'] ?? ''));
                    if (! empty($d['flip'])) $s['flip'] = true;
                    if (($d['tone'] ?? '') === 'grey') $s['tone'] = 'grey';
                    break;
                case 'cards': case 'features':
                    $s['cards'] = $clean((array) ($d['cards'] ?? []), ['icon', 'title', 'text']);
                    break;
                case 'steps':
                    $s['steps'] = $clean((array) ($d['steps'] ?? []), ['title', 'text']);
                    break;
                case 'table':
                    $split = fn (string $line) => array_map('trim', explode('|', $line));
                    $s['columns'] = $split((string) ($d['columns_text'] ?? ''));
                    $s['rows'] = array_values(array_map($split, array_filter(preg_split('/\R/', (string) ($d['rows_text'] ?? '')), fn ($l) => trim($l) !== '')));
                    if (filled($d['note'] ?? null)) $s['note'] = trim((string) $d['note']);
                    break;
                case 'metrics':
                    $s['metrics'] = $clean((array) ($d['metrics'] ?? []), ['label', 'value', 'text']);
                    break;
                case 'impact':
                    $s['text'] = Html::inline($d['text'] ?? '');
                    $s['stats'] = $clean((array) ($d['stats'] ?? []), ['value', 'label']);
                    break;
                case 'reviews':
                    $s['reviews'] = $clean((array) ($d['reviews'] ?? []), ['name', 'role', 'text']);
                    break;
                case 'cases':
                    if (filled($d['service'] ?? null)) $s['service'] = (string) $d['service'];
                    break;
                case 'industries':
                    $s['items'] = $clean((array) ($d['items'] ?? []), ['slug', 'text']);
                    break;
                case 'cta':
                    $s['text'] = trim((string) ($d['text'] ?? ''));
                    $s['button'] = trim((string) ($d['button'] ?? '')) ?: 'Get in touch';
                    $s['link'] = self::link($d['link'] ?? '', '#inquiry');
                    $s['tone'] = ($d['tone'] ?? '') === 'dark' ? 'dark' : 'red';
                    break;
                case 'faq':
                    $s['items'] = $clean((array) ($d['items'] ?? []), ['title', 'text']);
                    break;
                case 'video':
                    $s['video'] = self::videoId(trim((string) ($d['url'] ?? '')));
                    $s['caption'] = trim((string) ($d['caption'] ?? ''));
                    break;
                case 'pricing':
                    $s['plans'] = array_values(array_map(fn ($p) => [
                        'name' => trim((string) ($p['name'] ?? '')), 'price' => trim((string) ($p['price'] ?? '')),
                        'period' => trim((string) ($p['period'] ?? '')), 'text' => trim((string) ($p['text'] ?? '')),
                        'features' => array_values(array_filter(array_map(fn ($x) => trim(self::scalar($x)), (array) ($p['features'] ?? [])))),
                        'highlight' => ! empty($p['highlight']),
                        'button' => trim((string) ($p['button'] ?? '')) ?: 'Get started',
                        'link' => self::link($p['link'] ?? '', '#inquiry'),
                    ], (array) ($d['plans'] ?? [])));
                    break;
            }
            $out[] = $s;
        }
        return $out;
    }
}
