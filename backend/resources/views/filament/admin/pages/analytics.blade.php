<x-filament-panels::page>
@php
    $fmtTime = fn ($s) => $s >= 60 ? floor($s / 60).'m '.str_pad((string) ($s % 60), 2, '0', STR_PAD_LEFT).'s' : ((int) $s).'s';
    $C = \App\Filament\Admin\Pages\Analytics::class;
    $chName = fn ($c) => $c === 'AI' ? 'AI assistants' : $c;
    $order = array_keys($colours);
    $points = $daily['points'];
    $labels = array_keys($points);
    $fmtDay = fn ($k) => \Illuminate\Support\Carbon::parse($daily['monthly'] ? $k.'-01' : $k)->format($daily['monthly'] ? 'M Y' : 'D j M');
    $spark = function (array $vals, int $w = 120, int $h = 32) {
        $n = count($vals); $max = max(1, max($vals ?: [0]));
        if ($n < 2) return '';
        $pts = collect($vals)->values()->map(fn ($v, $i) => round($i / ($n - 1) * $w, 1).','.round($h - 2 - $v / $max * ($h - 4), 1))->implode(' ');
        return $pts;
    };
@endphp
<style>
    .an { --s-Search:{{ $colours['Search'][0] }}; --s-AI:{{ $colours['AI'][0] }}; --s-Direct:{{ $colours['Direct'][0] }}; --s-Social:{{ $colours['Social'][0] }}; --s-Referral:{{ $colours['Referral'][0] }}; --s-Paid:{{ $colours['Paid'][0] }}; --s-Email:{{ $colours['Email'][0] }}; --s-Campaign:{{ $colours['Campaign'][0] }};
          --c1:{{ $colours['Search'][0] }}; --c2:{{ $colours['AI'][0] }}; --c3:{{ $colours['Direct'][0] }}; --c4:{{ $colours['Social'][0] }}; --surface:#fff; --grid:rgb(229 231 235); --ink2:rgb(107 114 128); --prev:rgb(156 163 175); }
    .dark .an { --s-Search:{{ $colours['Search'][1] }}; --s-AI:{{ $colours['AI'][1] }}; --s-Direct:{{ $colours['Direct'][1] }}; --s-Social:{{ $colours['Social'][1] }}; --s-Referral:{{ $colours['Referral'][1] }}; --s-Paid:{{ $colours['Paid'][1] }}; --s-Email:{{ $colours['Email'][1] }}; --s-Campaign:{{ $colours['Campaign'][1] }};
          --c1:{{ $colours['Search'][1] }}; --c2:{{ $colours['AI'][1] }}; --c3:{{ $colours['Direct'][1] }}; --c4:{{ $colours['Social'][1] }}; --surface:rgb(24 24 27); --grid:rgba(255,255,255,.08); --ink2:rgb(156 163 175); --prev:rgb(107 114 128); }
    .an-card { background: var(--surface); }
    .an-bar:hover { opacity: .85 }
</style>

<div class="an space-y-6">
{{-- Toolbar --}}
<div class="flex flex-wrap items-center gap-2">
    <div class="inline-flex overflow-hidden rounded-lg ring-1 ring-gray-950/10 dark:ring-white/10" role="group" aria-label="Period">
        @foreach (['1' => 'Today', '7' => '7 days', '30' => '30 days', '90' => '90 days', '365' => '12 months'] as $v => $l)
            <button type="button" wire:click="$set('period', '{{ $v }}')" @class(['px-3 py-1.5 text-sm font-medium', 'bg-primary-600 text-white' => $period === (string) $v, 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/5' => $period !== (string) $v])>{{ $l }}</button>
        @endforeach
    </div>
    <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-700 dark:text-gray-300"><x-filament::input.checkbox wire:model.live="compare" /> Compare with the previous period</label>
    @foreach (['channel' => 'Channel', 'source' => 'Source', 'page' => 'Page'] as $key => $label)
        @if ($this->{$key} !== '')
            <x-filament::badge color="primary" class="cursor-pointer" icon="heroicon-m-x-mark" icon-position="after" wire:click="filter('{{ $key }}', {{ \Illuminate\Support\Js::from($this->{$key}) }})">{{ $label }}: {{ $key === 'channel' ? $chName($this->{$key}) : $this->{$key} }}</x-filament::badge>
        @endif
    @endforeach
    @if ($channel !== '' || $source !== '' || $page !== '')<x-filament::link tag="button" size="sm" wire:click="clear">Clear filters</x-filament::link>@endif
    <span class="ms-auto text-xs text-gray-500 dark:text-gray-400">{{ $r->from->format('j M Y') }} – {{ $r->to->format('j M Y') }} · no cookies · your own visits excluded</span>
</div>

{{-- Live + headline numbers --}}
<div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_300px]">
    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        @php
            $kpis = [
                ['Visitors', $totals['visitors'], $before['visitors'], number_format($totals['visitors']), 'people', true],
                ['Visits', $totals['visits'], $before['visits'], number_format($totals['visits']), 'visits', true],
                ['Page views', $totals['views'], $before['views'], number_format($totals['views']), 'views', true],
                ['Leads', $totals['leads'], $before['leads'], number_format($totals['leads']), 'leads', true],
                ['Conversion', $totals['conversion'], $before['conversion'], $totals['conversion'].'%', null, true],
                ['Avg visit', $totals['avg_time'], $before['avg_time'], $fmtTime($totals['avg_time']), null, true],
                ['Bounce rate', $totals['bounce'], $before['bounce'], $totals['bounce'].'%', null, false],
                ['From AI', $totals['ai'], $before['ai'], number_format($totals['ai']), null, true],
            ];
        @endphp
        @foreach ($kpis as [$label, $now, $was, $shown, $sparkKey, $upIsGood])
            @php $ch = $C::change($now, $was); @endphp
            <div class="an-card rounded-xl p-4 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
                    @if ($compare && $ch !== null && $ch != 0)
                        @php $good = $upIsGood ? $ch > 0 : $ch < 0; @endphp
                        <span @class(['inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[11px] font-semibold tabular-nums', 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400' => $good, 'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-400' => ! $good])
                            title="Previous period: {{ is_float($was) ? $was : number_format($was) }}">
                            <x-filament::icon :icon="$ch > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down'" class="h-3 w-3" />{{ abs($ch) }}%
                        </span>
                    @elseif ($compare && $ch === null)
                        <span class="rounded-full bg-gray-100 px-1.5 py-0.5 text-[11px] font-medium text-gray-600 dark:bg-white/5 dark:text-gray-400">new</span>
                    @endif
                </div>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-gray-950 dark:text-white">{{ $shown }}</p>
                @if ($sparkKey && count($points) > 1)
                    <svg viewBox="0 0 120 32" class="mt-2 h-8 w-full" preserveAspectRatio="none" aria-hidden="true"><polyline points="{{ $spark(array_column($points, $sparkKey)) }}" fill="none" stroke="var(--c1)" stroke-width="2" vector-effect="non-scaling-stroke" stroke-linejoin="round" stroke-linecap="round"/></svg>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Real time --}}
    <div wire:poll.30s.visible class="an-card rounded-xl p-4 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
        <div class="flex items-center gap-2">
            <span class="relative flex h-2.5 w-2.5"><span @class(['absolute inline-flex h-full w-full rounded-full opacity-75', 'animate-ping bg-success-400' => $live['active'] > 0])></span><span @class(['relative inline-flex h-2.5 w-2.5 rounded-full', 'bg-success-500' => $live['active'] > 0, 'bg-gray-300' => $live['active'] === 0])></span></span>
            <p class="text-sm font-medium">Right now</p>
        </div>
        <p class="mt-1 text-3xl font-semibold tabular-nums">{{ $live['active'] }} <span class="text-sm font-normal text-gray-500">{{ str('visitor')->plural($live['active']) }} in the last 5 minutes</span></p>
        @if ($live['pages']->isNotEmpty())
            <ul class="mt-2 space-y-1 text-xs">@foreach ($live['pages'] as $lp)<li class="flex justify-between gap-2"><span class="truncate">{{ $lp->k }}</span><span class="tabular-nums text-gray-500">{{ $lp->n }}</span></li>@endforeach</ul>
        @endif
        <p class="mt-3 text-xs font-medium text-gray-500">Latest page views</p>
        <ul class="mt-1 space-y-1.5 text-xs">
            @forelse ($live['recent'] as $v)
                <li class="flex items-center gap-2"><span class="h-2 w-2 shrink-0 rounded-full" style="background: var(--s-{{ $v->channel ?: 'Direct' }})" title="{{ $chName($v->channel) }}"></span><span class="min-w-0 flex-1 truncate">{{ $v->path }}</span><span class="shrink-0 text-gray-500">{{ \Illuminate\Support\Carbon::parse($v->viewed_at)->diffForHumans(short: true) }}</span></li>
            @empty
                <li class="text-gray-500">No visits yet.</li>
            @endforelse
        </ul>
    </div>
</div>

{{-- Trend chart --}}
@php
    $vals = array_column($points, $metric);
    $prevVals = $prevDaily ? array_values(array_column($prevDaily['points'], $metric)) : [];
    $n = count($vals); $W = 1000; $H = 240; $L = 40; $T = 12; $B = 26;
    $max = max(1, max($vals ?: [0]), $prevVals ? max($prevVals) : 0);
    $step = $max <= 5 ? 1 : (function ($m) { $raw = $m / 4; $p = 10 ** floor(log10($raw)); return (int) (ceil($raw / $p) * $p); })($max);
    $top = max($step, (int) ceil($max / $step) * $step);
    $x = fn ($i) => $n > 1 ? $L + $i / ($n - 1) * ($W - $L - 8) : $L + ($W - $L) / 2;
    $y = fn ($v) => $T + ($H - $T - $B) * (1 - $v / $top);
    $line = collect($vals)->values()->map(fn ($v, $i) => round($x($i), 1).','.round($y($v), 1))->implode(' ');
    $prevLine = collect(array_slice($prevVals, 0, $n))->map(fn ($v, $i) => round($x($i), 1).','.round($y($v), 1))->implode(' ');
    $area = $n > 1 ? 'M'.round($x(0), 1).','.($H - $B).' L'.str_replace(' ', ' L', $line).' L'.round($x($n - 1), 1).','.($H - $B).' Z' : '';
    $tipData = collect($points)->map(fn ($m, $k) => ['d' => $fmtDay($k), 'v' => $m[$metric]])->values()->all();
    $prevTip = $prevDaily ? collect($prevDaily['points'])->map(fn ($m, $k) => ['d' => $fmtDay($k), 'v' => $m[$metric]])->values()->all() : [];
@endphp
<x-filament::section>
    <x-slot name="heading">
        <div class="flex flex-wrap items-center gap-3">
            <span>Trend</span>
            <div class="inline-flex overflow-hidden rounded-lg text-xs ring-1 ring-gray-950/10 dark:ring-white/10" role="group" aria-label="Metric">
                @foreach (\App\Filament\Admin\Pages\Analytics::METRICS as $k => $l)
                    <button type="button" wire:click="$set('metric', '{{ $k }}')" @class(['px-2.5 py-1 font-medium', 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' => $metric === $k && ! $stacked, 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5' => $metric !== $k || $stacked])>{{ $l }}</button>
                @endforeach
                <button type="button" wire:click="$toggle('stacked')" @class(['border-s border-gray-950/10 px-2.5 py-1 font-medium dark:border-white/10', 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' => $stacked, 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5' => ! $stacked])>Visits by channel</button>
            </div>
        </div>
    </x-slot>
    @if (! $stacked)
        <div class="relative" x-data="{ i: null, pts: @js($tipData), prev: @js($prevTip), n: {{ $n }},
                move(e) { const r = this.$refs.svg.getBoundingClientRect(); const px = (e.clientX - r.left) / r.width * {{ $W }}; this.i = this.n > 1 ? Math.max(0, Math.min(this.n - 1, Math.round((px - {{ $L }}) / ({{ $W - $L - 8 }}) * (this.n - 1)))) : 0; } }"
             x-on:mouseleave="i = null">
            <div class="mb-2 flex flex-wrap gap-4 text-xs text-gray-600 dark:text-gray-300">
                <span class="inline-flex items-center gap-1.5"><span class="h-0.5 w-4 rounded" style="background: var(--c1)"></span>{{ \App\Filament\Admin\Pages\Analytics::METRICS[$metric] }}, this period</span>
                @if ($prevDaily)<span class="inline-flex items-center gap-1.5"><span class="h-0 w-4 border-t-2 border-dashed" style="border-color: var(--prev)"></span>Previous period</span>@endif
            </div>
            <svg x-ref="svg" viewBox="0 0 {{ $W }} {{ $H }}" class="h-auto w-full" role="img" aria-label="{{ \App\Filament\Admin\Pages\Analytics::METRICS[$metric] }} per {{ $daily['monthly'] ? 'month' : 'day' }}" x-on:mousemove="move($event)">
                <defs><linearGradient id="an-fill" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="var(--c1)" stop-opacity=".22"/><stop offset="1" stop-color="var(--c1)" stop-opacity="0"/></linearGradient></defs>
                @for ($t = 0; $t <= $top; $t += $step)
                    <line x1="{{ $L }}" x2="{{ $W }}" y1="{{ $y($t) }}" y2="{{ $y($t) }}" stroke="var(--grid)" stroke-width="1"/>
                    <text x="{{ $L - 8 }}" y="{{ $y($t) + 4 }}" text-anchor="end" fill="var(--ink2)" font-size="11">{{ number_format($t) }}</text>
                @endfor
                @foreach ($labels as $i => $k)
                    @if ($n <= 12 || $i % (int) ceil($n / 8) === 0 || $i === $n - 1)
                        <text x="{{ $x($i) }}" y="{{ $H - 6 }}" text-anchor="middle" fill="var(--ink2)" font-size="11">{{ \Illuminate\Support\Carbon::parse($daily['monthly'] ? $k.'-01' : $k)->format($daily['monthly'] ? 'M' : 'j M') }}</text>
                    @endif
                @endforeach
                @if ($prevLine)<polyline points="{{ $prevLine }}" fill="none" stroke="var(--prev)" stroke-width="2" stroke-dasharray="5 4" vector-effect="non-scaling-stroke"/>@endif
                @if ($area)<path d="{{ $area }}" fill="url(#an-fill)"/>@endif
                <polyline points="{{ $line }}" fill="none" stroke="var(--c1)" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke"/>
                @if ($n === 1)<circle cx="{{ $x(0) }}" cy="{{ $y($vals[0] ?? 0) }}" r="5" fill="var(--c1)"/>@endif
                <template x-if="i !== null">
                    <g>
                        <line :x1="{{ $L }} + (n > 1 ? i / (n - 1) : .5) * {{ $W - $L - 8 }}" :x2="{{ $L }} + (n > 1 ? i / (n - 1) : .5) * {{ $W - $L - 8 }}" y1="{{ $T }}" y2="{{ $H - $B }}" stroke="var(--ink2)" stroke-width="1" stroke-dasharray="3 3"/>
                        <circle :cx="{{ $L }} + (n > 1 ? i / (n - 1) : .5) * {{ $W - $L - 8 }}" :cy="{{ $T }} + {{ $H - $T - $B }} * (1 - pts[i].v / {{ $top }})" r="5" fill="var(--c1)" stroke="var(--surface)" stroke-width="2"/>
                    </g>
                </template>
                <rect x="{{ $L }}" y="0" width="{{ $W - $L }}" height="{{ $H - $B }}" fill="transparent"/>
            </svg>
            <div x-show="i !== null" x-cloak class="pointer-events-none absolute top-6 z-10 rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-lg dark:bg-white dark:text-gray-900"
                 :style="'left:' + Math.min(85, Math.max(5, (n > 1 ? i / (n - 1) : .5) * 92 + 4)) + '%; transform: translateX(-50%)'">
                <p class="font-semibold" x-text="pts[i]?.d"></p>
                <p><span x-text="(pts[i]?.v ?? 0).toLocaleString()"></span> {{ strtolower(\App\Filament\Admin\Pages\Analytics::METRICS[$metric]) }}</p>
                <p x-show="prev[i]" class="opacity-70">Previous: <span x-text="(prev[i]?.v ?? 0).toLocaleString()"></span> <span x-text="'(' + (prev[i]?.d ?? '') + ')'"></span></p>
            </div>
        </div>
    @else
        @php
            $buckets = $series['buckets'];
            $smax = max(1, collect($buckets)->map(fn ($b) => array_sum($b))->max());
            $sn = count($buckets); $SH = 220; $pad = 28; $gap = $sn > 60 ? 1 : 3; $bw = max(2, ($W - $pad) / max(1, $sn) - $gap);
            $tick = $smax <= 5 ? 1 : (int) ceil($smax / 4 / (10 ** floor(log10(max(1, $smax / 4))))) * (10 ** floor(log10(max(1, $smax / 4))));
        @endphp
        <div class="mb-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-600 dark:text-gray-300" aria-label="Legend">
            @foreach (\App\Support\Analytics\Classifier::CHANNELS as $c)
                <button type="button" wire:click="filter('channel', '{{ $c }}')" class="inline-flex items-center gap-1.5 {{ $channel !== '' && $channel !== $c ? 'opacity-40' : '' }}"><span class="inline-block h-2.5 w-2.5 rounded-sm" style="background: var(--s-{{ $c }})"></span>{{ $chName($c) }}</button>
            @endforeach
        </div>
        <svg viewBox="0 0 {{ $W }} {{ $SH + 22 }}" class="h-auto w-full" role="img" aria-label="Visits per {{ $series['monthly'] ? 'month' : 'day' }} by channel">
            @for ($t = 0; $t <= $smax; $t += $tick)
                @php $yy = $SH - $t / $smax * ($SH - 10); @endphp
                <line x1="{{ $pad }}" x2="{{ $W }}" y1="{{ $yy }}" y2="{{ $yy }}" stroke="var(--grid)" stroke-width="1"/>
                <text x="{{ $pad - 6 }}" y="{{ $yy + 4 }}" text-anchor="end" fill="var(--ink2)" font-size="11">{{ $t }}</text>
            @endfor
            @php $i = 0; @endphp
            @foreach ($buckets as $label => $b)
                @php $bx = $pad + $i * ($bw + $gap) + $gap / 2; @endphp @php $by = $SH; @endphp
                <g>
                    <title>{{ \Illuminate\Support\Carbon::parse($series['monthly'] ? $label.'-01' : $label)->format($series['monthly'] ? 'M Y' : 'D j M') }}: {{ array_sum($b) }} visits&#10;{{ collect($b)->filter()->map(fn ($v, $k) => $chName($k).': '.$v)->implode("\n") }}</title>
                    <rect x="{{ $bx }}" y="10" width="{{ $bw }}" height="{{ $SH - 10 }}" fill="transparent"/>
                    @foreach ($b as $chn => $v)
                        @continue(! $v)
                        @php $h = $v / $smax * ($SH - 10); @endphp
                        <rect class="an-bar" x="{{ $bx }}" y="{{ $by - $h }}" width="{{ $bw }}" height="{{ max(1, $h - 2) }}" rx="{{ $bw > 8 ? 2 : 0 }}" style="fill: var(--s-{{ $chn }})" />
                        @php $by -= $h; @endphp
                    @endforeach
                </g>
                @if ($sn <= 14 || $i % (int) ceil($sn / 10) === 0)
                    <text x="{{ $bx + $bw / 2 }}" y="{{ $SH + 16 }}" text-anchor="middle" fill="var(--ink2)" font-size="11">{{ \Illuminate\Support\Carbon::parse($series['monthly'] ? $label.'-01' : $label)->format($series['monthly'] ? 'M' : 'j M') }}</text>
                @endif
                @php $i++; @endphp
            @endforeach
        </svg>
    @endif
</x-filament::section>

{{-- Channels, devices, visitors, funnel --}}
<div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-4">
    @php
        $donut = function (array $parts) {
            $total = array_sum(array_column($parts, 1)) ?: 1; $circ = 2 * M_PI * 40; $off = 0; $out = [];
            foreach ($parts as [$label, $v, $color]) { $len = $v / $total * $circ; $out[] = [$label, $v, $color, max(0, $len - (count($parts) > 1 ? 2 : 0)), $off, round($v / $total * 100)]; $off += $len; }
            return [$out, $circ];
        };
    @endphp
    @foreach ([
        ['Channels', collect($channels)->sortBy(fn ($c) => array_search($c['k'], $order))->map(fn ($c) => [$chName($c['k']), (int) $c['visits'], 'var(--s-'.$c['k'].')', $c['k']])->values()->all(), 'channel'],
        ['Devices', collect($devices)->values()->map(fn ($d, $i) => [ucfirst($d['k'] ?: 'Unknown'), (int) $d['visits'], 'var(--c'.($i + 1).')', null])->all(), null],
        ['New and returning', [['New visitors', $newReturning['new'], 'var(--c1)', null], ['Returning', $newReturning['returning'], 'var(--c3)', null]], null],
    ] as [$title, $parts, $fkey])
        @php [$segs, $circ] = $donut(array_map(fn ($p) => array_slice($p, 0, 3), $parts)); @endphp
        <x-filament::section :heading="$title" compact>
            @if (array_sum(array_column($parts, 1)) === 0)
                <p class="py-8 text-center text-sm text-gray-500">No visits in this period.</p>
            @else
            <div class="flex items-center gap-4">
                <svg viewBox="0 0 100 100" class="h-24 w-24 shrink-0 -rotate-90" role="img" aria-label="{{ $title }}">
                    <circle cx="50" cy="50" r="40" fill="none" stroke="var(--grid)" stroke-width="14"/>
                    @foreach ($segs as [$label, $v, $color, $len, $off, $pct])
                        <circle cx="50" cy="50" r="40" fill="none" stroke="{{ $color }}" stroke-width="14" stroke-dasharray="{{ $len }} {{ $circ - $len }}" stroke-dashoffset="{{ -$off }}"><title>{{ $label }}: {{ number_format($v) }} ({{ $pct }}%)</title></circle>
                    @endforeach
                </svg>
                <ul class="min-w-0 flex-1 space-y-1.5 text-xs">
                    @foreach ($segs as $idx => [$label, $v, $color, $len, $off, $pct])
                        <li class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-sm" style="background: {{ $color }}"></span>
                            @if ($fkey && ($parts[$idx][3] ?? null))<button type="button" class="min-w-0 flex-1 text-left leading-tight hover:underline" wire:click="filter('{{ $fkey }}', '{{ $parts[$idx][3] }}')">{{ $label }}</button>@else<span class="min-w-0 flex-1 leading-tight">{{ $label }}</span>@endif
                            <span class="tabular-nums text-gray-500">{{ $pct }}%</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </x-filament::section>
    @endforeach

    <x-filament::section heading="Conversion funnel" compact>
        @php $f0 = max(1, $funnel[0][1]); @endphp
        <ol class="space-y-2.5 text-xs">
            @foreach ($funnel as $k => [$label, $v])
                <li>
                    <div class="flex justify-between gap-2"><span>{{ $label }}</span><span class="tabular-nums"><b>{{ number_format($v) }}</b> <span class="text-gray-500">{{ $k ? round($v / $f0 * 100, 1).'%' : '' }}</span></span></div>
                    <div class="mt-1 h-2.5 rounded-full bg-gray-100 dark:bg-white/10"><div class="h-2.5 rounded-full" style="width: {{ max(1, round($v / $f0 * 100)) }}%; background: var(--c1); opacity: {{ 1 - $k * 0.18 }}"></div></div>
                </li>
            @endforeach
        </ol>
    </x-filament::section>
</div>

{{-- When people visit --}}
@php $hmax = max(1, max(array_map('max', $heatmap))); @endphp
<x-filament::section heading="When people visit" description="Visits by day and hour (UK time). Darker = busier." compact>
    <div class="overflow-x-auto">
        <div class="min-w-[640px]">
            <div class="grid grid-cols-[40px_repeat(24,minmax(0,1fr))] gap-[2px] text-[10px] text-gray-500">
                <span></span>@for ($h = 0; $h < 24; $h++)<span class="text-center">{{ $h % 3 === 0 ? str_pad((string) $h, 2, '0', STR_PAD_LEFT) : '' }}</span>@endfor
                @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $d => $dn)
                    <span class="flex items-center">{{ $dn }}</span>
                    @for ($h = 0; $h < 24; $h++)
                        @php $v = $heatmap[$d][$h]; @endphp
                        <span class="h-5 rounded-[3px]" style="background: {{ $v ? 'var(--c1)' : 'var(--grid)' }}; opacity: {{ $v ? round(0.15 + 0.85 * $v / $hmax, 2) : 1 }}" title="{{ $dn }} {{ str_pad((string) $h, 2, '0', STR_PAD_LEFT) }}:00 – {{ $v }} {{ str('visit')->plural($v) }}"></span>
                    @endfor
                @endforeach
            </div>
            <div class="mt-2 flex items-center justify-end gap-1 text-[10px] text-gray-500">Fewer @foreach ([0.15, 0.35, 0.55, 0.75, 1] as $o)<span class="h-2.5 w-4 rounded-sm" style="background: var(--c1); opacity: {{ $o }}"></span>@endforeach More</div>
        </div>
    </div>
