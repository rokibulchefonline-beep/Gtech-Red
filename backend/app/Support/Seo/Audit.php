<?php

namespace App\Support\Seo;

use App\Models\Page;
use App\Models\Post;
use App\Models\SeoEntry;
use App\Models\SeoKeyword;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * SEO audit of the website, as rendered: classic ranking signals (SEO), answer engines such as featured
 * snippets and voice (AEO), and generative engines such as ChatGPT, Perplexity and AI Overviews (GEO).
 * Every service, industry and landing page is scored; blog posts get the editor's checks.
 */
class Audit
{
    private static function words(string $s): array
    {
        preg_match_all("/[A-Za-z0-9£%'’-]+/u", $s, $m);
        return $m[0];
    }

    /** Lower case, "&" read as "and", and "UI/UX" read the same as "UI UX". */
    private static function norm(string $t): string { return preg_replace('/\s+/', ' ', str_replace(['&', '/'], ['and', ' '], mb_strtolower($t))); }
    private static function has(string $hay, string $needle): bool { return $needle !== '' && str_contains(self::norm($hay), self::norm($needle)); }
    /** Text without tags. Only real tags are removed: a "<" in the copy (e.g. "< 3 months") is kept. */
    private static function plain(string $s): string
    {
        $s = preg_replace('#<(?:[a-zA-Z][^<>]*|/[a-zA-Z][^<>]*|!--.*?--)>#s', ' ', str_replace(['[[', ']]'], '', $s));
        return trim(preg_replace('/\s+/', ' ', html_entity_decode($s, ENT_QUOTES)));
    }

    /** "local seo services uk" -> "local seo": the words a page must really rank for. */
    public static function core(string $kw): string
    {
        return trim(preg_replace('/\s+/', ' ', preg_replace('/\b(services?|company|agency|uk|consultancy|management)\b/i', '', $kw)));
    }

    /** All visible copy of a page and its section headings. */
    private static function body(Page $p): array
    {
        $parts = [(string) ($p->hero['lead'] ?? ''), ...array_map('strval', (array) ($p->hero['points'] ?? []))];
        $h2 = [];
        foreach ((array) $p->sections as $s) {
            if (isset($s['heading']) && is_string($s['heading'])) { $h2[] = self::plain($s['heading']); $parts[] = $s['heading']; }
            foreach (['paras', 'bullets'] as $k) foreach ((array) ($s[$k] ?? []) as $x) $parts[] = (string) $x;
            foreach (['text', 'intro', 'note'] as $k) if (isset($s[$k]) && is_string($s[$k])) $parts[] = $s[$k];
            foreach (['cards', 'steps', 'metrics', 'stats', 'reviews', 'items'] as $k) foreach ((array) ($s[$k] ?? []) as $i) $parts[] = implode(' ', array_filter((array) $i, 'is_string'));
            foreach ((array) ($s['rows'] ?? []) as $r) $parts[] = implode(' ', (array) $r);
        }
        foreach ((array) $p->faqs as $f) { $parts[] = $f['q'] ?? ''; $parts[] = $f['a'] ?? ''; }
        return ['text' => self::plain(implode(" \n", $parts)), 'h2' => $h2];
    }

