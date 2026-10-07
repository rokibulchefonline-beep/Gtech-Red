<x-filament-panels::page>
    <style>
        @foreach ($palette as $i => [$light, $dark])
            .lm-c{{ $i }}{fill:{{ $light }}} .dark .lm-c{{ $i }}{fill:{{ $dark }}} .lm-k{{ $i }}{background:{{ $light }}} .dark .lm-k{{ $i }}{background:{{ $dark }}}
        @endforeach
        .lm-site{fill:#52525b} .dark .lm-site{fill:#a1a1aa}
        .lm-node{cursor:pointer;stroke:#fff;stroke-width:2} .dark .lm-node{stroke:#18181b}
        .lm-edge{fill:none;stroke:#a1a1aa;stroke-width:.8;opacity:.45} .dark .lm-edge{stroke:#71717a}
        .lm-edge.hot{stroke:#e8202f;stroke-width:2;opacity:.95} .lm-edge.dim{opacity:.08} .lm-g.dim{opacity:.25}
        .lm-label{font-size:10.5px;font-weight:600;fill:#18181b;paint-order:stroke;stroke:#fafafa;stroke-width:3px;pointer-events:none}
        .dark .lm-label{fill:#f4f4f5;stroke:#09090b}
    </style>
    <p class="-mt-4 text-sm text-gray-500 dark:text-gray-400">Built from the live content {{ \Illuminate\Support\Carbon::parse($builtAt)->diffForHumans() }} and rebuilt automatically whenever a page, post, case study, menu or the keyword map is saved, so new pages and links appear by themselves.
        @if ($broken) <b class="text-danger-600 dark:text-danger-400">{{ count($broken) }} broken {{ str('link')->plural(count($broken)) }}</b> (see SEO audit › Internal links).@endif</p>
    <div x-data="{ sel: null, types: ['content', 'semantic'], posts: @js($postCount <= 80), find: '', info: @js($info),
            on(t) { return this.types.includes(t) },
            hot(f, t) { return this.sel && (this.sel === f || this.sel === t) },
            shown(id) { return this.posts || ! this.info[id].post },
            matches() { const q = this.find.trim().toLowerCase(); return q.length < 2 ? [] : Object.entries(this.info).filter(([id, n]) => (n.label + ' ' + id).toLowerCase().includes(q)).slice(0, 8) },
            near(id) { return ! this.sel || this.sel === id || (this.info[this.sel].out.some(l => l[0] === id) || this.info[this.sel].in.some(l => l[0] === id)) } }"
        class="grid gap-6 xl:grid-cols-[1fr_320px]">
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="mb-3 flex flex-wrap gap-x-5 gap-y-2 text-sm text-gray-700 dark:text-gray-300">
                @foreach ($types as $k => $l)
                    <label class="flex items-center gap-1.5"><input type="checkbox" value="{{ $k }}" x-model="types" class="rounded border-gray-300 text-primary-600 dark:border-white/20 dark:bg-white/5"> {{ $l }}</label>
                @endforeach
                @if ($postCount)
                    <label class="flex items-center gap-1.5 font-medium"><input type="checkbox" x-model="posts" class="rounded border-gray-300 text-primary-600 dark:border-white/20 dark:bg-white/5"> Show blog posts and case studies ({{ number_format($postCount) }})</label>
                @endif
            </div>
            <svg viewBox="-470 -440 940 880" role="img" aria-label="Map of the internal links between the website's pages" class="h-auto w-full" style="max-height:78vh">
                @foreach ($edges as $e)
                    @php([$a, $b] = [$nodes[$e['from']], $nodes[$e['to']]])
                    <path d="M{{ $a['x'] }},{{ $a['y'] }} Q{{ round(($a['x'] + $b['x']) / 2 * .55, 1) }},{{ round(($a['y'] + $b['y']) / 2 * .55, 1) }} {{ $b['x'] }},{{ $b['y'] }}" class="lm-edge"
 x-show="on('{{ $e['type'] }}'){{ $e['post'] ? ' && posts' : '' }}" :class="{ hot: hot(@js($e['from']), @js($e['to'])), dim: sel && ! hot(@js($e['from']), @js($e['to'])) }"></path>
                @endforeach
                @foreach ($nodes as $n)
                    <g transform="translate({{ $n['x'] }},{{ $n['y'] }})" class="lm-g" :class="{ dim: ! near(@js($n['id'])) }" @if ($n['post']) x-show="posts" @endif
                        role="button" tabindex="0" aria-label="{{ $n['label'] }}" x-on:click="sel = sel === @js($n['id']) ? null : @js($n['id'])" x-on:keydown.enter="sel = sel === @js($n['id']) ? null : @js($n['id'])">
                        <circle r="{{ $n['r'] }}" class="lm-node {{ $n['c'] === null ? 'lm-site' : 'lm-c'.$n['c'] }}"><title>{{ $n['label'] }} · {{ $n['in'] }} links in, {{ $n['out'] }} out</title></circle>
                        <text y="{{ $n['r'] + 13 }}" text-anchor="middle" class="lm-label" @if ($n['r'] < 14) x-show="sel && near(@js($n['id']))" @endif>{{ \Illuminate\Support\Str::limit($n['label'], 22) }}</text>
                    </g>
                @endforeach
            </svg>
            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-600 dark:text-gray-400">
                @foreach ($clusters as $c => $label)
                    <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-full {{ $colour[$c] === null ? 'bg-gray-500' : 'lm-k'.$colour[$c] }}"></span>{{ $label }}</span>
                @endforeach
                <span>· Bigger dot = more links pointing in. Click a page to highlight its links.</span>
            </div>
        </div>

        <aside class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="mb-4">
                <x-filament::input.wrapper><x-filament::input type="search" x-model="find" placeholder="Find a page or post" /></x-filament::input.wrapper>
                <ul class="mt-1" x-show="matches().length">
                    <template x-for="[id, n] in matches()" :key="id">
                        <li><button type="button" class="w-full rounded px-2 py-1 text-left text-sm text-gray-800 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/5" x-on:click="if (n.post) posts = true; sel = id; find = ''" x-text="n.label"></button></li>
                    </template>
                </ul>
            </div>
            <template x-if="! sel"><p class="text-sm text-gray-500 dark:text-gray-400">Select a page on the map to see which pages link to it and where it links. Pages with few links in are hard for Google to find; give them more from related pages.</p></template>
            <template x-if="sel">
                <div>
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white" x-text="info[sel].label"></h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="sel"></p>
                    <div class="mt-2 flex gap-3 text-sm">
                        <a x-show="info[sel].edit" :href="info[sel].edit" class="text-primary-600 hover:underline dark:text-primary-400">Edit page</a>
                        <a :href="info[sel].view" target="_blank" class="text-primary-600 hover:underline dark:text-primary-400">View</a>
                    </div>
                    @foreach (['out' => 'Links out', 'in' => 'Links in'] as $k => $h)
                        <h4 class="mt-4 text-sm font-semibold text-gray-950 dark:text-white">{{ $h }} (<span x-text="info[sel].{{ $k }}.length"></span>)</h4>
                        <ul class="mt-1 max-h-64 space-y-1.5 overflow-y-auto">
                            <template x-for="l in info[sel].{{ $k }}" :key="l[0] + l[3]">
                                <li class="text-sm">
                                    <button type="button" class="text-left font-medium text-gray-900 hover:underline dark:text-gray-100" x-on:click="if (info[l[0]].post) posts = true; sel = l[0]" x-text="l[1]"></button>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400" x-text="'“' + l[2] + '” · ' + l[3]"></span>
                                </li>
                            </template>
                        </ul>
                    @endforeach
                </div>
            </template>
        </aside>
    </div>
</x-filament-panels::page>