</x-filament::section>

{{-- Reports --}}
<x-filament::tabs>
    @foreach ($tabs as $k => $label)
        <x-filament::tabs.item :active="$tab === $k" wire:click="$set('tab', '{{ $k }}')">{{ $label }}</x-filament::tabs.item>
    @endforeach
</x-filament::tabs>
@php $visitCols = [['k', 'Source', 'text'], ['visits', 'Visits', 'num'], ['people', 'Visitors', 'num'], ['ppv', 'Pages / visit', 'dec'], ['secs', 'Avg time', 'time'], ['leads', 'Leads', 'num']]; @endphp

@if ($tab === 'acquisition')
<div class="grid gap-4 lg:grid-cols-2">
    @include('filament.admin.pages.partials.an-table', ['title' => 'Channels', 'hint' => 'Click one to see only that traffic.', 'rows' => $channels, 'filterKey' => 'channel', 'cols' => array_merge([['k', 'Channel', 'text']], array_slice($visitCols, 1)), 'bar' => 'visits'])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Top sources', 'rows' => $sources, 'filterKey' => 'source', 'cols' => [['k', 'Source', 'text'], ['channel', 'Channel', 'text'], ['visits', 'Visits', 'num'], ['ppv', 'Pages / visit', 'dec'], ['leads', 'Leads', 'num']], 'bar' => 'visits'])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Search engines', 'rows' => $search, 'filterKey' => 'source', 'cols' => array_merge([['k', 'Search engine', 'text']], array_slice($visitCols, 1)), 'bar' => 'visits'])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Social networks', 'rows' => $social, 'filterKey' => 'source', 'cols' => array_merge([['k', 'Network', 'text']], array_slice($visitCols, 1)), 'bar' => 'visits'])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Referring websites', 'hint' => 'Other sites that link to you.', 'rows' => $referrers, 'filterKey' => '', 'cols' => array_merge([['k', 'Website', 'text']], array_slice($visitCols, 1)), 'bar' => 'visits'])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Referring links', 'hint' => 'The exact pages that sent visitors.', 'rows' => $links, 'cols' => [['k', 'Link', 'link'], ['channel', 'Channel', 'text'], ['visits', 'Visits', 'num']]])
    <div class="lg:col-span-2">@include('filament.admin.pages.partials.an-table', ['title' => 'Campaigns (UTM tags)', 'hint' => 'Links tagged with utm_source, utm_medium, utm_campaign.', 'rows' => $campaigns, 'cols' => [['s', 'Source', 'text'], ['m', 'Medium', 'text'], ['c', 'Campaign', 'text'], ['visits', 'Visits', 'num'], ['leads', 'Leads', 'num']], 'empty' => 'No tagged links used in this period. Add ?utm_source=…&utm_medium=…&utm_campaign=… to links you share.'])</div>
