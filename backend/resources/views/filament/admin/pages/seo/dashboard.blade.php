<x-filament-panels::page>
    @php($audit = \App\Filament\Admin\Pages\SeoAudit::class)
    <p class="-mt-4 text-sm text-gray-500 dark:text-gray-400">
        {{ $s['pages'] }} service, industry and landing pages and {{ $s['posts'] }} blog posts checked {{ \Illuminate\Support\Carbon::parse($a['at'])->diffForHumans() }}.
        SEO = ranking in Google and Bing. AEO = being the answer (featured snippets, voice). GEO = being cited by AI (ChatGPT, Perplexity, AI Overviews).
    </p>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
        @include('filament.admin.pages.seo.score', ['n' => $s['total'], 'label' => 'Overall'])
        @include('filament.admin.pages.seo.score', ['n' => $s['seo'], 'label' => 'SEO'])
        @include('filament.admin.pages.seo.score', ['n' => $s['aeo'], 'label' => 'AEO (answers)'])
        @include('filament.admin.pages.seo.score', ['n' => $s['geo'], 'label' => 'GEO (AI engines)'])
        @include('filament.admin.pages.seo.score', ['n' => $s['postScore'], 'label' => 'Blog posts', 'sub' => 'Average of '.$s['posts'].' posts'])
    </div>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
        @foreach ([
            ['Failing checks', $s['failing'], $s['failing'] ? 'danger' : 'success', $audit::getUrl()],
            ['Orphan pages', $s['orphans'], $s['orphans'] ? 'danger' : 'success', $audit::getUrl(['tab' => 'links'])],
            ['Broken links', $s['broken'], $s['broken'] ? 'danger' : 'success', $audit::getUrl(['tab' => 'links'])],
            ['Keyword conflicts', $s['conflicts'], $s['conflicts'] ? 'warning' : 'success', $audit::getUrl(['tab' => 'keywords'])],
            ['Search visits (30 days)', $traffic['search'], 'gray', \App\Filament\Admin\Pages\Analytics::getUrl(['channel' => 'Search'])],
            ['AI assistant visits (30 days)', $traffic['ai'], 'gray', \App\Filament\Admin\Pages\Analytics::getUrl(['channel' => 'AI'])],
        ] as [$label, $value, $color, $url])
            <a href="{{ $url }}" class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 transition hover:ring-primary-500 dark:bg-gray-900 dark:ring-white/10">
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</p>
                <p class="mt-1 text-2xl font-semibold {{ $color === 'danger' ? 'text-danger-600 dark:text-danger-400' : ($color === 'warning' ? 'text-warning-600 dark:text-warning-400' : ($color === 'success' ? 'text-success-600 dark:text-success-400' : 'text-gray-950 dark:text-white')) }}">{{ number_format($value) }}</p>
            </a>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-filament::section heading="Most common problems" description="Across all audited pages: fix these first for the biggest gain.">
            @if (! $issues)
                <p class="text-sm text-success-600">No problems found.</p>
            @else
                <ul class="divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($issues as $id => $i)
                        <li class="flex items-start gap-3 py-2.5">
                            <x-filament::badge size="sm" :color="$i['fail'] ? 'danger' : 'warning'">{{ $i['group'] }}</x-filament::badge>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $i['label'] }}</p>
                                @if ($i['fix'])<p class="text-xs text-gray-500 dark:text-gray-400">{{ $i['fix'] }}</p>@endif
                            </div>
                            <span class="whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">{{ $i['fail'] + $i['warn'] }} {{ str('page')->plural($i['fail'] + $i['warn']) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-filament::section>

        <x-filament::section heading="Pages that need the most work" description="Lowest overall score first. Open one to see every check.">
            <ul class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($weakest as $p)
                    <li class="flex items-center gap-3 py-2.5">
                        <div class="min-w-0 flex-1">
                            <a href="{{ $audit::getUrl(['open' => $p['path']]) }}" class="text-sm font-medium text-gray-950 hover:underline dark:text-white">{{ $p['name'] }}</a>
                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $p['path'] }} · {{ $p['keyword'] }}</p>
                        </div>
                        @foreach (['seo' => 'SEO', 'aeo' => 'AEO', 'geo' => 'GEO'] as $k => $l)
                            <span class="hidden text-xs text-gray-500 sm:inline dark:text-gray-400">{{ $l }} {{ $p['scores'][$k] }}</span>
                        @endforeach
                        <x-filament::badge :color="\App\Filament\Admin\Pages\SeoDashboard::tone($p['scores']['total'])">{{ $p['scores']['total'] }}</x-filament::badge>
                    </li>
                @endforeach
            </ul>
        </x-filament::section>

        <x-filament::section heading="Blog posts to improve" description="Focus keyword, length, headings, internal links and images.">
            <ul class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($posts as $p)
                    @php($top = collect($p['checks'])->firstWhere('level', 'fail'))
                    <li class="flex items-center gap-3 py-2.5">
                        <div class="min-w-0 flex-1">
                            <a href="{{ \App\Filament\Admin\Resources\PostResource::getUrl('edit', ['record' => $p['id']]) }}" class="text-sm font-medium text-gray-950 hover:underline dark:text-white">{{ $p['title'] }}</a>
                            @if ($top)<p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $top['label'] }}</p>@endif
                        </div>
                        <x-filament::badge :color="\App\Filament\Admin\Pages\SeoDashboard::tone($p['score'])">{{ $p['score'] }}</x-filament::badge>
                    </li>
                @endforeach
            </ul>
        </x-filament::section>

        <x-filament::section heading="Search and AI visibility" description="Last 30 days, from the website's own analytics.">
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ([['Search engines', $search], ['AI assistants', $ai], ['AI crawlers reading the site', $aiBots]] as [$h, $rows])
                    <div>
                        <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $h }}</p>
                        @forelse ($rows as $row)
                            <p class="flex justify-between gap-2 py-0.5 text-sm text-gray-950 dark:text-white"><span class="truncate">{{ $row['k'] }}</span><span class="text-gray-500 dark:text-gray-400">{{ number_format($row['visits'] ?? $row['hits'] ?? 0) }}</span></p>
                        @empty
                            <p class="text-sm text-gray-400">None yet</p>
                        @endforelse
                    </div>
                @endforeach
            </div>
            <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">{{ number_format($traffic['leads']) }} leads came from search and AI in the last 30 days.</p>
        </x-filament::section>

        <x-filament::section heading="Index health" class="lg:col-span-2">
            <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-5">
                @foreach ([
                    ['Live pages and posts', $health['live'], url('/sitemap.xml'), 'Open the sitemap'],
                    ['Hidden from search (noindex)', $health['noindex'], \App\Filament\Admin\Resources\SeoEntryResource::getUrl(), 'SEO overrides'],
                    ['Pages with SEO overrides', $health['overrides'], \App\Filament\Admin\Resources\SeoEntryResource::getUrl(), 'SEO overrides'],
                    ['Missing meta description', $health['missingDesc'], $audit::getUrl(), 'Full audit'],
                    ['Redirects (times used)', $health['redirects'].' ('.number_format($health['redirectHits']).')', \App\Filament\Admin\Resources\RedirectResource::getUrl(), 'Redirects'],
                ] as [$label, $value, $url, $link])
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</p>
                        <p class="text-xl font-semibold text-gray-950 dark:text-white">{{ $value }}</p>
                        <a href="{{ $url }}" class="text-xs text-primary-600 hover:underline dark:text-primary-400">{{ $link }}</a>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
