<?php

namespace App\Support\Site;

use App\Models\SeoKeyword;
use App\Models\Setting;

/**
 * JSON-LD builders (a port of the website's lib/schema.ts). Every page emits one connected @graph:
 * Organization <- WebSite <- WebPage <- the page's main entity (Service, Article...), so search engines and
 * AI assistants can follow one consistent entity: GTech Digital, its services and the evidence behind them.
 */
class Schema
{
    public static function base(): string
    {
        return config('gtech.public_url');
    }

    public static function abs(string $p): string
    {
        return str_starts_with($p, 'http') ? $p : self::base().($p === '/' ? '/' : $p);
    }

    public static function org(): string { return self::base().'/#organization'; }
    public static function site(): string { return self::base().'/#website'; }
    public static function pageId(string $path): string { return self::abs($path).'#webpage'; }
    private static function ref(string $id): array { return ['@id' => $id]; }

    /** Date for dateModified when a page gives none (the website used its build date). */
    public static ?string $modified = null;
    private static function today(): string { return self::$modified ?? now()->format('Y-m-d'); }

    public static function orgNode(): array
    {
        $s = Setting::all_();
        $c = $s['contact'];
        return [
            '@type' => ['Organization', 'ProfessionalService'], '@id' => self::org(), 'name' => $s['general']['siteName'], 'url' => self::base().'/',
            'logo' => ['@type' => 'ImageObject', 'url' => self::base().'/logo.png'], 'image' => self::base().'/logo.png',
            'description' => 'GTech Digital is a UK digital agency providing digital marketing, SEO, Google Ads, social media marketing, web design and development, custom software development and branding services.',
            'email' => $c['email'], 'telephone' => $c['phone'], 'priceRange' => '££', 'areaServed' => ['@type' => 'Country', 'name' => 'United Kingdom'], 'slogan' => $s['general']['tagline'],
            ...(! empty($c['address']) ? ['address' => ['@type' => 'PostalAddress', 'streetAddress' => $c['address'], 'addressCountry' => 'GB']] : []),
            'contactPoint' => ['@type' => 'ContactPoint', 'contactType' => 'sales', 'email' => $c['email'], 'telephone' => $c['phone'], 'areaServed' => 'GB', 'availableLanguage' => 'English'],
            'sameAs' => array_values(array_filter(array_map(fn ($x) => $x['url'] ?? '', $s['socials'] ?? []))),
            'knowsAbout' => ['Digital marketing', 'Search engine optimisation', 'Answer engine optimisation', 'Generative engine optimisation', 'Google Ads', 'Social media marketing', 'Web design', 'Web development', 'Custom software development', 'Branding'],
        ];
    }

    public static function siteNode(): array
    {
        $g = Setting::all_()['general'];
        return ['@type' => 'WebSite', '@id' => self::site(), 'url' => self::base().'/', 'name' => $g['siteName'], 'description' => $g['tagline'], 'inLanguage' => 'en-GB', 'publisher' => self::ref(self::org())];
    }

    /** @param array{path:string,name:string,description:string,type?:string|array,mainEntity?:string,about?:array,image?:string,published?:string,modified?:string,speakable?:bool,breadcrumb?:bool} $o */
    public static function page(array $o): array
    {
        $p = $o['path'];
        return [
            '@type' => $o['type'] ?? 'WebPage', '@id' => self::pageId($p), 'url' => self::abs($p), 'name' => $o['name'], 'description' => $o['description'], 'inLanguage' => 'en-GB',
            'isPartOf' => self::ref(self::site()), 'about' => self::ref(self::org()), 'publisher' => self::ref(self::org()),
            ...(($o['breadcrumb'] ?? true) !== false && $p !== '/' ? ['breadcrumb' => self::ref(self::abs($p).'#breadcrumb')] : []),
            ...(! empty($o['mainEntity']) ? ['mainEntity' => self::ref($o['mainEntity'])] : []),
            ...(! empty($o['about']) ? ['mentions' => array_map(fn ($n) => ['@type' => 'Thing', 'name' => $n], array_values($o['about']))] : []),
            ...(! empty($o['image']) ? ['primaryImageOfPage' => ['@type' => 'ImageObject', 'url' => self::abs($o['image'])]] : []),
            ...(! empty($o['published']) ? ['datePublished' => $o['published']] : []), 'dateModified' => $o['modified'] ?? self::today(),
            ...(($o['speakable'] ?? true) === false ? [] : ['speakable' => ['@type' => 'SpeakableSpecification', 'cssSelector' => ['h1', '.sp-lead']]]),
        ];
    }

    /** @param array<array{0:string,1:string}> $items */
    public static function breadcrumb(string $path, array $items): array
    {
        $all = [['Home', '/'], ...$items];
        return ['@type' => 'BreadcrumbList', '@id' => self::abs($path).'#breadcrumb', 'itemListElement' => array_map(fn ($x, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $x[0], 'item' => self::abs($x[1])], $all, array_keys($all))];
    }

    /** Keyword map entry for a service or industry (Site structure > Keyword map). */
    public static function keywords(string $slug): ?SeoKeyword
    {
        static $memo = [];
        return array_key_exists($slug, $memo) ? $memo[$slug] : ($memo[$slug] = SeoKeyword::query()->find($slug));
    }

