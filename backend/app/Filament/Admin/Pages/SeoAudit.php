<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Resources\PageResource;
use App\Models\SeoKeyword;
use App\Support\Seo\Audit;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

/**
 * Every check for every page (SEO, AEO, GEO), the blog posts, the keyword and entity map, and the internal links:
 * orphans, broken semantic links and pages competing for the same keyword.
 */
class SeoAudit extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'SEO';
    protected static ?string $navigationLabel = 'SEO audit';
    protected static ?string $title = 'SEO audit';
    protected static ?string $slug = 'seo-audit';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.admin.pages.seo.audit';

    public const TABS = ['health' => 'Site health', 'broken' => 'Broken links', 'linking' => 'Inbound & outbound', 'external' => 'External links',
        'pages' => 'Page checks', 'posts' => 'Blog posts', 'keywords' => 'Keyword map', 'links' => 'Internal link plan'];

    #[Url] public string $show = 'all';

    #[Url] public string $tab = 'health';
    #[Url] public string $q = '';
    #[Url] public string $kind = '';
    #[Url] public string $sort = 'total';
    #[Url] public ?string $open = null;
    #[Url] public int $p = 1;

    public const PER_PAGE = 25;

    /** A new search, filter, sort or tab starts at page 1. */
    public function updated(string $name): void
    {
        if (in_array($name, ['q', 'kind', 'sort', 'tab', 'show'], true)) { $this->p = 1; $this->open = null; }
    }

    public function goTo(int $page): void
    {
        $this->p = max(1, $page);
        $this->open = null;
    }

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasPerm('seo.view');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('crawl')->label(fn () => \App\Support\Seo\Crawler::running() ? 'Crawling…' : 'Crawl the site now')->icon('heroicon-o-bug-ant')
                ->disabled(fn () => (bool) \App\Support\Seo\Crawler::running())
                ->requiresConfirmation()->modalIcon('heroicon-o-bug-ant')->modalHeading('Crawl the whole website?')
                ->modalDescription('Opens every page and checks every link and image, on this site and on others. It takes a minute or two; the results appear here when it is done.')
                ->modalSubmitActionLabel('Start crawl')
                ->action(function () {
                    // Runs after this response, so the page does not wait (and the crawl cannot disturb this request).
                    $id = \App\Support\Seo\Crawler::start('manual');
                    app()->terminating(fn () => (new \App\Support\Seo\Crawler())->run('manual', $id));
                    $this->tab = 'health';
                    \Filament\Notifications\Notification::make()->info()->title('Crawl started')->body('Results appear here in a minute or two.')->send();
                }),
            Action::make('rerun')->label('Rerun page checks')->icon('heroicon-o-arrow-path')->color('gray')->action(fn () => Audit::site(true)),
            Action::make('map')->label('Link map')->icon('heroicon-o-share')->color('gray')->url(LinkMap::getUrl()),
        ];
    }

    public function toggle(string $path): void
    {
        $this->open = $this->open === $path ? null : $path;
    }

    /** Results of the latest site crawl for the crawl tabs. */
    private function crawlData(string $q): array
    {
        $run = \App\Support\Seo\Crawler::latest();
        if (! $run) return ['run' => null];
        $db = \Illuminate\Support\Facades\DB::class;
        $prev = $db::table('crawl_runs')->where('status', 'done')->where('id', '<', $run->id)->orderByDesc('id')->first();
        $history = $db::table('crawl_runs')->where('status', 'done')->orderByDesc('id')->limit(10)->get()->reverse()->values();
        $search = fn ($query, array $cols) => $q === '' ? $query : $query->where(fn ($w) => array_map(fn ($c) => $w->orWhere($c, 'like', "%$q%"), $cols));
        $out = ['run' => $run, 'prev' => $prev, 'history' => $history];
        $per = self::PER_PAGE;
        $page = fn ($query) => [$query->count(), (clone $query)->skip(($this->p - 1) * $per)->take($per)->get()];
        if ($this->tab === 'health') {
            $out['errorPages'] = $db::table('crawl_pages')->where('run_id', $run->id)->where('status', '>=', 400)->orderBy('path')->limit(100)->get();
            $out['redirectPages'] = $db::table('crawl_pages')->where('run_id', $run->id)->whereBetween('status', [300, 399])->orderBy('path')->limit(100)->get();
            $out['slow'] = $db::table('crawl_pages')->where('run_id', $run->id)->where('status', 200)->orderByDesc('ms')->limit(8)->get();
            $out['orphans'] = $db::table('crawl_pages')->where('run_id', $run->id)->where('status', 200)->where('inbound', 0)->where('path', '!=', '/')->orderBy('path')->limit(50)->get();
            $out['issues'] = [
                'Missing title' => $db::table('crawl_pages')->where('run_id', $run->id)->where('status', 200)->where('title', '')->pluck('path'),
                'No H1 heading' => $db::table('crawl_pages')->where('run_id', $run->id)->where('status', 200)->where('h1', 0)->pluck('path'),
                'More than one H1' => $db::table('crawl_pages')->where('run_id', $run->id)->where('status', 200)->where('h1', '>', 1)->pluck('path'),
                'Thin content (under 300 words)' => $db::table('crawl_pages')->where('run_id', $run->id)->where('status', 200)->where('words', '<', 300)->where('noindex', false)->pluck('path'),
                'Hidden from search (noindex)' => $db::table('crawl_pages')->where('run_id', $run->id)->where('noindex', true)->pluck('path'),
                'Slow (over 1.5 s to build)' => $db::table('crawl_pages')->where('run_id', $run->id)->where('ms', '>', 1500)->pluck('path'),
            ];
            $out['dupTitles'] = $db::table('crawl_pages')->where('run_id', $run->id)->where('status', 200)->where('title', '!=', '')->selectRaw('title, count(*) n')->groupBy('title')->having('n', '>', 1)->limit(20)->get();
        }
        if ($this->tab === 'broken') {
            $query = $db::table('crawl_links')->where('run_id', $run->id)->where('ok', false)
                ->when($this->show === 'internal', fn ($w) => $w->where('internal', true))->when($this->show === 'external', fn ($w) => $w->where('internal', false))
                ->when($this->show === 'images', fn ($w) => $w->where('kind', 'image'))->orderBy('url')->orderBy('from_path');
            [$out['total'], $out['list']] = $page($search($query, ['url', 'from_path', 'anchor']));
        }
        if ($this->tab === 'linking') {
            $query = $db::table('crawl_pages')->where('run_id', $run->id)->where('status', 200)
                ->orderBy(match ($this->sort) { 'name' => 'path', 'out' => 'out_internal', 'ext' => 'out_external', default => 'inbound' }, $this->sort === 'name' ? 'asc' : ($this->sort === 'inbound' ? 'desc' : ($this->sort === 'total' ? 'asc' : 'desc')));
            [$out['total'], $out['list']] = $page($search($query, ['path', 'title']));
            if ($this->open) {
                $out['in'] = $db::table('crawl_links')->where('run_id', $run->id)->where('internal', true)->where('kind', 'link')->where('url', $this->open)->select('from_path', 'anchor')->distinct()->orderBy('from_path')->limit(200)->get();
                $out['outLinks'] = $db::table('crawl_links')->where('run_id', $run->id)->where('kind', 'link')->where('from_path', $this->open)->orderByDesc('internal')->orderBy('url')->limit(300)->get();
            }
        }
        if ($this->tab === 'external') {
            $query = $db::table('crawl_links')->where('run_id', $run->id)->where('internal', false)->where('kind', 'link')
                ->selectRaw('url, max(status) status, min(case when ok then 1 else 0 end) ok, count(distinct from_path) pages, max(nofollow) nofollow, max(anchor) anchor')->groupBy('url')
                ->when($this->show === 'broken', fn ($w) => $w->havingRaw('min(case when ok then 1 else 0 end) = 0'))->orderByDesc('pages')->orderBy('url');
            $query = $search($query, ['url', 'anchor']);
            $out['total'] = $db::query()->fromSub($query, 'x')->count();
            $out['list'] = (clone $query)->skip(($this->p - 1) * $per)->take($per)->get();
            $out['domains'] = $db::table('crawl_links')->where('run_id', $run->id)->where('internal', false)->where('kind', 'link')->pluck('url')
                ->map(fn ($u) => preg_replace('/^www\./', '', strtolower((string) parse_url($u, PHP_URL_HOST))))->countBy()->sortDesc()->take(12);
        }
        if (isset($out['total'])) $out['pages'] = max(1, (int) ceil($out['total'] / $per));
        return $out;
    }

    public static function editUrl(array $p): string
    {
        return PageResource::getUrl('edit', ['record' => $p['key']]);
    }

    protected function getViewData(): array
    {
        $a = Audit::site();
        $q = mb_strtolower(trim($this->q));
        $rows = array_values(array_filter($a['pages'], fn ($p) => (! $this->kind || $p['kind'] === $this->kind)
            && ($q === '' || str_contains(mb_strtolower($p['name'].' '.$p['path'].' '.$p['keyword']), $q))));
        $sort = in_array($this->sort, ['total', 'seo', 'aeo', 'geo', 'name'], true) ? $this->sort : 'total';
        usort($rows, fn ($x, $y) => $sort === 'name' ? strcmp($x['name'], $y['name']) : $x['scores'][$sort] <=> $y['scores'][$sort]);
        $posts = array_values(array_filter($a['posts'], fn ($p) => $q === '' || str_contains(mb_strtolower($p['title'].' '.$p['path'].' '.$p['keyword']), $q)));
        usort($posts, fn ($x, $y) => match ($sort) { 'name' => strcmp($x['title'], $y['title']), default => $x['score'] <=> $y['score'] });
        // One page of results for the open tab.
        $list = $this->tab === 'posts' ? $posts : $rows;
        $pages = max(1, (int) ceil(count($list) / self::PER_PAGE));
        if (in_array($this->tab, ['pages', 'posts'], true)) $this->p = min(max(1, $this->p), $pages);
        $slice = fn (array $l) => array_slice($l, ($this->p - 1) * self::PER_PAGE, self::PER_PAGE);
        $g = $a['graph'];
        $linkRows = array_values(array_filter($g['nodes'], fn ($n) => in_array($n['kind'], ['service', 'industry', 'category', 'landing'], true)));
        usort($linkRows, fn ($x, $y) => ($g['stats'][$x['id']]['in'] ?? 0) <=> ($g['stats'][$y['id']]['in'] ?? 0));
        $crawl = $this->crawlData($q);
        return [
            'crawl' => $crawl, 'a' => $a, 'rows' => $slice($rows), 'posts' => $slice($posts), 'total' => count($list), 'pages' => $pages, 'perPage' => self::PER_PAGE, 'map' => SeoKeyword::query()->get()->keyBy('slug'),
            'linkRows' => $linkRows, 'stats' => $g['stats'], 'tabs' => self::TABS,
        ];
    }
}
