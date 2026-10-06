<?php

namespace App\Support;

use App\Models\CaseStudy;
use App\Models\Category;
use App\Models\Client;
use App\Models\Media;
use App\Models\Page;
use App\Models\Partner;
use App\Models\Post;
use App\Models\SeoEntry;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Serves MySQL rows in the same shape the website used to read from MongoDB (camelCase fields, `_id`),
 * so the Next.js pages work unchanged. Only collections the public website needs are exposed.
 */
class LegacyDocs
{
    public const COLLECTIONS = [
        'posts' => Post::class,
        'case_studies' => CaseStudy::class,
        'categories' => Category::class,
        'partners' => Partner::class,
        'clients' => Client::class,
        'seo' => SeoEntry::class,
        'media' => Media::class,
    ];

    /** @return array{rows?: array, total?: int} */
    public static function query(string $coll, array $filter, array $sort, int $limit, int $skip, bool $countOnly): array
    {
        if ($coll === 'settings') {
            $doc = ['_id' => 'site'] + Setting::publicView();
            $match = ! isset($filter['_id']) || $filter['_id'] === 'site';
            return $countOnly ? ['total' => $match ? 1 : 0] : ['rows' => $match ? [$doc] : []];
        }
        if ($coll === 'page_content') return self::pageContent($filter, $limit, $countOnly);
        $class = self::COLLECTIONS[$coll] ?? null;
        if (! $class) throw new \InvalidArgumentException("Unknown collection: $coll");

        /** @var Model $model */
        $model = new $class;
        $q = $class::query();
        foreach ($filter as $field => $cond) self::where($q, $model, (string) $field, $cond);
        if ($countOnly) return ['total' => $q->count()];

        foreach ($sort as $field => $dir) {
            $col = self::column($model, (string) $field);
            if ($col) $q->orderBy($col, (int) $dir < 0 ? 'desc' : 'asc');
        }
        $rows = $q->skip(max(0, $skip))->take(min(500, max(1, $limit ?: 100)))->get();
        return ['rows' => $rows->map(fn (Model $m) => self::doc($m))->all()];
    }

    /**
     * The website's page "overrides", now served from the full page content in `pages`. Giving every editable field
     * as an override renders exactly the same page, and edits made in the panel show up.
     */
    private static function pageContent(array $filter, int $limit, bool $countOnly): array
    {
        $q = Page::query()->whereIn('kind', ['page', 'service', 'industry']);
        foreach ($filter as $field => $cond) {
            $col = ['_id' => 'key', 'kind' => 'kind', 'slug' => 'slug'][$field] ?? null;
            if (! $col) throw new \InvalidArgumentException("Unknown field: $field");
            is_array($cond) && isset($cond['$in']) ? $q->whereIn($col, (array) $cond['$in']) : $q->where($col, $cond);
        }
        if ($countOnly) return ['total' => $q->count()];
        return ['rows' => $q->orderBy('sort')->take(min(500, max(1, $limit ?: 100)))->get()->map(fn (Page $p) => self::pageDoc($p))->all()];
    }

    public static function pageDoc(Page $p): array
    {
        $sections = [];
        foreach ((array) $p->sections as $s) {
            $e = array_intersect_key((array) $s, array_flip(['heading', 'intro', 'text', 'paras', 'bullets', 'cards', 'steps', 'stats', 'reviews', 'items']));
            if ($e && isset($s['id'])) $sections[$s['id']] = $e;
        }
        $hero = array_intersect_key((array) $p->hero, array_flip(['h1', 'keyword', 'lead', 'points']));
        return ['_id' => $p->key, 'kind' => $p->kind, 'slug' => $p->slug, 'metaTitle' => $p->meta_title, 'metaDescription' => $p->meta_description,
            'focusKeyword' => $p->focus_keyword, 'hero' => $hero, 'sections' => (object) $sections, 'faqs' => (array) $p->faqs,
            'updatedAt' => $p->updated_at?->format(DATE_ATOM)];
    }

    /** camelCase field from the website -> database column (or null when unknown). */
    private static function column(Model $m, string $field): ?string
    {
        if ($field === '_id') return $m->getKeyName();
        $col = Str::snake($field);
        return in_array($col, [...$m->getFillable(), 'created_at', 'updated_at', $m->getKeyName()], true) ? $col : null;
    }

    private static function where(Builder $q, Model $m, string $field, mixed $cond): void
    {
        if ($field === '_id' && $m->getKeyName() === 'id') {
            // Old MongoDB ids live in legacy_id; new rows are addressed by their numeric id.
            $ids = is_array($cond) && isset($cond['$in']) ? (array) $cond['$in'] : [$cond];
            $q->where(fn ($w) => $w->whereIn('legacy_id', $ids)->orWhereIn('id', array_filter($ids, 'is_numeric')));
            return;
        }
        $col = self::column($m, $field);
        if (! $col) throw new \InvalidArgumentException("Unknown field: $field");
        if (is_array($cond) && ! array_is_list($cond)) {
            foreach ($cond as $op => $v) {
                match ($op) {
                    '$in' => $q->whereIn($col, (array) $v),
                    '$ne' => $q->where(fn ($w) => $w->where($col, '!=', $v)->orWhereNull($col)),
                    default => throw new \InvalidArgumentException("Unsupported operator: $op"),
                };
            }
            return;
        }
        $q->where($col, $cond);
    }

    /** One row in the old document shape. */
    public static function doc(Model $m): array
    {
        $out = [];
        foreach ($m->attributesToArray() as $k => $v) {
            if (in_array($k, ['id', 'legacy_id', 'key', 'path'], true) && ! ($m instanceof SeoEntry && $k === 'path')) continue;
            $out[Str::camel($k)] = $v instanceof \DateTimeInterface ? $v->format(DATE_ATOM) : $v;
        }
        $out['_id'] = $m->getKeyName() === 'id' ? (string) ($m->getAttribute('legacy_id') ?: $m->getKey()) : (string) $m->getKey();
        if ($m instanceof Media) {
            $out = ['_id' => $out['_id'], 'name' => $m->name, 'type' => $m->type, 'size' => $m->size, 'url' => $m->url, 'createdAt' => $out['createdAt'] ?? null];
        }
        if ($m instanceof Post && isset($out['date']) && $out['date']) $out['date'] = $m->date?->toIso8601String();
        return $out;
    }
}