</div>
@elseif ($tab === 'content')
    @include('filament.admin.pages.partials.an-table', ['title' => 'Pages', 'hint' => 'Click a page to see where its visitors came from. Entrances: visits that started on this page.', 'rows' => $pages, 'filterKey' => 'page', 'cols' => [['k', 'Page', 'text'], ['views', 'Views', 'num'], ['people', 'Visitors', 'num'], ['secs', 'Avg time', 'time'], ['entries', 'Entrances', 'num'], ['leads', 'Leads', 'num']], 'bar' => 'views'])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Landing pages', 'hint' => 'Where visits start.', 'rows' => $landing, 'filterKey' => 'page', 'cols' => array_merge([['k', 'Page', 'text']], array_slice($visitCols, 1)), 'bar' => 'visits'])
@elseif ($tab === 'ai')
<div class="grid gap-4 lg:grid-cols-2">
    @include('filament.admin.pages.partials.an-table', ['title' => 'AI assistants', 'hint' => 'People who clicked through from an AI answer.', 'rows' => $ai, 'filterKey' => 'source', 'cols' => array_merge([['k', 'Assistant', 'text']], array_slice($visitCols, 1)), 'bar' => 'visits', 'empty' => 'No visits from ChatGPT, Perplexity, Claude, Gemini, Copilot or Grok in this period.'])
    @include('filament.admin.pages.partials.an-table', ['title' => 'AI bots reading your site', 'hint' => '"Live fetch" means someone asked an AI assistant and it opened your page to answer.', 'rows' => $aiBots, 'cols' => [['k', 'Bot', 'text'], ['hits', 'Pages read', 'num'], ['pages', 'Different pages', 'num'], ['last', 'Last seen', 'when']], 'bar' => 'hits', 'empty' => 'No AI crawlers recorded in this period.'])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Pages AI bots read most', 'rows' => $botPages, 'filterKey' => 'page', 'cols' => [['k', 'Page', 'text'], ['hits', 'Reads', 'num'], ['bots', 'Bots', 'num']], 'bar' => 'hits', 'empty' => 'No AI crawlers recorded in this period.'])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Search engine crawlers', 'rows' => $searchBots, 'cols' => [['k', 'Crawler', 'text'], ['hits', 'Pages read', 'num'], ['pages', 'Different pages', 'num'], ['last', 'Last seen', 'when']], 'bar' => 'hits'])
</div>
@else
<div class="grid gap-4 lg:grid-cols-3">
    @include('filament.admin.pages.partials.an-table', ['title' => 'Devices', 'rows' => $devices, 'cols' => [['k', 'Device', 'text'], ['visits', 'Visits', 'num'], ['ppv', 'Pages / visit', 'dec'], ['leads', 'Leads', 'num']], 'bar' => 'visits'])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Browsers', 'rows' => $browsers, 'cols' => [['k', 'Browser', 'text'], ['visits', 'Visits', 'num'], ['leads', 'Leads', 'num']], 'bar' => 'visits'])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Countries', 'hint' => 'Where visitors are browsing from. Only the country is kept, never the IP address. IP geolocation by DB-IP (db-ip.com).', 'rows' => $countries, 'empty' => 'No countries yet. Run php artisan gtech:geoip-update once on the server.', 'cols' => [['k', 'Country', 'text'], ['visits', 'Visits', 'num'], ['leads', 'Leads', 'num']], 'bar' => 'visits'])
</div>
@endif
</div>
</x-filament-panels::page>