    /** The semantic links of a page (semanticLinksFor in lib/link-graph.ts). */
    public static function semanticLinks(string $slug): array
    {
        $e = self::keywords($slug);
        if (! $e) return [];
        return array_map(function ($l) {
            $t = $l['target'];
            $href = str_starts_with($t, '/') ? $t : (str_starts_with($t, 'i:') ? '/industries/'.substr($t, 2) : "/services/$t");
            $group = str_starts_with($t, 'i:') ? 'industry' : (str_starts_with($t, '/') ? 'page' : (Repo::group($t) ? 'category' : 'service'));
            return ['href' => $href, 'anchor' => $l['anchor'], 'group' => $group];
        }, (array) $e->links);
    }

    /** @param array{path:string,slug:string,name:string,description:string,category?:string,audience?:string,related?:array} $o */
    public static function service(array $o): array
    {
        $e = self::keywords($o['slug']);
        $p = $o['path'];
        return [
            '@type' => 'Service', '@id' => self::abs($p).'#service', 'name' => $o['name'], 'serviceType' => $o['name'], 'description' => $o['description'], 'url' => self::abs($p),
            'provider' => self::ref(self::org()), 'areaServed' => ['@type' => 'Country', 'name' => 'United Kingdom'], 'mainEntityOfPage' => self::ref(self::pageId($p)),
            ...(! empty($o['category']) ? ['category' => $o['category']] : []), ...(! empty($o['audience']) ? ['audience' => ['@type' => 'BusinessAudience', 'name' => $o['audience']]] : []),
            ...($e ? ['keywords' => implode(', ', [$e->kw, ...(array) $e->sec]), 'about' => array_map(fn ($n) => ['@type' => 'Thing', 'name' => $n], (array) $e->ent)] : []),
            ...(! empty($o['related']) ? ['isRelatedTo' => array_map(fn ($r) => ['@type' => 'Service', 'name' => $r['name'], 'url' => self::abs($r['path'])], $o['related'])] : []),
            'termsOfService' => self::base().'/terms',
        ];
    }

    /** Related services of a service or industry page, plus its semantic links (the isRelatedTo list). */
    public static function relatedFor(string $slug, array $related): array
    {
        $out = [];
        foreach ($related as $r) if ($f = Repo::item($r)) $out[] = ['name' => $f['item']->name, 'path' => '/services/'.$f['item']->slug];
        foreach (self::semanticLinks($slug) as $l) if ($l['group'] !== 'page') $out[] = ['name' => $l['anchor'], 'path' => $l['href']];
        return $out;
    }

    public static function faq(string $path, array $faqs): array
    {
        return [
            '@type' => 'FAQPage', '@id' => self::abs($path).'#faq', 'isPartOf' => self::ref(self::pageId($path)), 'inLanguage' => 'en-GB',
            'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => Blog::stripHtml($f['a'])]], array_values($faqs)),
        ];
    }

    /** @param array<array{0:string,1:string}> $items [name, path] */
    public static function itemList(string $path, string $name, array $items): array
    {
        return [
            '@type' => 'ItemList', '@id' => self::abs($path).'#list', 'name' => $name, 'numberOfItems' => count($items),
            'itemListElement' => array_map(fn ($x, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $x[0], 'url' => self::abs($x[1])], $items, array_keys($items)),
        ];
    }

    /** @param array{path:string,type?:string,headline:string,description:string,image?:string,published?:string,modified?:string,section?:string,keywords?:array,words?:int,author?:string} $o */
    public static function article(array $o): array
    {
        $p = $o['path'];
        return [
            '@type' => $o['type'] ?? 'Article', '@id' => self::abs($p).'#article', 'headline' => $o['headline'], 'description' => $o['description'], 'mainEntityOfPage' => self::ref(self::pageId($p)), 'url' => self::abs($p), 'inLanguage' => 'en-GB',
            ...(! empty($o['image']) ? ['image' => self::abs($o['image'])] : []), ...(! empty($o['published']) ? ['datePublished' => $o['published']] : []), 'dateModified' => $o['modified'] ?? $o['published'] ?? self::today(),
            ...(! empty($o['section']) ? ['articleSection' => $o['section']] : []), ...(! empty($o['keywords']) ? ['keywords' => implode(', ', $o['keywords'])] : []), ...(! empty($o['words']) ? ['wordCount' => $o['words']] : []),
            'author' => ! empty($o['author']) ? ['@type' => 'Organization', 'name' => $o['author'], 'url' => self::base().'/about'] : self::ref(self::org()), 'publisher' => self::ref(self::org()), 'isPartOf' => self::ref(self::pageId($p)),
        ];
    }

    public static function graph(array $nodes): array
    {
        return ['@context' => 'https://schema.org', '@graph' => [self::orgNode(), self::siteNode(), ...$nodes]];
    }

    /** JSON for a <script> tag, written like JSON.stringify (no escaped slashes or Unicode; "<" escaped). */
    public static function json(mixed $v): string
    {
        return str_replace('<', '\u003c', json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /** Checks custom JSON-LD from the SEO screen. Returns an error message or null. */
    public static function validateCustom(?string $text): ?string
    {
        $text = (string) $text;
        if (trim($text) === '') return null;
        if (strlen($text) > 20000) return 'The custom schema is too long (20,000 characters max).';
        $v = json_decode($text);
        if (json_last_error() !== JSON_ERROR_NONE) return 'Custom schema is not valid JSON.';
        $items = is_array($v) ? $v : [$v];
        if (! $items || array_filter($items, fn ($x) => ! is_object($x))) return 'Custom schema must be a JSON object or an array of objects.';
        if (array_filter($items, fn ($x) => ! isset($x->{'@type'}) && ! isset($x->{'@graph'}))) return 'Each custom schema object needs an @type (or an @graph).';
        return null;
    }
}