    /** @return array{path:string,key:string,name:string,kind:string,keyword:string,core:string,scores:array,stats:array,checks:array} */
    public static function page(Page $p, array $linkStats, array $ctx): array
    {
        $map = $ctx['map'][$p->slug] ?? null;
        $entry = $ctx['seo'][SeoEntry::keyFor($p->path)] ?? null;
        $name = $p->name;
        $keyword = ($entry?->focus_keyword ?: $p->focus_keyword) ?: ($map?->kw ?: ($p->hero['keyword'] ?? $name));
        $core = self::core($keyword) ?: $keyword;
        $h1 = self::plain((string) ($p->hero['h1'] ?? (($p->hero['keyword'] ?? $name).' Services That Deliver Results')));
        ['text' => $text, 'h2' => $h2] = self::body($p);
        $w = self::words($text);
        $lead = self::plain((string) ($p->hero['lead'] ?? ''));
        $leadWords = count(self::words($lead));
        $title = $entry?->title ?: $p->meta_title;
        $desc = $entry?->description ?: $p->meta_description;
        $tl = mb_strlen($title);
        $dl = mb_strlen($desc);
        $checks = [];
        $add = function (string $id, string $group, string $level, string $label, int $weight, ?string $detail = null, ?string $fix = null) use (&$checks) {
            $checks[] = compact('id', 'group', 'level', 'label', 'weight', 'detail', 'fix');
        };
        $lvl = fn (bool $pass, bool $warn = false) => $pass ? 'pass' : ($warn ? 'warn' : 'fail');

        // ---- SEO ----
        $add('title-len', 'SEO', $tl >= 30 && $tl <= 60 ? 'pass' : ($tl > 70 || $tl < 20 ? 'fail' : 'warn'), "SEO title length ($tl)", 2, 'Aim for 30–60 characters so Google does not cut it off.', 'Shorten or expand the SEO title.');
        $add('title-kw', 'SEO', $lvl(self::has($title, $core)), 'Primary keyword in the SEO title', 3, "Target: “{$keyword}” (core: “{$core}”).", "Add “{$core}” near the start of the title.");
        $add('desc-len', 'SEO', $dl >= 120 && $dl <= 160 ? 'pass' : ($dl > 175 || $dl < 90 ? 'fail' : 'warn'), "Meta description length ($dl)", 2, 'Aim for 120–160 characters.', 'Rewrite the meta description to 120–160 characters.');
        $add('desc-kw', 'SEO', $lvl(self::has($desc, $core)), 'Primary keyword in the meta description', 2, null, "Mention “{$core}” in the description.");
        $add('h1-kw', 'SEO', self::has($h1, $core) ? 'pass' : (self::has($h1, explode(' ', $core)[0]) ? 'warn' : 'fail'), 'Primary keyword in the H1', 3, "H1: “{$h1}”.", "Make the main heading contain “{$core}”.");
        $add('lead-kw', 'SEO', $lvl(self::has($lead, $core)), 'Primary keyword in the opening description', 2, null, "Use “{$core}” in the first sentence.");
        $sec = (array) ($map?->sec ?? []);
        $found = array_values(array_filter($sec, fn ($t) => self::has($text, preg_replace('/^(a|an|the) /i', '', $t))));
        $cov = $sec ? count($found) / count($sec) : 1;
        $missing = array_values(array_diff($sec, $found));
        $add('sec-kw', 'SEO', $cov >= 0.6 ? 'pass' : ($cov >= 0.35 ? 'warn' : 'fail'), 'Related keywords used ('.count($found).'/'.count($sec).')', 3, $missing ? 'Missing: '.implode(', ', $missing).'.' : null, 'Work the missing phrases into headings, bullets or paragraphs.');
        $kwHeads = array_filter($h2, fn ($h) => self::has($h, $core) || array_filter($sec, fn ($t) => self::has($h, $t)));
        $add('h2-kw', 'SEO', count($kwHeads) >= 3 ? 'pass' : (count($kwHeads) >= 1 ? 'warn' : 'fail'), 'Subheadings with keywords ('.count($kwHeads).'/'.count($h2).')', 2, null, 'Include the primary or a related keyword in more section headings.');
        $add('h2-count', 'SEO', count($h2) >= 8 ? 'pass' : (count($h2) >= 5 ? 'warn' : 'fail'), 'Page structure ('.count($h2).' sections)', 1);
        $add('words', 'SEO', count($w) >= 800 ? 'pass' : (count($w) >= 600 ? 'warn' : 'fail'), 'Content depth ('.count($w).' words)', 2, 'Aim for 800+ words of useful, non-repetitive copy.', 'Add detail, examples or FAQs.');
        $dens = $w ? (substr_count(self::norm($text), self::norm($core)) / count($w)) * 100 : 0;
        $add('density', 'SEO', $dens >= 0.3 && $dens <= 2.5 ? 'pass' : ($dens > 3.5 ? 'fail' : 'warn'), 'Keyword density '.number_format($dens, 2).'%', 1, 'Keep between about 0.3% and 2.5%.');
        $lo = $linkStats['out'] ?? 0;
        $li = $linkStats['in'] ?? 0;
        $add('links-out', 'SEO', $lo >= 6 ? 'pass' : ($lo >= 3 ? 'warn' : 'fail'), "Contextual internal links out ($lo)", 2, null, 'Add related services or semantic links (SEO > Keyword map).');
        $add('links-in', 'SEO', $li >= 4 ? 'pass' : ($li >= 1 ? 'warn' : 'fail'), "Contextual internal links in ($li)", 2, $li === 0 ? 'Orphan page: no page body links here.' : null, 'Link to this page from related service and industry pages.');
        $add('indexable', 'SEO', $lvl(! $entry?->noindex && empty(((array) $p->data)['noindex'])), 'Page can be indexed', 2, null, 'Remove "noindex" in SEO overrides if the page should rank.');

        // ---- AEO ----
        $snippet = $leadWords >= 25 && $leadWords <= 60 && self::has($lead, 'GTech Digital') && self::has($lead, $core);
        $add('lead-snippet', 'AEO', $snippet ? 'pass' : ($leadWords >= 20 && $leadWords <= 70 && self::has($lead, 'GTech Digital') ? 'warn' : 'fail'), "Snippet-ready opening ($leadWords words)", 3, 'A 25–60 word direct answer that names GTech Digital and the keyword is what featured snippets and AI answers quote.', 'Rewrite the opening as one clear definition sentence.');
        $faqs = (array) $p->faqs;
        $nf = count($faqs);
        $add('faq-count', 'AEO', $nf >= 6 ? 'pass' : ($nf >= 4 ? 'warn' : 'fail'), "FAQ questions ($nf)", 2, null, 'Add questions people actually ask (People Also Ask style).');
        $good = array_filter($faqs, fn ($f) => ($n = count(self::words(self::plain($f['a'] ?? '')))) >= 25 && $n <= 85);
        $ratio = $nf ? count($good) / $nf : 0;
        $add('faq-len', 'AEO', $ratio >= 0.8 ? 'pass' : ($ratio >= 0.5 ? 'warn' : 'fail'), 'FAQ answers 25–85 words ('.count($good)."/$nf)", 3, 'Answers in this range are the length AI assistants and snippets prefer.', 'Expand one-line answers or trim long ones.');
        $qOk = array_filter($faqs, fn ($f) => preg_match('/^(what|how|why|when|who|which|can|do|does|is|are|should|will|where)\b/i', trim($f['q'] ?? '')));
        $add('faq-form', 'AEO', $nf && count($qOk) === $nf ? 'pass' : (count($qOk) / max(1, $nf) >= 0.7 ? 'warn' : 'fail'), 'Questions written as real questions ('.count($qOk)."/$nf)", 1);
        $add('definition', 'AEO', array_filter($h2, fn ($h) => preg_match('/^what (is|are)\b/i', $h)) ? 'pass' : 'warn', 'A “What is …” definition section', 2, null, 'Add a heading that defines the service in plain words.');
        $types = array_column((array) $p->sections, 'type');
        $add('structured', 'AEO', $lvl((bool) array_intersect(['table', 'steps'], $types)), 'Process steps or comparison table (extractable answers)', 2, null, 'Add a Steps or Table section.');
        $add('faq-schema', 'AEO', $lvl(! $entry?->schema_off && $nf > 0), 'FAQPage schema emitted', 1, $entry?->schema_off ? 'Automatic schema is switched off for this page.' : null);

        // ---- GEO ----
        $brand = substr_count($text, 'GTech Digital') + (str_contains($title, 'GTech') ? 1 : 0) + 1; // +1: the "Reviewed by GTech Digital specialists" line
        $add('brand', 'GEO', $brand >= 3 ? 'pass' : ($brand >= 1 ? 'warn' : 'fail'), "Brand entity mentioned ({$brand}×)", 2, 'Generative engines build an entity from repeated, consistent naming.', 'Mention GTech Digital in the opening, a section and an FAQ answer.');
        $ent = (array) ($map?->ent ?? []);
        $entFound = array_values(array_filter($ent, fn ($t) => self::has($text, $t)));
        $er = $ent ? count($entFound) / count($ent) : 1;
        $entMissing = array_values(array_diff($ent, $entFound));
        $add('entities', 'GEO', $er >= 0.6 ? 'pass' : ($er >= 0.35 ? 'warn' : 'fail'), 'Topic entities covered ('.count($entFound).'/'.count($ent).')', 3, $entMissing ? 'Missing: '.implode(', ', $entMissing).'.' : null, 'Name the platforms, standards and concepts people associate with this service.');
        $nums = preg_match_all('/(£\s?\d[\d,.]*[kKmM]?|\d[\d,.]*\s?(%|x\b|months?|weeks?|days?|hours?|years?|\+))/', $text);
        $add('evidence', 'GEO', $nums >= 8 ? 'pass' : ($nums >= 4 ? 'warn' : 'fail'), "Specific numbers and evidence ($nums)", 2, 'Concrete figures make a page citable.', 'Add timeframes, ranges, prices and results.');
        $add('proof', 'GEO', in_array('reviews', $types, true) && in_array('cases', $types, true) ? 'pass' : 'warn', 'Reviews and case studies on the page', 2, null, 'Add a Reviews and a Case studies section.');
        $uk = preg_match_all('/\bUK\b|United Kingdom|British/', $text);
        $add('geo-ref', 'GEO', $uk >= 2 ? 'pass' : ($uk >= 1 ? 'warn' : 'fail'), "UK location signals ($uk)", 1);
        $add('sameas', 'GEO', $ctx['socials'] >= 3 ? 'pass' : 'warn', "Organisation schema links to profiles ({$ctx['socials']} profiles)", 1, null, 'Add social profile links in Site settings > Contact.');
        $add('fresh', 'GEO', $p->updated_at && $p->updated_at->gt(now()->subMonths(12)) ? 'pass' : 'warn', 'Updated in the last 12 months', 1, 'Freshness: AI engines prefer recently reviewed pages.', 'Review the page and save it.');

        $score = function (?string $g = null) use ($checks) {
            $cs = array_filter($checks, fn ($c) => ! $g || $c['group'] === $g);
            $t = array_sum(array_column($cs, 'weight'));
            return (int) round(array_sum(array_map(fn ($c) => $c['weight'] * ($c['level'] === 'pass' ? 1 : ($c['level'] === 'warn' ? 0.5 : 0)), $cs)) / max(1, $t) * 100);
        };
        return [
            'path' => $p->path, 'key' => $p->key, 'name' => $name, 'kind' => $p->kind, 'keyword' => $keyword, 'core' => $core, 'checks' => $checks,
            'scores' => ['seo' => $score('SEO'), 'aeo' => $score('AEO'), 'geo' => $score('GEO'), 'total' => $score()],
            'stats' => ['words' => count($w), 'faqs' => $nf, 'h2' => count($h2), 'linksIn' => $li, 'linksOut' => $lo, 'titleLen' => $tl, 'descLen' => $dl],
        ];
    }

