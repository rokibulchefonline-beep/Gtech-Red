<x-filament-panels::page>
@php
    $fmtTime = fn ($s) => $s >= 60 ? floor($s / 60).'m '.($s % 60).'s' : ((int) $s).'s';
    $buckets = $series['buckets'];
    $max = max(1, collect($buckets)->map(fn ($b) => array_sum($b))->max());
    $n = count($buckets); $W = 1000; $H = 220; $pad = 28; $gap = $n > 60 ? 1 : 3; $bw = max(2, ($W - $pad) / max(1, $n) - $gap);
    $tick = $max <= 5 ? 1 : (int) ceil($max / 4 / (10 ** floor(log10(max(1, $max / 4))))) * (10 ** floor(log10(max(1, $max / 4))));
    $visitCols = [['k', 'Source', 'text'], ['visits', 'Visits', 'num'], ['people', 'Visitors', 'num'], ['ppv', 'Pages / visit', 'dec'], ['secs', 'Avg time', 'time'], ['leads', 'Leads', 'num']];
@endphp
<style>
    .an-viz { --s-Search:{{ $colours['Search'][0] }}; --s-AI:{{ $colours['AI'][0] }}; --s-Direct:{{ $colours['Direct'][0] }}; --s-Social:{{ $colours['Social'][0] }}; --s-Referral:{{ $colours['Referral'][0] }}; --s-Paid:{{ $colours['Paid'][0] }}; --s-Email:{{ $colours['Email'][0] }}; --s-Campaign:{{ $colours['Campaign'][0] }}; --gap:#fff; }
    .dark .an-viz { --s-Search:{{ $colours['Search'][1] }}; --s-AI:{{ $colours['AI'][1] }}; --s-Direct:{{ $colours['Direct'][1] }}; --s-Social:{{ $colours['Social'][1] }}; --s-Referral:{{ $colours['Referral'][1] }}; --s-Paid:{{ $colours['Paid'][1] }}; --s-Email:{{ $colours['Email'][1] }}; --s-Campaign:{{ $colours['Campaign'][1] }}; --gap:rgb(24 24 27); }
    .an-bar:hover { opacity: .8 }
</style>

{{-- Filters: one row above everything --}}
<div class="flex flex-wrap items-center gap-2">
    @foreach (['1' => 'Today', '7' => '7 days', '30' => '30 days', '90' => '90 days', '365' => '12 months'] as $v => $l)
        <x-filament::button size="sm" :color="$period === (string) $v ? 'primary' : 'gray'" wire:click="$set('period', '{{ $v }}')">{{ $l }}</x-filament::button>
    @endforeach
    @foreach (['channel' => 'Channel', 'source' => 'Source', 'page' => 'Page'] as $key => $label)
        @if ($this->{$key} !== '')
            <x-filament::badge color="primary" class="cursor-pointer" wire:click="filter('{{ $key }}', {{ \Illuminate\Support\Js::from($this->{$key}) }})">{{ $label }}: {{ $this->{$key} }} ✕</x-filament::badge>
        @endif
    @endforeach
    @if ($channel !== '' || $source !== '' || $page !== '')
        <x-filament::link tag="button" size="sm" wire:click="clear">Clear filters</x-filament::link>
    @endif
    <span class="ms-auto text-xs text-gray-500 dark:text-gray-400">{{ $r->from->format('j M Y') }} to {{ $r->to->format('j M Y') }} · no cookies, your own visits excluded</span>
</div>

{{-- Headline numbers --}}
<div class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-8">
    @foreach ([['Visitors', number_format($totals['visitors']), 'different people'], ['Visits', number_format($totals['visits']), 'sessions'], ['Page views', number_format($totals['views']), ''],
        ['Avg visit', $fmtTime($totals['avg_time']), 'time on site'], ['Bounce', $totals['bounce'].'%', 'saw one page'], ['Leads', number_format($totals['leads']), 'from these visits'],
        ['Conversion', $totals['conversion'].'%', 'visits → lead'], ['From AI', number_format($totals['ai']), 'ChatGPT, Perplexity…']] as [$label, $value, $sub])
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</div>
            <div class="mt-1 text-2xl font-semibold tabular-nums text-gray-950 dark:text-white">{{ $value }}</div>
            @if ($sub)<div class="text-xs text-gray-500 dark:text-gray-400">{{ $sub }}</div>@endif
        </div>
    @endforeach
</div>

{{-- Visits over time, stacked by channel --}}
<x-filament::section heading="Visits by channel" :description="$series['monthly'] ? 'Per month' : 'Per day'" compact>
    <div class="an-viz">
        <div class="mb-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-600 dark:text-gray-300" aria-label="Legend">
            @foreach (\App\Support\Analytics\Classifier::CHANNELS as $c)
                <button type="button" wire:click="filter('channel', '{{ $c }}')" class="inline-flex items-center gap-1.5 {{ $channel !== '' && $channel !== $c ? 'opacity-40' : '' }}">
                    <span class="inline-block h-2.5 w-2.5 rounded-sm" style="background: var(--s-{{ $c }})"></span>{{ $c === 'AI' ? 'AI assistants' : $c }}
                </button>
            @endforeach
        </div>
        <svg viewBox="0 0 {{ $W }} {{ $H + 22 }}" class="w-full h-auto" role="img" aria-label="Visits per {{ $series['monthly'] ? 'month' : 'day' }} by channel">
            @for ($t = 0; $t <= $max; $t += $tick)
                @php($y = $H - $t / $max * ($H - 10))
                <line x1="{{ $pad }}" x2="{{ $W }}" y1="{{ $y }}" y2="{{ $y }}" class="stroke-gray-200 dark:stroke-white/10" stroke-width="1"/>
                <text x="{{ $pad - 6 }}" y="{{ $y + 4 }}" text-anchor="end" class="fill-gray-400" font-size="11">{{ $t }}</text>
            @endfor
            @php($i = 0)
            @foreach ($buckets as $label => $b)
                @php($x = $pad + $i * ($bw + $gap) + $gap / 2) @php($y = $H) @php($total = array_sum($b))
                <g>
                    <title>{{ \Illuminate\Support\Carbon::parse($series['monthly'] ? $label.'-01' : $label)->format($series['monthly'] ? 'M Y' : 'D j M') }}: {{ $total }} visits&#10;{{ collect($b)->filter()->map(fn ($v, $k) => ($k === 'AI' ? 'AI assistants' : $k).': '.$v)->implode("\n") }}</title>
                    <rect x="{{ $x }}" y="10" width="{{ $bw }}" height="{{ $H - 10 }}" fill="transparent"/>
                    @foreach ($b as $ch => $v)
                        @continue(! $v)
                        @php($h = $v / $max * ($H - 10))
                        <rect class="an-bar" x="{{ $x }}" y="{{ $y - $h }}" width="{{ $bw }}" height="{{ max(1, $h - 1) }}" style="fill: var(--s-{{ $ch }})" />
                        @php($y -= $h)
                    @endforeach
                </g>
                @if ($n <= 14 || $i % (int) ceil($n / 10) === 0)
                    <text x="{{ $x + $bw / 2 }}" y="{{ $H + 16 }}" text-anchor="middle" class="fill-gray-400" font-size="11">{{ \Illuminate\Support\Carbon::parse($series['monthly'] ? $label.'-01' : $label)->format($series['monthly'] ? 'M' : 'j M') }}</text>
                @endif
                @php($i++)
            @endforeach
        </svg>
    </div>
</x-filament::section>

<div class="grid gap-4 lg:grid-cols-2">
    @include('filament.admin.pages.partials.an-table', ['title' => 'Channels', 'hint' => 'Click one to see only that traffic.', 'rows' => $channels, 'filterKey' => 'channel', 'cols' => array_merge([['k', 'Channel', 'text']], array_slice($visitCols, 1))])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Top sources', 'rows' => $sources, 'filterKey' => 'source', 'cols' => [['k', 'Source', 'text'], ['channel', 'Channel', 'text'], ['visits', 'Visits', 'num'], ['ppv', 'Pages / visit', 'dec'], ['leads', 'Leads', 'num']]])
    @include('filament.admin.pages.partials.an-table', ['title' => 'AI assistants', 'hint' => 'People who clicked through from an AI answer.', 'rows' => $ai, 'filterKey' => 'source', 'cols' => array_merge([['k', 'Assistant', 'text']], array_slice($visitCols, 1)), 'empty' => 'No visits from ChatGPT, Perplexity, Claude, Gemini, Copilot or Grok in this period.'])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Search engines', 'rows' => $search, 'filterKey' => 'source', 'cols' => array_merge([['k', 'Search engine', 'text']], array_slice($visitCols, 1))])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Referring websites', 'hint' => 'Other sites that link to you.', 'rows' => $referrers, 'filterKey' => '', 'cols' => array_merge([['k', 'Website', 'text']], array_slice($visitCols, 1))])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Social networks', 'rows' => $social, 'filterKey' => 'source', 'cols' => array_merge([['k', 'Network', 'text']], array_slice($visitCols, 1))])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Referring links', 'hint' => 'The exact pages that sent visitors.', 'rows' => $links, 'cols' => [['k', 'Link', 'link'], ['channel', 'Channel', 'text'], ['visits', 'Visits', 'num']]])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Campaigns (UTM tags)', 'hint' => 'Links tagged with utm_source, utm_medium, utm_campaign.', 'rows' => $campaigns, 'cols' => [['s', 'Source', 'text'], ['m', 'Medium', 'text'], ['c', 'Campaign', 'text'], ['visits', 'Visits', 'num'], ['leads', 'Leads', 'num']], 'empty' => 'No tagged links used in this period. Add ?utm_source=…&utm_medium=…&utm_campaign=… to links you share.'])
