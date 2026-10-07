<?php

namespace App\Support\Site;

use App\Models\CaseStudy;
use App\Models\Client;
use App\Models\CoreService;
use App\Models\Industry;
use App\Models\Page;
use App\Models\Partner;
use App\Models\Post;
use App\Models\ServiceGroup;
use App\Models\ServiceItem;
use App\Models\Stat;
use App\Models\Testimonial;
use Illuminate\Support\Collection;

/** Read helpers for the public site (the Blade versions of lib/data.ts, lib/content.ts and lib/mongo.ts). */
class Repo
{
    private static array $memo = [];

    private static function once(string $k, callable $fn): mixed
    {
        return array_key_exists($k, self::$memo) ? self::$memo[$k] : (self::$memo[$k] = $fn());
    }

    public static function flush(): void { self::$memo = []; }

    /** Uses this copy of a page for the rest of the request (the panel's preview of unsaved changes). */
    public static function put(Page $p): void
    {
        self::$memo["page:{$p->key}"] = $p;
    }

    public static function page(string $key): ?Page
    {
        return self::once("page:$key", fn () => Page::query()->find($key));
    }

    /** Section of the home page by id (heading, paras, bullets, text...). */
    public static function homeSection(string $id): array
    {
        return collect((array) self::page('page~home')?->sections)->firstWhere('id', $id) ?? ['heading' => ''];
    }

    public static function groups(): Collection
    {
        return self::once('groups', fn () => ServiceGroup::query()->with('items')->orderBy('sort')->get());
    }

    public static function group(string $slug): ?ServiceGroup
    {
        return self::groups()->firstWhere('slug', $slug);
    }

    /** @return array{group: ServiceGroup, item: ServiceItem}|null */
    public static function item(string $slug): ?array
    {
        foreach (self::groups() as $g) {
            $i = $g->items->firstWhere('slug', $slug);
            if ($i) return ['group' => $g, 'item' => $i];
        }
        return null;
    }

    public static function allItems(): Collection
    {
        return self::groups()->flatMap->items->keyBy('slug');
    }

    public static function industries(): Collection
    {
        return self::once('industries', fn () => Industry::query()->orderBy('sort')->get());
    }

    public static function industry(string $slug): ?Industry
    {
        return self::industries()->firstWhere('slug', $slug);
    }

    public static function clients(): array
    {
        return self::once('clients', fn () => Client::query()->where('visible', true)->where('logo', '!=', '')->orderBy('order')->orderBy('id')->get(['name', 'logo', 'url'])->toArray());
    }

    public static function partners(): array
    {
        return self::once('partners', fn () => Partner::query()->where('visible', true)->where('logo', '!=', '')->orderBy('order')->orderBy('id')->get(['name', 'logo', 'url'])->toArray());
    }

    public static function caseStudies(int $limit = 50): Collection
    {
        return self::once("cases:$limit", fn () => CaseStudy::query()->where('status', 'published')->orderBy('order')->orderByDesc('created_at')->orderBy('id')->limit($limit)->get());
    }

    public static function caseStudiesFor(string $service, int $limit = 6): Collection
    {
        return self::caseStudies(50)->filter(fn ($d) => in_array($service, (array) $d->services, true))->take($limit)->values();
    }

    public static function caseStudy(string $slug): ?CaseStudy
    {
        return CaseStudy::query()->where('slug', $slug)->where('status', 'published')->first();
    }

    /** Live posts: published, or scheduled with a date that has passed; public only; newest first. */
    public static function posts(): Collection
    {
        return self::once('posts', fn () => Post::query()->whereIn('status', ['published', 'scheduled'])->get()
            ->filter(fn (Post $p) => $p->visibility !== 'private' && ($p->status === 'published' || ($p->date && $p->date->isPast())))
            ->sortByDesc(fn (Post $p) => $p->date?->toIso8601String() ?? '')->values());
    }

    public static function coreServices(): Collection
    {
        return self::once('core', fn () => CoreService::query()->orderBy('sort')->get());
    }

    public static function stats(): Collection
    {
        return self::once('stats', fn () => Stat::query()->orderBy('sort')->get());
    }

    public static function testimonials(): Collection
    {
        return self::once('testimonials', fn () => Testimonial::query()->where('visible', true)->orderBy('sort')->get());
    }

    /** The CTA label under each image-and-text section (same rule as the website). */
    public static function ctaLabel(string $slug, string $name, string $id): string
    {
        if ($slug === 'about') return 'Work With Us';
        $opts = ["Get a Free $name Audit", "Talk to Our $name Team", "Get a $name Quote"];
        return $opts[array_sum(array_map('ord', str_split($id))) % 3];
    }

    /** encodeURIComponent() as in JavaScript. */
    public static function uri(string $s): string
    {
        return strtr(rawurlencode($s), ['%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')']);
    }
}
