<?php

namespace App\Filament\Support;

use App\Models\SeoEntry;
use App\Support\Site\Schema;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\HtmlString;

/**
 * "Schema markup" in the page, blog post and case study editors. Every page already outputs an automatic
 * schema.org graph (organisation, website, page, breadcrumbs and the page's own type: Service, BlogPosting,
 * Article, FAQPage...). Here editors can see it, switch it off, and add their own JSON-LD (HowTo, Product,
 * Event, Video, Review...) from ready-made templates. Stored with the page's SEO override (SEO > SEO overrides).
 */
class SchemaPanel
{
    /** Ready-made JSON-LD; {url} and {name} are filled with the page's address and title. */
    public const TEMPLATES = [
        'FAQPage' => ['label' => 'FAQ (extra questions)', 'json' => ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => [
            ['@type' => 'Question', 'name' => 'Your question?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'A 40-60 word answer.']]]]],
        'HowTo' => ['label' => 'How-to steps', 'json' => ['@context' => 'https://schema.org', '@type' => 'HowTo', 'name' => '{name}', 'totalTime' => 'PT30M', 'step' => [
            ['@type' => 'HowToStep', 'position' => 1, 'name' => 'First step', 'text' => 'What to do.'],
            ['@type' => 'HowToStep', 'position' => 2, 'name' => 'Second step', 'text' => 'What to do next.']]]],
        'Product' => ['label' => 'Product or package with a price', 'json' => ['@context' => 'https://schema.org', '@type' => 'Product', 'name' => '{name}', 'url' => '{url}',
            'brand' => ['@type' => 'Brand', 'name' => 'GTech Digital'], 'offers' => ['@type' => 'Offer', 'price' => '499', 'priceCurrency' => 'GBP', 'availability' => 'https://schema.org/InStock', 'url' => '{url}']]],
        'Review' => ['label' => 'Review with rating', 'json' => ['@context' => 'https://schema.org', '@type' => 'Review', 'itemReviewed' => ['@type' => 'Organization', 'name' => 'GTech Digital'],
            'author' => ['@type' => 'Person', 'name' => 'Customer name'], 'reviewRating' => ['@type' => 'Rating', 'ratingValue' => '5', 'bestRating' => '5'], 'reviewBody' => 'What they said.']],
        'VideoObject' => ['label' => 'Video', 'json' => ['@context' => 'https://schema.org', '@type' => 'VideoObject', 'name' => '{name}', 'description' => 'What the video shows.',
            'thumbnailUrl' => 'https://www.gtechdigital.co.uk/...jpg', 'uploadDate' => '2026-01-01', 'contentUrl' => 'https://www.youtube.com/watch?v=...']],
        'Event' => ['label' => 'Event or webinar', 'json' => ['@context' => 'https://schema.org', '@type' => 'Event', 'name' => '{name}', 'startDate' => '2026-01-01T10:00:00+00:00',
            'eventAttendanceMode' => 'https://schema.org/OnlineEventAttendanceMode', 'location' => ['@type' => 'VirtualLocation', 'url' => '{url}'], 'organizer' => ['@type' => 'Organization', 'name' => 'GTech Digital']]],
        'LocalBusiness' => ['label' => 'Local business (an office or branch)', 'json' => ['@context' => 'https://schema.org', '@type' => 'LocalBusiness', 'name' => 'GTech Digital', 'url' => '{url}',
            'telephone' => '+44 ...', 'address' => ['@type' => 'PostalAddress', 'streetAddress' => '...', 'addressLocality' => 'London', 'postalCode' => '...', 'addressCountry' => 'GB']]],
    ];

    /** The form section. $path(Get) gives the page's public address, $name(Get) its title, $auto the types it outputs automatically. */
    public static function section(\Closure $path, \Closure $name, string|\Closure $auto): Forms\Components\Section
    {
        return Forms\Components\Section::make('Schema markup (structured data)')->collapsible()
            ->description('Helps Google, Bing and AI assistants understand the page (rich results, AI answers).')
            ->schema([
                Forms\Components\Placeholder::make('schema_auto')->hiddenLabel()->content(fn (Get $get) => new HtmlString(
                    '<p style="font-size:13px;line-height:1.6">This page already outputs schema automatically: <b>'.e(is_string($auto) ? $auto : $auto($get)).'</b>, linked to your organisation and website. '
                    .'<a style="color:#e8202f;text-decoration:underline" target="_blank" rel="noopener" href="https://search.google.com/test/rich-results?url='.rawurlencode(Schema::abs($path($get))).'">Test it in Google</a> · '
                    .'<a style="color:#e8202f;text-decoration:underline" target="_blank" rel="noopener" href="https://validator.schema.org/#url='.rawurlencode(Schema::abs($path($get))).'">Schema validator</a></p>')),
                Forms\Components\Toggle::make('schema.off')->label('Turn off the automatic schema for this page')
                    ->helperText('Only if you replace it completely with your own below.'),
                Forms\Components\Select::make('schema.template')->label('Add a template')->placeholder('Choose one to add it below…')
                    ->options(array_map(fn ($t) => $t['label'], self::TEMPLATES))->live()->dehydrated(false)
                    ->afterStateUpdated(function (?string $state, Get $get, Set $set) use ($path, $name) {
                        if (! $state || ! isset(self::TEMPLATES[$state])) return;
                        $json = json_encode(self::TEMPLATES[$state]['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                        $json = strtr($json, ['{url}' => Schema::abs($path($get)), '{name}' => addslashes((string) $name($get))]);
                        $cur = trim((string) $get('schema.custom'));
                        // Several blocks are kept as one JSON array.
                        if ($cur === '') $new = $json;
                        else {
                            $old = json_decode($cur, true);
                            $list = is_array($old) && array_is_list($old) ? $old : ($old ? [$old] : null);
                            $new = $list === null ? $cur."\n".$json : json_encode([...$list, json_decode($json, true)], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                        }
                        $set('schema.custom', $new);
                        $set('schema.template', null);
                    }),
                Forms\Components\Textarea::make('schema.custom')->label('Your own JSON-LD (optional)')->rows(8)->extraInputAttributes(['style' => 'font-family:ui-monospace,Menlo,Consolas,monospace;font-size:13px', 'spellcheck' => 'false'])
                    ->placeholder("{\n  \"@context\": \"https://schema.org\",\n  \"@type\": \"HowTo\",\n  \"name\": \"…\"\n}")
                    ->rule(fn () => fn ($attr, $v, $fail) => ($err = Schema::validateCustom((string) $v)) ? $fail($err) : null)
                    ->helperText('Added next to the automatic schema. Saved straight away with the rest of this form (for pages: also when you only save a draft).'),
            ]);
    }

    /** Form state for a page's address. */
    public static function fill(string $path): array
    {
        $e = SeoEntry::query()->find(SeoEntry::keyFor($path));
        return ['off' => (bool) $e?->schema_off, 'custom' => (string) $e?->schema_custom];
    }

    /** Saves the panel to the page's SEO override (created if needed). */
    public static function save(string $path, ?array $state): void
    {
        if ($state === null) return;
        $off = (bool) ($state['off'] ?? false);
        $custom = trim((string) ($state['custom'] ?? ''));
        $key = SeoEntry::keyFor($path);
        $e = SeoEntry::query()->find($key);
        if (! $e && ! $off && $custom === '') return;
        $e ??= new SeoEntry(['key' => $key, 'path' => $path]);
        $e->forceFill(['path' => $path, 'schema_off' => $off, 'schema_custom' => $custom])->save();
    }

    /** The schema types a site page outputs on its own, by kind. */
    public static function autoTypes(?\App\Models\Page $p): string
    {
        return match (true) {
            ! $p => 'WebPage, BreadcrumbList',
            in_array($p->kind, ['service', 'industry'], true) => 'Service, WebPage, BreadcrumbList, FAQPage',
            $p->kind === 'landing' => 'WebPage, BreadcrumbList'.(empty($p->faqs) ? '' : ', FAQPage'),
            $p->kind === 'legal' => 'WebPage, BreadcrumbList',
            $p->slug === 'home' => 'WebPage, ItemList (services)',
            $p->slug === 'about' => 'AboutPage, BreadcrumbList, FAQPage',
            $p->slug === 'contact' => 'ContactPage, BreadcrumbList, FAQPage',
            default => 'CollectionPage, ItemList, BreadcrumbList'.(empty($p->faqs) ? '' : ', FAQPage'),
        };
    }
}
