<?php

namespace App\Support\Site;

use App\Models\CaseStudy;
use App\Models\Industry;
use App\Models\Page;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Revision;
use App\Models\SeoEntry;
use App\Models\SeoKeyword;

/**
 * Removes industries from the whole website: the industry and its page, its keyword-map entry, every link to it
 * (industry sections on other pages, semantic links, links written in page text, posts and case studies) and its
 * tag on case studies. The old address gets a 301 to /industries so visitors and Google are not left on a 404.
 */
class RemoveIndustry
{
    /** @param string[] $slugs */
    public static function run(array $slugs): void
    {
        if (! $slugs) return;
        $paths = array_map(fn ($s) => "/industries/$s", $slugs);
        $targets = [...$paths, ...array_map(fn ($s) => "i:$s", $slugs)];

        Industry::query()->whereIn('slug', $slugs)->delete();
        $keys = Page::query()->whereIn('path', $paths)->pluck('key');
        Revision::query()->where('model', 'page')->whereIn('model_key', $keys)->delete();
        Page::query()->whereIn('key', $keys)->delete();
        SeoEntry::query()->whereIn('key', array_map(fn ($p) => SeoEntry::keyFor($p), $paths))->delete();
        SeoKeyword::query()->whereIn('slug', $slugs)->delete();

        foreach (SeoKeyword::query()->get() as $k) {
            $links = array_values(array_filter((array) $k->links, fn ($l) => ! in_array($l['target'] ?? '', $targets, true)));
            if (count($links) !== count((array) $k->links)) { $k->links = $links; $k->save(); }
        }
        foreach (Page::query()->get() as $p) {
            $sections = array_map(function ($s) use ($slugs, $paths) {
                if (($s['type'] ?? '') === 'industries') $s['items'] = array_values(array_filter((array) ($s['items'] ?? []), fn ($i) => ! in_array($i['slug'] ?? '', $slugs, true)));
                return self::unlinkDeep($s, $paths);
            }, (array) $p->sections);
            $hero = self::unlinkDeep((array) $p->hero, $paths);
            $faqs = self::unlinkDeep((array) $p->faqs, $paths);
            if ($sections !== (array) $p->sections || $hero !== (array) $p->hero || $faqs !== (array) $p->faqs) {
                $p->forceFill(['sections' => $sections, 'hero' => $hero, 'faqs' => $faqs])->save();
            }
        }
        foreach (CaseStudy::query()->get() as $c) {
            $new = ['services' => array_values(array_diff((array) $c->services, $slugs))];
            foreach (['body', 'challenge', 'solution'] as $f) $new[$f] = self::unlink((string) $c->$f, $paths);
            $c->forceFill($new);
            if ($c->isDirty()) $c->save();
        }
        foreach (Post::query()->where('body', 'like', '%/industries/%')->get() as $post) {
            $post->body = self::unlink((string) $post->body, $paths);
            if ($post->isDirty()) $post->save();
        }
        foreach ($paths as $path) {
            Redirect::query()->where('to_path', $path)->update(['to_path' => '/industries']);
            Redirect::query()->updateOrCreate(['from_path' => $path], ['to_path' => '/industries', 'status_code' => 301, 'automatic' => true]);
        }
        PageCache::flush();
    }

    /** Links to the removed pages become plain text (HTML and Markdown links). */
    public static function unlink(string $text, array $paths): string
    {
        if ($text === '' || ! str_contains($text, '/industries/')) return $text;
        $alt = implode('|', array_map(fn ($p) => preg_quote($p, '#'), $paths));
        $text = preg_replace('#<a\b[^>]*href\s*=\s*["\'](?:https?://[^/"\']+)?(?:'.$alt.')/?(?:[?\#][^"\']*)?["\'][^>]*>(.*?)</a>#is', '$1', $text);
        return preg_replace('#\[([^\]]*)\]\((?:https?://[^/)]+)?(?:'.$alt.')/?\)#i', '$1', $text);
    }

    private static function unlinkDeep(mixed $v, array $paths): mixed
    {
        if (is_string($v)) return self::unlink($v, $paths);
        if (is_array($v)) return array_map(fn ($x) => self::unlinkDeep($x, $paths), $v);
        return $v;
    }
}
