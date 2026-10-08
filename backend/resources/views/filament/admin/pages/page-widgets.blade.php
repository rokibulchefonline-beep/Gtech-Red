<x-filament-panels::page>
@php($totals = $this->getTotals())
<div class="grid gap-4 sm:grid-cols-3">
    <x-filament::section compact>
        <p class="text-sm text-gray-500 dark:text-gray-400">Widgets you can add</p>
        <p class="mt-1 text-3xl font-semibold">{{ $totals['widgets'] }}</p>
    </x-filament::section>
    <x-filament::section compact>
        <p class="text-sm text-gray-500 dark:text-gray-400">Pages in the site</p>
        <p class="mt-1 text-3xl font-semibold">{{ $totals['pages'] }}</p>
    </x-filament::section>
    <x-filament::section compact>
        <p class="text-sm text-gray-500 dark:text-gray-400">Sections on all pages</p>
        <p class="mt-1 text-3xl font-semibold">{{ $totals['sections'] }}</p>
    </x-filament::section>
</div>

<p class="text-sm text-gray-600 dark:text-gray-400">
    To add a widget, open a page in <b>Website content</b> → <b>Pages</b> (or a landing page), go to the <b>Sections</b> tab and click <b>Add a section</b>.
    Sections keep their order and can be moved, copied or removed there.
</p>

@foreach ($this->getGroups() as $group => $widgets)
    <div class="space-y-3">
        <h2 class="text-lg font-semibold">{{ $group }}</h2>
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($widgets as $w)
                <x-filament::section>
                    <x-slot name="heading">{{ $w['name'] }}</x-slot>
                    <x-slot name="description">
                        Used in {{ $w['used'] }} {{ \Illuminate\Support\Str::plural('section', $w['used']) }}@if ($w['pageTotal']) on {{ $w['pageTotal'] }} {{ \Illuminate\Support\Str::plural('page', $w['pageTotal']) }}@endif
                    </x-slot>
                    <div class="space-y-3 text-sm">
                        <p><b>What it is for:</b> {{ $w['use'] }}</p>
                        <p><b>Best used:</b> {{ $w['best'] }}</p>
                        <div>
                            <p class="font-medium">Fields</p>
                            <ul class="list-disc pl-5">@foreach ($w['fields'] as $f)<li>{{ $f }}</li>@endforeach</ul>
                        </div>
                        <div>
                            <p class="font-medium">Tips</p>
                            <ul class="list-disc pl-5">@foreach ($w['tips'] as $t)<li>{{ $t }}</li>@endforeach</ul>
                        </div>
                        <p class="rounded-lg bg-gray-50 p-3 dark:bg-white/5"><b>Example:</b> {{ $w['example'] }}</p>
                        @if ($w['pageNames'])
                            <p class="text-xs text-gray-500 dark:text-gray-400">Used on: {{ implode(', ', $w['pageNames']) }}{{ $w['pageTotal'] > count($w['pageNames']) ? ' and '.($w['pageTotal'] - count($w['pageNames'])).' more' : '' }}</p>
                        @endif
                    </div>
                </x-filament::section>
            @endforeach
        </div>
    </div>
@endforeach
</x-filament-panels::page>
