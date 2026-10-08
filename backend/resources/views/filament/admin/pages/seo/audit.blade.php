<x-filament-panels::page>
    @php($s = $a['summary'])
    @php($tone = fn ($n) => \App\Filament\Admin\Pages\SeoDashboard::tone($n))
    @php($icon = ['pass' => ['heroicon-m-check-circle', 'text-success-600 dark:text-success-400'], 'warn' => ['heroicon-m-exclamation-circle', 'text-warning-600 dark:text-warning-400'], 'fail' => ['heroicon-m-x-circle', 'text-danger-600 dark:text-danger-400']])

    @php($crawlTab = in_array($tab, ['health', 'broken', 'linking', 'external'], true))
    <div @class(['grid grid-cols-2 gap-4 lg:grid-cols-4', 'hidden' => $crawlTab])>
        @include('filament.admin.pages.seo.score', ['n' => $s['total'], 'label' => 'Overall', 'sub' => $s['pages'].' pages, '.$s['failing'].' failing checks'])
        @include('filament.admin.pages.seo.score', ['n' => $s['seo'], 'label' => 'SEO'])
        @include('filament.admin.pages.seo.score', ['n' => $s['aeo'], 'label' => 'AEO (answers)'])
        @include('filament.admin.pages.seo.score', ['n' => $s['geo'], 'label' => 'GEO (AI engines)'])
    </div>

    <x-filament::tabs>
        @foreach ($tabs as $k => $label)
            <x-filament::tabs.item :active="$tab === $k" wire:click="$set('tab', '{{ $k }}')">{{ $label }}</x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>

    @if ($crawlTab)
        @include('filament.admin.pages.seo.crawl')
    @endif

    @if (in_array($tab, ['pages', 'posts'], true))
        <div class="flex flex-wrap items-center gap-3">
            <x-filament::input.wrapper class="w-64"><x-filament::input type="search" wire:model.live.debounce.300ms="q" :placeholder="$tab === 'posts' ? 'Search posts or keywords' : 'Search pages or keywords'" /></x-filament::input.wrapper>
            @if ($tab === 'pages')
                <x-filament::input.wrapper class="w-44"><x-filament::input.select wire:model.live="kind">
                    <option value="">All pages</option><option value="service">Services</option><option value="industry">Industries</option><option value="landing">Landing pages</option>
                </x-filament::input.select></x-filament::input.wrapper>
            @endif
            <x-filament::input.wrapper class="w-52"><x-filament::input.select wire:model.live="sort">
                <option value="total">Lowest score first</option>
                @if ($tab === 'pages')<option value="seo">Lowest SEO first</option><option value="aeo">Lowest AEO first</option><option value="geo">Lowest GEO first</option>@endif
                <option value="name">A to Z</option>
            </x-filament::input.select></x-filament::input.wrapper>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ number_format($total) }} {{ $tab === 'posts' ? str('post')->plural($total) : str('page')->plural($total) }}. Click one to see every check and how to fix it.</span>
        </div>
    @endif

    @if ($tab === 'pages')
        <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-950 dark:bg-white/5 dark:text-white"><tr>
                    <th class="px-4 py-3 font-semibold">Page</th><th class="hidden px-4 py-3 font-semibold md:table-cell">Target keyword</th>
                    <th class="px-3 py-3 text-center font-semibold">SEO</th><th class="px-3 py-3 text-center font-semibold">AEO</th><th class="px-3 py-3 text-center font-semibold">GEO</th><th class="px-3 py-3 text-center font-semibold">Total</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                    @forelse ($rows as $p)
                        <tr wire:key="r-{{ $p['path'] }}" wire:click="toggle(@js($p['path']))" class="cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3"><p class="font-medium text-gray-950 dark:text-white">{{ $p['name'] }}</p><p class="text-xs text-gray-500 dark:text-gray-400">{{ $p['path'] }}</p></td>
                            <td class="hidden px-4 py-3 text-gray-700 md:table-cell dark:text-gray-300">{{ $p['keyword'] }}</td>
                            @foreach (['seo', 'aeo', 'geo', 'total'] as $k)
                                <td class="px-3 py-3 text-center"><x-filament::badge :color="$tone($p['scores'][$k])" class="inline-flex">{{ $p['scores'][$k] }}</x-filament::badge></td>
                            @endforeach
                        </tr>
                        @if ($open === $p['path'])
                            <tr wire:key="d-{{ $p['path'] }}"><td colspan="6" class="bg-gray-50 px-4 py-4 dark:bg-white/5">
                                <div class="mb-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-600 dark:text-gray-400">
                                    <span>{{ $p['stats']['words'] }} words</span><span>{{ $p['stats']['h2'] }} sections</span><span>{{ $p['stats']['faqs'] }} FAQs</span>
                                    <span>title {{ $p['stats']['titleLen'] }} chars</span><span>description {{ $p['stats']['descLen'] }} chars</span>
                                    <span>{{ $p['stats']['linksIn'] }} links in</span><span>{{ $p['stats']['linksOut'] }} links out</span>
                                    <x-filament::link :href="\App\Filament\Admin\Pages\SeoAudit::editUrl($p)" icon="heroicon-m-pencil-square">Edit this page</x-filament::link>
                                    <x-filament::link :href="\App\Filament\Support\SiteLink::to($p['path'])" target="_blank" icon="heroicon-m-arrow-top-right-on-square">View</x-filament::link>
                                </div>
                                <div class="grid gap-4 lg:grid-cols-3">
                                    @foreach (['SEO', 'AEO', 'GEO'] as $grp)
                                        <div>
                                            <h4 class="mb-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $grp }} · {{ $p['scores'][strtolower($grp)] }}</h4>
                                            <ul class="space-y-2">
                                                @foreach (collect($p['checks'])->where('group', $grp) as $c)
                                                    <li class="flex gap-2 text-sm">
                                                        <x-filament::icon :icon="$icon[$c['level']][0]" class="mt-0.5 h-4 w-4 shrink-0 {{ $icon[$c['level']][1] }}" />
                                                        <span class="text-gray-800 dark:text-gray-200">{{ $c['label'] }}
                                                            @if ($c['level'] !== 'pass' && ($c['detail'] || $c['fix']))
                                                                <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $c['detail'] }} @if ($c['fix'])<em>Fix: {{ $c['fix'] }}</em>@endif</span>
                                                            @endif
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach
                                </div>
                            </td></tr>
                        @endif
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No pages match.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif ($tab === 'posts')
        <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-950 dark:bg-white/5 dark:text-white"><tr>
                    <th class="px-4 py-3 font-semibold">Post</th><th class="hidden px-4 py-3 font-semibold md:table-cell">Focus keyword</th>
                    <th class="hidden px-3 py-3 text-center font-semibold sm:table-cell">Words</th><th class="px-3 py-3 text-center font-semibold">Score</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                    @forelse ($posts as $p)
                        <tr wire:key="pr-{{ $p['id'] }}" wire:click="toggle(@js($p['path']))" class="cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3"><p class="font-medium text-gray-950 dark:text-white">{{ $p['title'] }}</p><p class="text-xs text-gray-500 dark:text-gray-400">{{ $p['path'] }}</p></td>
                            <td class="hidden px-4 py-3 md:table-cell {{ $p['keyword'] ? 'text-gray-700 dark:text-gray-300' : 'text-danger-600 dark:text-danger-400' }}">{{ $p['keyword'] ?: 'Not set' }}</td>
                            <td class="hidden px-3 py-3 text-center text-gray-700 sm:table-cell dark:text-gray-300">{{ number_format($p['words']) }}</td>
                            <td class="px-3 py-3 text-center"><x-filament::badge class="inline-flex" :color="$tone($p['score'])">{{ $p['score'] }}</x-filament::badge></td>
                        </tr>
                        @if ($open === $p['path'])
                            <tr wire:key="pd-{{ $p['id'] }}"><td colspan="4" class="bg-gray-50 px-4 py-4 dark:bg-white/5">
                                <ul class="grid gap-x-6 gap-y-1.5 md:grid-cols-2">
                                    @foreach ($p['checks'] as $c)
                                        <li class="flex gap-2 text-sm">
                                            <x-filament::icon :icon="$icon[$c['level']][0]" class="mt-0.5 h-4 w-4 shrink-0 {{ $icon[$c['level']][1] }}" />
                                            <span class="text-gray-800 dark:text-gray-200">{{ $c['label'] }}@if ($c['level'] !== 'pass' && $c['hint'])<span class="block text-xs text-gray-500 dark:text-gray-400">{{ $c['hint'] }}</span>@endif</span>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="mt-3 flex gap-4">
                                    <x-filament::link :href="\App\Filament\Admin\Resources\PostResource::getUrl('edit', ['record' => $p['id']])" icon="heroicon-m-pencil-square">Edit this post</x-filament::link>
                                    <x-filament::link :href="\App\Filament\Support\SiteLink::to($p['path'])" target="_blank" icon="heroicon-m-arrow-top-right-on-square">View</x-filament::link>
                                </div>
                            </td></tr>
                        @endif
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">No posts match.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif ($tab === 'keywords')
        @if ($a['conflicts'])
            <div class="rounded-xl bg-warning-50 p-4 text-sm text-warning-800 ring-1 ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-300">
                <b>Keyword conflicts</b> (two pages competing for the same phrase):
                @foreach ($a['conflicts'] as $c) <span class="block">“{{ $c['keyword'] }}” → {{ implode(', ', $c['paths']) }}</span> @endforeach
            </div>
        @endif
        <p class="text-sm text-gray-500 dark:text-gray-400">Each page targets one primary keyword, supports it with related phrases and names the entities people and AI systems associate with it. Change them in <x-filament::link :href="\App\Filament\Admin\Resources\SeoKeywordResource::getUrl()">SEO › Keyword map</x-filament::link>; a page's own focus keyword (SEO overrides) wins.</p>
        <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-950 dark:bg-white/5 dark:text-white"><tr><th class="px-4 py-3">Page</th><th class="px-4 py-3">Primary</th><th class="px-4 py-3">Related keywords</th><th class="px-4 py-3">Entities</th></tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($a['pages'] as $p)
                        @php($m = $map[last(explode('/', $p['path']))] ?? null)
                        <tr class="align-top">
                            <td class="px-4 py-3"><a class="font-medium text-gray-950 hover:underline dark:text-white" href="{{ $m ? \App\Filament\Admin\Resources\SeoKeywordResource::getUrl('edit', ['record' => $m->slug]) : \App\Filament\Admin\Pages\SeoAudit::editUrl($p) }}">{{ $p['name'] }}</a></td>
                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $p['keyword'] }}</td>
                            <td class="px-4 py-3"><div class="flex flex-wrap gap-1">@foreach ((array) ($m?->sec ?? []) as $x)<x-filament::badge size="sm" color="gray">{{ $x }}</x-filament::badge>@endforeach</div></td>
                            <td class="px-4 py-3"><div class="flex flex-wrap gap-1">@foreach ((array) ($m?->ent ?? []) as $x)<x-filament::badge size="sm" color="info">{{ $x }}</x-filament::badge>@endforeach</div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif ($tab === 'links')
        @foreach ([['Orphan pages', 'No page body links to these, so search engines see them as unimportant.', $a['orphans'], 'danger'], ['Broken semantic links', 'Keyword map links to pages that do not exist or are not published.', $a['broken'], 'danger']] as [$h, $d, $list, $c])
            <div class="rounded-xl p-4 text-sm ring-1 {{ $list ? 'bg-danger-50 text-danger-800 ring-danger-600/20 dark:bg-danger-400/10 dark:text-danger-300' : 'bg-success-50 text-success-800 ring-success-600/20 dark:bg-success-400/10 dark:text-success-300' }}">
                <b>{{ $h }}: {{ count($list) }}</b> · {{ $d }}
                @foreach ($list as $x)<span class="block">{{ $x }}</span>@endforeach
            </div>
        @endforeach
        <p class="text-sm text-gray-500 dark:text-gray-400">Contextual links are the ones in a page's body (related services, industries, semantic links, category lists). Menus, the footer and breadcrumbs are left out because they pass little topical relevance. Aim for 4+ links in. See them drawn on the <x-filament::link :href="\App\Filament\Admin\Pages\LinkMap::getUrl()">link map</x-filament::link>.</p>
        <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-950 dark:bg-white/5 dark:text-white"><tr><th class="px-4 py-3">Page</th><th class="px-4 py-3 text-center">Links in (body)</th><th class="px-4 py-3 text-center">Links out (body)</th><th class="hidden px-4 py-3 text-center md:table-cell">All links in</th></tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($linkRows as $n)
                        @php($st = $stats[$n['id']])
                        <tr>
                            <td class="px-4 py-2.5"><p class="font-medium text-gray-950 dark:text-white">{{ $n['label'] }}</p><p class="text-xs text-gray-500 dark:text-gray-400">{{ $n['id'] }}</p></td>
                            <td class="px-4 py-2.5 text-center"><x-filament::badge class="inline-flex" :color="$st['in'] >= 4 ? 'success' : ($st['in'] >= 1 ? 'warning' : 'danger')">{{ $st['in'] }}</x-filament::badge></td>
                            <td class="px-4 py-2.5 text-center text-gray-800 dark:text-gray-200">{{ $st['out'] }}</td>
                            <td class="hidden px-4 py-2.5 text-center text-gray-500 md:table-cell dark:text-gray-400">{{ $st['allIn'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (in_array($tab, ['pages', 'posts'], true) && $pages > 1)
        <nav class="flex flex-wrap items-center justify-between gap-3 text-sm" aria-label="Pages">
            <span class="text-gray-500 dark:text-gray-400">{{ ($this->p - 1) * $perPage + 1 }}–{{ min($this->p * $perPage, $total) }} of {{ number_format($total) }}</span>
            <div class="flex flex-wrap items-center gap-1">
                <x-filament::button size="sm" color="gray" wire:click="goTo({{ $this->p - 1 }})" :disabled="$this->p <= 1" icon="heroicon-m-chevron-left">Previous</x-filament::button>
                @foreach (collect(range(1, $pages))->filter(fn ($n) => $n === 1 || $n === $pages || abs($n - $this->p) <= 2) as $n)
                    @if (! $loop->first && $n - $prevN > 1)<span class="px-1 text-gray-400">…</span>@endif
                    <x-filament::button size="sm" :color="$n === $this->p ? 'primary' : 'gray'" wire:click="goTo({{ $n }})">{{ $n }}</x-filament::button>
                    @php($prevN = $n)
                @endforeach
                <x-filament::button size="sm" color="gray" wire:click="goTo({{ $this->p + 1 }})" :disabled="$this->p >= $pages" icon="heroicon-m-chevron-right" icon-position="after">Next</x-filament::button>
            </div>
        </nav>
    @endif
</x-filament-panels::page>