    /** The whole site. Cached for 10 minutes (and cleared whenever content changes, with the page cache version). */
    public static function site(bool $fresh = false): array
    {
        $key = 'seo-audit:'.\App\Support\Site\PageCache::version();
        if ($fresh) Cache::forget($key);
        return Cache::remember($key, 600, function () {
            $g = LinkGraph::build();
            $stats = LinkGraph::stats($g);
            $ctx = [
                'map' => SeoKeyword::query()->get()->keyBy('slug')->all(),
                'seo' => SeoEntry::query()->get()->keyBy('key')->all(),
                'socials' => count(array_filter((array) (Setting::group('socials') ?: []), fn ($s) => ! empty($s['url']))),
            ];
            $pages = [];
            foreach (Page::query()->whereIn('kind', ['service', 'industry', 'landing'])->where('published', true)->orderBy('kind')->orderBy('sort')->get() as $p) {
                $pages[] = self::page($p, $stats[$p->path] ?? [], $ctx);
            }
            $avg = fn (string $k) => (int) round(array_sum(array_map(fn ($p) => $p['scores'][$k], $pages)) / max(1, count($pages)));
            $byCore = [];
            foreach ($pages as $p) $byCore[mb_strtolower($p['core'])][] = $p['path'];
            $conflicts = [];
            foreach ($byCore as $kw => $paths) if (count($paths) > 1) $conflicts[] = ['keyword' => $kw, 'paths' => $paths];
            $broken = LinkGraph::broken($g);
            $orphans = LinkGraph::orphans($g, $stats);
            $posts = array_map(fn (Post $p) => self::post($p), Post::query()->whereIn('status', ['published', 'scheduled'])->orderByDesc('date')->get()->all());
            $failing = array_sum(array_map(fn ($p) => count(array_filter($p['checks'], fn ($c) => $c['level'] === 'fail')), $pages));
            return [
                'at' => now()->toIso8601String(),
                'summary' => ['pages' => count($pages), 'seo' => $avg('seo'), 'aeo' => $avg('aeo'), 'geo' => $avg('geo'), 'total' => $avg('total'),
                    'failing' => $failing, 'conflicts' => count($conflicts), 'orphans' => count($orphans), 'broken' => count($broken),
                    'posts' => count($posts), 'postScore' => (int) round(array_sum(array_column($posts, 'score')) / max(1, count($posts)))],
                'pages' => $pages, 'posts' => $posts, 'conflicts' => $conflicts, 'orphans' => $orphans, 'broken' => $broken,
                'graph' => ['nodes' => array_values($g['nodes']), 'edges' => $g['edges'], 'stats' => $stats],
            ];
        });
    }

