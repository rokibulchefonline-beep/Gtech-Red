{{-- SEO audit crawl tabs: Site health, Broken links, Inbound & outbound, External links. $crawl from SeoAudit::crawlData(). --}}
@php($run = $crawl['run'])
@php($running = \App\Support\Seo\Crawler::running())
@if ($running)
    <div wire:poll.3s class="flex items-center gap-3 rounded-xl bg-primary-50 p-4 text-sm text-primary-800 ring-1 ring-primary-200 dark:bg-primary-500/10 dark:text-primary-300 dark:ring-primary-500/30">
        <x-filament::loading-indicator class="h-5 w-5" />
        <span><b>Crawling the website…</b> {{ number_format($running->pages) }} pages so far. Started {{ \Illuminate\Support\Carbon::parse($running->started_at)->diffForHumans() }}. This page updates by itself.</span>
    </div>
@endif
@php($code = fn (int $s) => match (true) { $s === 0 => ['No reply', 'danger'], $s < 300 => [$s, 'success'], $s < 400 => [$s, 'warning'], default => [$s, 'danger'] })
@if (! $run && ! $running)
    <x-filament::section>
        <div class="py-8 text-center">
            <x-filament::icon icon="heroicon-o-bug-ant" class="mx-auto h-10 w-10 text-gray-400" />
            <p class="mt-3 font-medium">The site has not been crawled yet</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Click <b>Crawl the site now</b> above. It opens every page like Google does and checks every link and image. After that it runs every night at 02:30 and emails you new broken links.</p>
        </div>
    </x-filament::section>