</div>

@include('filament.admin.pages.partials.an-table', ['title' => 'Pages', 'hint' => 'Click a page to see where its visitors came from. Entrances: visits that started on this page.', 'rows' => $pages, 'filterKey' => 'page', 'cols' => [['k', 'Page', 'text'], ['views', 'Views', 'num'], ['people', 'Visitors', 'num'], ['secs', 'Avg time', 'time'], ['entries', 'Entrances', 'num'], ['leads', 'Leads', 'num']]])

<div class="grid gap-4 lg:grid-cols-2">
    @include('filament.admin.pages.partials.an-table', ['title' => 'Landing pages', 'hint' => 'Where visits start.', 'rows' => $landing, 'filterKey' => 'page', 'cols' => array_merge([['k', 'Page', 'text']], array_slice($visitCols, 1))])
    @include('filament.admin.pages.partials.an-table', ['title' => 'AI bots reading your site', 'hint' => '"Live fetch" means someone asked an AI assistant and it opened your page to answer.', 'rows' => $aiBots, 'cols' => [['k', 'Bot', 'text'], ['hits', 'Pages read', 'num'], ['pages', 'Different pages', 'num'], ['last', 'Last seen', 'when']], 'empty' => 'No AI crawlers recorded in this period.'])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Pages AI bots read most', 'rows' => $botPages, 'filterKey' => 'page', 'cols' => [['k', 'Page', 'text'], ['hits', 'Reads', 'num'], ['bots', 'Bots', 'num']], 'empty' => 'No AI crawlers recorded in this period.'])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Search engine crawlers', 'rows' => $searchBots, 'cols' => [['k', 'Crawler', 'text'], ['hits', 'Pages read', 'num'], ['pages', 'Different pages', 'num'], ['last', 'Last seen', 'when']]])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Devices', 'rows' => $devices, 'cols' => [['k', 'Device', 'text'], ['visits', 'Visits', 'num'], ['ppv', 'Pages / visit', 'dec'], ['leads', 'Leads', 'num']]])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Browsers', 'rows' => $browsers, 'cols' => [['k', 'Browser', 'text'], ['visits', 'Visits', 'num'], ['leads', 'Leads', 'num']]])
    @include('filament.admin.pages.partials.an-table', ['title' => 'Countries', 'hint' => 'Available when the site runs behind Cloudflare.', 'rows' => $countries, 'cols' => [['k', 'Country', 'text'], ['visits', 'Visits', 'num'], ['leads', 'Leads', 'num']]])
</div>
</x-filament-panels::page>
