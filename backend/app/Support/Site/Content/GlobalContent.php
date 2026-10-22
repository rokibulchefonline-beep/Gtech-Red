<?php

namespace App\Support\Site\Content;

use App\Models\CaseStudy;
use App\Models\Page;
use App\Models\SeoKeyword;
use Illuminate\Support\Facades\DB;

/**
 * The October 2026 content rewrite: GTech Digital as a London-based agency serving the UK and the world, with every page
 * rewritten for SEO, AEO and GEO (database/data/content-global.json). Each change carries a fingerprint of the text it
 * replaces and is applied only while that text is unchanged, so anything edited in the admin is kept. Sections are
 * matched one by one, so editing one section never blocks the rest of the page. Saves go through the models, so every
 * change is in the version history.
 */
class GlobalContent
{
    public static function fp(mixed $v): string
    {
        return sha1(json_encode($v ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /** @return array{0:int,1:int} [fields changed, fields kept because they were edited] */
    public static function apply(): array
    {
        $file = database_path('data/content-global.json');
        if (! is_file($file)) return [0, 0];
        $d = json_decode((string) file_get_contents($file), true) ?: [];
        $done = $kept = 0;
        $swap = function (mixed $live, array $c) use (&$done, &$kept) {
            if (self::fp($live) === $c['old']) { $done++; return [true, $c['new']]; }
            if (self::fp($live) !== self::fp($c['new'])) $kept++;
            return [false, $live];
        };

        foreach ($d['pages'] ?? [] as $key => $fields) {
            $p = Page::query()->find($key);
            if (! $p) continue;
            $up = [];
            foreach ($fields as $f => $c) {
                if ($f === 'sections') continue;
                [$ok, $v] = $swap($p->{$f}, $c);
                if ($ok) $up[$f] = $v;
            }
            if (! empty($fields['sections'])) {
                $secs = (array) $p->sections;
                $changed = false;
                foreach ($fields['sections'] as $id => $c) {
                    foreach ($secs as $i => $s) {
                        if (($s['id'] ?? null) !== $id) continue;
                        [$ok, $v] = $swap($s, $c);
                        if ($ok) { $secs[$i] = $v; $changed = true; }
                        break;
                    }
                }
                if ($changed) $up['sections'] = $secs;
            }
            if ($up) $p->forceFill($up)->save();
        }

        foreach (['items' => ['service_items', 'blurb'], 'groups' => ['service_groups', 'intro']] as $k => [$table, $col]) {
            foreach ($d[$k] ?? [] as $slug => $c) {
                $live = DB::table($table)->where('slug', $slug)->value($col);
                [$ok, $v] = $swap($live, $c);
                if ($ok) DB::table($table)->where('slug', $slug)->update([$col => $v, 'updated_at' => now()]);
            }
        }
        foreach ($d['core'] ?? [] as $slug => $fields) {
            $row = DB::table('core_services')->where('slug', $slug)->first();
            if (! $row) continue;
            foreach ($fields as $f => $c) {
                $live = $f === 'points' ? json_decode((string) $row->points, true) : $row->{$f};
                [$ok, $v] = $swap($live, $c);
                if ($ok) DB::table('core_services')->where('slug', $slug)->update([$f => $f === 'points' ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v, 'updated_at' => now()]);
            }
        }
        foreach ($d['cases'] ?? [] as $slug => $fields) {
            $cs = CaseStudy::query()->where('slug', $slug)->first();
            if (! $cs) continue;
            $up = [];
            foreach ($fields as $f => $c) {
                [$ok, $v] = $swap($cs->{$f}, $c);
                if ($ok) $up[$f] = $v;
            }
            if ($up) $cs->forceFill($up)->save();
        }
        // Keyword map (used for the Service schema and the SEO tools): new terms are added, nothing is removed.
        foreach ($d['keywords'] ?? [] as $slug => $k) {
            $row = SeoKeyword::query()->find($slug);
            if (! $row) {
                SeoKeyword::query()->create(['slug' => $slug, 'kw' => $k['kw'], 'sec' => $k['sec'], 'ent' => $k['ent'], 'links' => []]);
                continue;
            }
            $merge = fn (array $a, array $b) => array_values(array_unique(array_merge($a, $b), SORT_REGULAR));
            $row->forceFill(['sec' => $merge((array) $row->sec, $k['sec']), 'ent' => $merge((array) $row->ent, $k['ent'])])->save();
        }

        \App\Support\Site\PageCache::flush();
        \App\Support\Site\Repo::flush();

        return [$done, $kept];
    }
}