@elseif ($run)
    @php($delta = fn (string $k) => $crawl['prev'] ? $run->{$k} - $crawl['prev']->{$k} : null)
    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
        @foreach ([['pages', 'Pages crawled', 'heroicon-o-document-text', 'gray', false], ['links', 'Links checked', 'heroicon-o-link', 'gray', false], ['broken', 'Broken links', 'heroicon-o-x-circle', 'danger', true],
                   ['errors', 'Error pages (4xx/5xx)', 'heroicon-o-exclamation-triangle', 'danger', true], ['redirects', 'Redirecting pages', 'heroicon-o-arrow-uturn-right', 'warning', true], ['external', 'External sites linked', 'heroicon-o-globe-alt', 'gray', false]] as [$k, $label, $ic, $bad, $lowerBetter])
            @php($d = $delta($k))
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"><x-filament::icon :icon="$ic" class="h-4 w-4" />{{ $label }}</div>
                <p @class(['mt-1 text-3xl font-semibold', 'text-danger-600 dark:text-danger-400' => $lowerBetter && $run->{$k} > 0 && $bad === 'danger', 'text-warning-600 dark:text-warning-400' => $lowerBetter && $run->{$k} > 0 && $bad === 'warning'])>{{ number_format($run->{$k}) }}</p>
                @if ($d !== null && $d !== 0)<p @class(['text-xs', 'text-danger-600' => $lowerBetter && $d > 0, 'text-success-600' => $lowerBetter && $d < 0, 'text-gray-500' => ! $lowerBetter])>{{ $d > 0 ? '+' : '' }}{{ number_format($d) }} since the last crawl</p>@endif
            </div>
        @endforeach
    </div>
    <p class="text-xs text-gray-500 dark:text-gray-400">Last crawl {{ \Illuminate\Support\Carbon::parse($run->finished_at)->diffForHumans() }} ({{ \Illuminate\Support\Carbon::parse($run->finished_at)->format('D j M, H:i') }}, {{ $run->trigger === 'nightly' ? 'nightly' : 'started by hand' }}, {{ $run->seconds }} s). The crawl runs every night at 02:30.</p>

    @if ($tab === 'health')
        <div class="grid gap-4 lg:grid-cols-3">
            {{-- Trend of the last crawls --}}
            <x-filament::section heading="Trend" description="Broken links and error pages over the last crawls" class="lg:col-span-1">
                @php($h = $crawl['history'])
                @php($max = max(1, (int) $h->max(fn ($r) => max($r->broken, $r->errors))))
                <div class="flex h-36 items-end gap-2" role="img" aria-label="Broken links and error pages per crawl">
                    @foreach ($h as $r)
                        <div class="flex h-full flex-1 flex-col justify-end gap-0.5" title="{{ \Illuminate\Support\Carbon::parse($r->finished_at)->format('j M') }}: {{ $r->broken }} broken, {{ $r->errors }} errors">
                            <div class="rounded-t bg-danger-500" style="height: {{ max(2, round($r->broken / $max * 100)) }}%"></div>
                            <div class="rounded-t bg-warning-400" style="height: {{ max(2, round($r->errors / $max * 100)) }}%"></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex gap-4 text-xs text-gray-500"><span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-sm bg-danger-500"></span>Broken links</span><span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-sm bg-warning-400"></span>Error pages</span></div>
            </x-filament::section>

            <x-filament::section heading="Page issues found by the crawl" class="lg:col-span-2">
                <ul class="divide-y divide-gray-100 text-sm dark:divide-white/10">
                    @foreach ($crawl['issues'] as $label => $paths)
                        <li class="py-2" x-data="{ o: false }">
                            <button type="button" class="flex w-full items-center gap-2 text-left" x-on:click="o = ! o">
                                <x-filament::icon :icon="$paths->isEmpty() ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-circle'" @class(['h-5 w-5', 'text-success-600' => $paths->isEmpty(), 'text-warning-600' => $paths->isNotEmpty()]) />
                                <span class="flex-1">{{ $label }}</span><x-filament::badge :color="$paths->isEmpty() ? 'success' : 'warning'">{{ $paths->count() }}</x-filament::badge>
                            </button>
                            @if ($paths->isNotEmpty())<p x-show="o" x-cloak class="mt-2 ps-7 text-xs text-gray-600 dark:text-gray-400">{{ $paths->take(30)->implode(' · ') }}{{ $paths->count() > 30 ? ' …' : '' }}</p>@endif
                        </li>
                    @endforeach
                    <li class="py-2" x-data="{ o: false }">
                        <button type="button" class="flex w-full items-center gap-2 text-left" x-on:click="o = ! o">
                            <x-filament::icon :icon="$crawl['dupTitles']->isEmpty() ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-circle'" @class(['h-5 w-5', 'text-success-600' => $crawl['dupTitles']->isEmpty(), 'text-warning-600' => $crawl['dupTitles']->isNotEmpty()]) />
                            <span class="flex-1">Duplicate titles</span><x-filament::badge :color="$crawl['dupTitles']->isEmpty() ? 'success' : 'warning'">{{ $crawl['dupTitles']->count() }}</x-filament::badge>
                        </button>
                        <ul x-show="o" x-cloak class="mt-2 ps-7 text-xs text-gray-600 dark:text-gray-400">@foreach ($crawl['dupTitles'] as $t)<li>{{ $t->n }}× “{{ $t->title }}”</li>@endforeach</ul>
                    </li>
                </ul>
            </x-filament::section>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <x-filament::section heading="Error pages" :description="$crawl['errorPages']->isEmpty() ? 'No page returned an error.' : 'Pages that returned an error code. Fix the page or add a redirect (SEO > Redirects).'">
                @if ($crawl['errorPages']->isNotEmpty())
                <table class="w-full text-sm"><thead><tr class="text-left text-gray-500"><th class="py-1">Page</th><th>Status</th><th>Found on</th></tr></thead><tbody class="divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($crawl['errorPages'] as $p)<tr><td class="py-1.5 font-medium">{{ $p->path }}</td><td><x-filament::badge color="danger">{{ $p->status }}</x-filament::badge></td><td class="text-xs text-gray-500">{{ $p->found_on ?: '-' }}</td></tr>@endforeach
                </tbody></table>
                @endif
            </x-filament::section>
            <x-filament::section heading="Redirects" :description="$crawl['redirectPages']->isEmpty() ? 'No internal link goes through a redirect.' : 'Links on your pages that redirect. Point them straight at the final address.'">
                @if ($crawl['redirectPages']->isNotEmpty())
                <table class="w-full text-sm"><thead><tr class="text-left text-gray-500"><th class="py-1">From</th><th>Status</th><th>To</th></tr></thead><tbody class="divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($crawl['redirectPages'] as $p)<tr><td class="py-1.5 font-medium">{{ $p->path }}</td><td><x-filament::badge color="warning">{{ $p->status }}</x-filament::badge></td><td class="break-all text-xs text-gray-500">{{ $p->redirect_to }}</td></tr>@endforeach
                </tbody></table>
                @endif
            </x-filament::section>
            <x-filament::section heading="Orphan pages" :description="$crawl['orphans']->isEmpty() ? 'Every page is linked from another page.' : 'Pages no other page links to (found only through the sitemap). Link to them from a related page.'">
                @if ($crawl['orphans']->isNotEmpty())<p class="text-sm">{{ $crawl['orphans']->pluck('path')->implode(' · ') }}</p>@endif
            </x-filament::section>
            <x-filament::section heading="Slowest pages" description="Time for the server to build the page (cached pages are faster for visitors).">
                <ul class="space-y-1.5 text-sm">
                    @php($slowMax = max(1, (int) $crawl['slow']->max('ms')))
                    @foreach ($crawl['slow'] as $p)
                        <li><div class="flex justify-between gap-2"><span class="truncate">{{ $p->path }}</span><span class="shrink-0 text-gray-500">{{ number_format($p->ms) }} ms</span></div>
                            <div class="mt-0.5 h-1.5 rounded-full bg-gray-100 dark:bg-white/10"><div @class(['h-1.5 rounded-full', 'bg-danger-500' => $p->ms > 1500, 'bg-warning-400' => $p->ms > 600 && $p->ms <= 1500, 'bg-success-500' => $p->ms <= 600]) style="width: {{ round($p->ms / $slowMax * 100) }}%"></div></div></li>
                    @endforeach
                </ul>
            </x-filament::section>
        </div>
    @else
        <div class="flex flex-wrap items-center gap-3">
            <x-filament::input.wrapper class="w-64"><x-filament::input type="search" wire:model.live.debounce.300ms="q" placeholder="Search addresses or link text" /></x-filament::input.wrapper>
            @if ($tab === 'broken')
                <x-filament::input.wrapper class="w-48"><x-filament::input.select wire:model.live="show">
                    <option value="all">All broken links</option><option value="internal">On this site</option><option value="external">To other sites</option><option value="images">Broken images</option>
                </x-filament::input.select></x-filament::input.wrapper>
            @elseif ($tab === 'external')
                <x-filament::input.wrapper class="w-48"><x-filament::input.select wire:model.live="show">
                    <option value="all">All external links</option><option value="broken">Only broken</option>
                </x-filament::input.select></x-filament::input.wrapper>
            @elseif ($tab === 'linking')
                <x-filament::input.wrapper class="w-56"><x-filament::input.select wire:model.live="sort">
                    <option value="total">Fewest inbound links first</option><option value="inbound">Most inbound links first</option><option value="out">Most internal links out</option><option value="ext">Most external links out</option><option value="name">A to Z</option>
                </x-filament::input.select></x-filament::input.wrapper>
            @endif
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ number_format($crawl['total']) }} {{ $tab === 'linking' ? 'pages' : 'links' }}</span>
        </div>

        @if ($tab === 'external' && $crawl['domains']->isNotEmpty())
            <x-filament::section heading="Sites you link to most" compact>
                @php($dmax = max(1, (int) $crawl['domains']->max()))
                <div class="grid gap-x-6 gap-y-1.5 text-sm sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($crawl['domains'] as $dom => $n)
                        <div><div class="flex justify-between"><span class="truncate">{{ $dom }}</span><span class="text-gray-500">{{ $n }}</span></div><div class="mt-0.5 h-1.5 rounded-full bg-gray-100 dark:bg-white/10"><div class="h-1.5 rounded-full bg-primary-500" style="width: {{ round($n / $dmax * 100) }}%"></div></div></div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <table class="w-full text-left text-sm">
                @if ($tab === 'broken')
                    <thead class="bg-gray-50 dark:bg-white/5"><tr><th class="px-4 py-3 font-semibold">Broken link</th><th class="px-3 py-3 font-semibold">Status</th><th class="px-4 py-3 font-semibold">On page</th><th class="hidden px-4 py-3 font-semibold md:table-cell">Link text</th></tr></thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                        @forelse ($crawl['list'] as $l)
                            @php([$t, $c] = $code((int) $l->status))
                            <tr><td class="max-w-md px-4 py-2.5"><p class="break-all font-medium">{{ $l->url }}</p><p class="text-xs text-gray-500">{{ $l->kind === 'image' ? 'Image' : 'Link' }} · {{ $l->internal ? 'this site' : 'other site' }}@if ($l->error) · {{ \Illuminate\Support\Str::limit($l->error, 80) }}@endif</p></td>
                                <td class="px-3 py-2.5"><x-filament::badge :color="$c">{{ $t }}</x-filament::badge></td>
                                <td class="px-4 py-2.5"><x-filament::link :href="\App\Filament\Support\SiteLink::to($l->from_path)" target="_blank">{{ $l->from_path }}</x-filament::link></td>
                                <td class="hidden px-4 py-2.5 text-gray-600 md:table-cell dark:text-gray-400">{{ \Illuminate\Support\Str::limit($l->anchor, 60) ?: '-' }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-10 text-center text-gray-500"><x-filament::icon icon="heroicon-o-check-badge" class="mx-auto mb-2 h-8 w-8 text-success-500" />No broken links found.</td></tr>
                        @endforelse
                    </tbody>
                @elseif ($tab === 'linking')
                    <thead class="bg-gray-50 dark:bg-white/5"><tr><th class="px-4 py-3 font-semibold">Page</th><th class="px-3 py-3 text-center font-semibold" title="Other pages that link here">Inbound</th><th class="px-3 py-3 text-center font-semibold" title="Links to other pages of this site">Internal out</th><th class="px-3 py-3 text-center font-semibold" title="Links to other websites">External out</th><th class="px-3 py-3 text-center font-semibold">Broken</th><th class="hidden px-3 py-3 text-center font-semibold md:table-cell">Depth</th></tr></thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                        @foreach ($crawl['list'] as $p)
                            <tr wire:key="lk-{{ $p->id }}" wire:click="toggle(@js($p->path))" class="cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-2.5"><p class="font-medium">{{ $p->path }}</p><p class="truncate text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($p->title, 80) }}</p></td>
                                <td class="px-3 py-2.5 text-center"><x-filament::badge :color="$p->inbound === 0 ? 'danger' : ($p->inbound < 3 ? 'warning' : 'success')">{{ $p->inbound }}</x-filament::badge></td>
                                <td class="px-3 py-2.5 text-center">{{ $p->out_internal }}</td><td class="px-3 py-2.5 text-center">{{ $p->out_external }}</td>
                                <td class="px-3 py-2.5 text-center">@if ($p->broken_links)<x-filament::badge color="danger">{{ $p->broken_links }}</x-filament::badge>@else<span class="text-gray-400">0</span>@endif</td>
                                <td class="hidden px-3 py-2.5 text-center text-gray-500 md:table-cell" title="Clicks from the home page">{{ $p->depth }}</td>
                            </tr>
                            @if ($open === $p->path)
                                <tr wire:key="lkd-{{ $p->id }}"><td colspan="6" class="bg-gray-50 px-4 py-4 dark:bg-white/5">
                                    <div class="grid gap-6 md:grid-cols-2">
                                        <div><p class="mb-2 font-semibold">Linked from {{ count($crawl['in'] ?? []) }} {{ str('page')->plural(count($crawl['in'] ?? [])) }}</p>
                                            <ul class="space-y-1 text-xs">@forelse ($crawl['in'] ?? [] as $i)<li><b>{{ $i->from_path }}</b> <span class="text-gray-500">“{{ \Illuminate\Support\Str::limit($i->anchor, 60) }}”</span></li>@empty<li class="text-danger-600">No other page links here.</li>@endforelse</ul></div>
                                        <div><p class="mb-2 font-semibold">Links out ({{ count($crawl['outLinks'] ?? []) }})</p>
                                            <ul class="space-y-1 text-xs">@foreach ($crawl['outLinks'] ?? [] as $o)<li class="flex gap-2">@php([$t, $c] = $code((int) $o->status))<x-filament::badge size="sm" :color="$o->ok ? ($o->internal ? 'gray' : 'info') : 'danger'">{{ $o->ok ? ($o->internal ? 'internal' : 'external') : $t }}</x-filament::badge><span class="break-all">{{ $o->url }}</span>@if ($o->nofollow)<span class="text-gray-400">nofollow</span>@endif</li>@endforeach</ul></div>
                                    </div>
                                </td></tr>
                            @endif
                        @endforeach
                    </tbody>
                @else
                    <thead class="bg-gray-50 dark:bg-white/5"><tr><th class="px-4 py-3 font-semibold">External link</th><th class="px-3 py-3 font-semibold">Status</th><th class="px-3 py-3 text-center font-semibold">On pages</th><th class="hidden px-4 py-3 font-semibold md:table-cell">Link text</th></tr></thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                        @forelse ($crawl['list'] as $l)
                            @php([$t, $c] = $code((int) $l->status))
                            <tr><td class="max-w-md px-4 py-2.5"><a class="break-all font-medium text-primary-600 hover:underline" href="{{ $l->url }}" target="_blank" rel="noopener noreferrer">{{ $l->url }}</a>@if ($l->nofollow)<span class="ms-1 text-xs text-gray-400">nofollow</span>@endif</td>
                                <td class="px-3 py-2.5"><x-filament::badge :color="$l->ok ? ($c === 'danger' ? 'gray' : $c) : 'danger'" :tooltip="$l->ok && $l->status >= 400 ? 'The site refuses automatic checks; open it to be sure.' : null">{{ $t }}</x-filament::badge></td>
                                <td class="px-3 py-2.5 text-center">{{ $l->pages }}</td>
                                <td class="hidden px-4 py-2.5 text-gray-600 md:table-cell dark:text-gray-400">{{ \Illuminate\Support\Str::limit($l->anchor, 60) ?: '-' }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-10 text-center text-gray-500">No external links.</td></tr>
                        @endforelse
                    </tbody>
                @endif
            </table>
        </div>

        @if ($crawl['pages'] > 1)
            <nav class="flex items-center justify-between gap-3 text-sm" aria-label="Pages">
                <span class="text-gray-500">Page {{ $this->p }} of {{ $crawl['pages'] }}</span>
                <div class="flex gap-1">
                    <x-filament::button size="sm" color="gray" wire:click="goTo({{ $this->p - 1 }})" :disabled="$this->p <= 1" icon="heroicon-m-chevron-left">Previous</x-filament::button>
                    <x-filament::button size="sm" color="gray" wire:click="goTo({{ $this->p + 1 }})" :disabled="$this->p >= $crawl['pages']" icon="heroicon-m-chevron-right" icon-position="after">Next</x-filament::button>
                </div>
            </nav>
        @endif
    @endif
@endif