    /** The blog editor's checks for one post (a Yoast-style score). */
    public static function post(Post $p): array
    {
        $kw = mb_strtolower(trim((string) $p->focus_keyword));
        $title = trim($p->meta_title ?: $p->title);
        $desc = trim((string) ($p->meta_description ?: $p->excerpt));
        $html = $p->format === 'html' ? (string) $p->body : \App\Support\Site\Blog::markdownToHtml((string) $p->body);
        $text = self::plain(preg_replace('#<(/p|br|/h\d|/li)>#i', ' $0', $html));
        $w = self::words($text);
        preg_match_all('#<h2[^>]*>(.*?)</h2>#is', $html, $hm);
        $h2s = array_map(fn ($h) => mb_strtolower(self::plain($h)), $hm[1]);
        preg_match('#<p[^>]*>(.*?)</p>#is', $html, $fp);
        $first = mb_strtolower(self::plain($fp[1] ?? ''));
        $links = preg_match_all('#<a\s[^>]*href=#i', $html);
        $internal = preg_match_all('#<a\s[^>]*href="/(?!/)#i', $html);
        $imgs = preg_match_all('#<img\b#i', $html);
        $noAlt = preg_match_all('#<img\b(?![^>]*\balt="[^"]+")[^>]*>#i', $html);
        $checks = [];
        $c = function (string $label, bool|string $ok, ?string $hint = null) use (&$checks) { $checks[] = ['label' => $label, 'level' => $ok === true ? 'pass' : ($ok === 'warn' ? 'warn' : 'fail'), 'hint' => $hint]; };
        $tl = mb_strlen($title);
        $dl = mb_strlen($desc);
        $c("SEO title length ($tl/60)", $tl >= 30 && $tl <= 60 ? true : ($tl ? 'warn' : false), 'Aim for 30 to 60 characters.');
        $c("Meta description length ($dl/160)", $dl >= 120 && $dl <= 160 ? true : ($dl ? 'warn' : false), 'Aim for 120 to 160 characters.');
        if ($kw !== '') {
            $dens = $w ? substr_count(mb_strtolower($text), $kw) / count($w) * 100 : 0;
            $c('Focus keyword in the SEO title', str_contains(mb_strtolower($title), $kw));
            $c('Focus keyword in the meta description', str_contains(mb_strtolower($desc), $kw));
            $c('Focus keyword in the address', str_contains($p->slug, str_replace(' ', '-', $kw)));
            $c('Focus keyword in the opening paragraph', str_contains($first, $kw));
            $c('Focus keyword in a subheading', (bool) array_filter($h2s, fn ($h) => str_contains($h, $kw)));
            $c('Keyword density '.number_format($dens, 1).'%', $dens >= 0.3 && $dens <= 2.5 ? true : ($dens > 0 ? 'warn' : false), 'Roughly 0.5% to 2%.');
        } else {
            $c('No focus keyword set', false, 'Add the phrase you want this post to rank for (Search engines section).');
        }
        $c('Length: '.count($w).' words', count($w) >= 800 ? true : (count($w) >= 400 ? 'warn' : false), 'Guides of 800+ words tend to rank best.');
        $c('Subheadings ('.count($h2s).')', count($h2s) >= 3 ? true : (count($h2s) ? 'warn' : false), 'Use at least 3 H2 headings.');
        $c("Internal links ($internal)", $internal >= 2 ? true : ($internal ? 'warn' : false), 'Link to 2+ related services or posts (use "Link to a page on this site").');
        $c('Links in total ('.$links.')', $links >= 3 ? true : ($links ? 'warn' : false));
        $c('Has an image', $imgs > 0 || (bool) $p->image);
        $c('Images have alt text', $noAlt === 0 && ($p->image === '' || $p->image === null || filled($p->image_alt)), 'Describe each image for search engines and screen readers.');
        $c('Can be indexed', ! $p->noindex);
        $pts = array_sum(array_map(fn ($x) => $x['level'] === 'pass' ? 1 : ($x['level'] === 'warn' ? 0.5 : 0), $checks));
        return ['id' => $p->id, 'title' => $p->title, 'path' => '/blogs/'.$p->slug, 'keyword' => $p->focus_keyword, 'words' => count($w),
            'score' => (int) round($pts / max(1, count($checks)) * 100), 'checks' => $checks];
    }
}
