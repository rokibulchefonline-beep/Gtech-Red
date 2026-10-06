<?php

namespace App\Support\Site;

use App\Models\SeoEntry;

/**
 * Page head for the public site: title, description, canonical, robots, Open Graph and Twitter tags, and the
 * JSON-LD graph. Built-in values come from each page; the panel's SEO overrides (Website content > SEO) win,
 * as on the website (lib/seo.ts and components/Schema.tsx).
 *
 * Differences from the Next.js output, on purpose: canonical and image URLs are absolute (the website printed
 * relative canonicals and localhost image URLs), and every page gets a canonical and basic Open Graph tags.
 */
class Seo
{
    /**
     * @param array{title:string, absolute?:bool, description?:string, canonical?:string, noindex?:bool, keywords?:array,
     *   og?:array{type?:string,title?:string,description?:string,image?:string,published?:string,author?:string}} $base
     * @param array $nodes JSON-LD nodes of the page (Organization and WebSite are added)
     */
    public static function make(string $path, array $base, array $nodes = []): array
    {
        $o = SeoEntry::query()->find(SeoEntry::keyFor($path));
        $name = \App\Models\Setting::all_()['general']['siteName'] ?? 'GTech Digital';
        $title = ($base['absolute'] ?? false) ? $base['title'] : $base['title'].' | '.$name;
        if ($o?->title) $title = $o->title;
        $description = $o?->description ?: ($base['description'] ?? '');
        $canonical = Schema::abs($o?->canonical ?: ($base['canonical'] ?? $path));
        $noindex = (bool) ($o?->noindex || ($base['noindex'] ?? false));

        $og = $base['og'] ?? [];
        // An override image replaces the page's Open Graph block, as on the website.
        if ($o?->og_image) $og = ['image' => $o->og_image];
        $ogTitle = $og['title'] ?? ($o?->title ?: $base['title']);
        $image = ! empty($og['image']) ? Schema::abs($og['image']) : '';

        $scripts = [];
        if (! $o?->schema_off) $scripts[] = Schema::json(Schema::graph($nodes));
        if ($o?->schema_custom && ! Schema::validateCustom($o->schema_custom)) $scripts[] = Schema::json(json_decode($o->schema_custom));

        return [
            'title' => $title, 'description' => $description, 'canonical' => $canonical,
            'robots' => $noindex ? 'noindex, follow' : '',
            'keywords' => implode(',', $base['keywords'] ?? []),
            'og' => array_filter([
                'og:type' => $og['type'] ?? 'website', 'og:site_name' => $name, 'og:locale' => 'en_GB', 'og:url' => $canonical,
                'og:title' => $ogTitle, 'og:description' => $og['description'] ?? $description, 'og:image' => $image,
                'article:published_time' => $og['published'] ?? '', 'article:author' => $og['author'] ?? '',
            ]),
            'twitter' => array_filter([
                'twitter:card' => $image ? 'summary_large_image' : 'summary', 'twitter:title' => $ogTitle,
                'twitter:description' => $og['description'] ?? $description, 'twitter:image' => $image,
            ]),
            'schema' => $scripts,
        ];
    }
}
