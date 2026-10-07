<x-filament-panels::page>
    @php($cols = $this->columns())
    @php($canMove = $this->canMove())
    <div class="flex flex-wrap items-center gap-3">
        <x-filament::input.wrapper class="w-64">
            <x-filament::input type="search" wire:model.live.debounce.400ms="q" placeholder="Search name, business or email" />
        </x-filament::input.wrapper>
        <x-filament::input.wrapper class="w-56">
            <x-filament::input.select wire:model.live="owner">
                @foreach ($this->ownerOptions() as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
        <span class="text-sm text-gray-500 dark:text-gray-400">
            Open pipeline: <b class="text-gray-900 dark:text-white">£{{ number_format(collect($cols)->where('closed', false)->sum('value')) }}</b>
            · {{ $canMove ? 'Drag a card to another column to move it.' : 'Read only.' }} Won and Lost show the last 30 days.
        </span>
    </div>

    <div x-data="{ drag: null, over: null }" class="pl-board -mx-4 flex gap-3 overflow-x-auto px-4 pb-4" style="scroll-snap-type:x proximity">
        @foreach ($cols as $c)
            <section wire:key="col-{{ $c['key'] }}" style="scroll-snap-align:start"
                class="pl-col flex w-72 shrink-0 flex-col rounded-xl bg-gray-100 dark:bg-white/5"
                x-bind:class="over === '{{ $c['key'] }}' && 'ring-2 ring-primary-500'"
                @if ($canMove)
                    x-on:dragover.prevent="over = '{{ $c['key'] }}'" x-on:dragleave="over = null"
                    x-on:drop.prevent="over = null; if (drag) $wire.move(drag, '{{ $c['key'] }}'); drag = null"
                @endif>
                <header class="flex items-baseline justify-between gap-2 px-3 pb-2 pt-3">
                    <h2 class="text-sm font-semibold text-gray-950 dark:text-white">{{ $c['label'] }}
                        <span class="ml-1 rounded-full bg-white px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/10 dark:text-gray-300">{{ $c['count'] }}</span></h2>
                    @if ($c['value'] > 0)<span class="text-xs text-gray-500 dark:text-gray-400">£{{ number_format($c['value']) }}</span>@endif
                </header>
                <div class="flex min-h-24 flex-1 flex-col gap-2 px-2 pb-2">
                    @forelse ($c['leads'] as $l)
                        <article wire:key="lead-{{ $l->id }}" @if ($canMove) draggable="true" x-on:dragstart="drag = {{ $l->id }}" x-on:dragend="drag = null; over = null" @endif
                            class="rounded-lg bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 {{ $canMove ? 'cursor-grab' : '' }}">
                            <a href="{{ $this->url($l) }}" class="block text-sm font-semibold text-gray-950 hover:underline dark:text-white">{{ $l->name }}</a>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $l->business }}</p>
                            <p class="mt-1 text-xs text-gray-700 dark:text-gray-300">{{ $l->service }}</p>
                            <div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs">
                                @if ((float) $l->value > 0)<span class="font-medium text-gray-900 dark:text-white">£{{ number_format((float) $l->value) }}</span>@endif
                                @if ($l->next_action_at && ! $c['closed'])
                                    <x-filament::badge size="sm" :color="$l->isOverdue() ? 'danger' : ($l->next_action_at->isToday() ? 'warning' : 'gray')" :icon="$l->isOverdue() ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-calendar'">
                                        {{ $l->next_action_at->format('j M') }}
                                    </x-filament::badge>
                                @endif
                                <span class="ml-auto text-gray-500 dark:text-gray-400" title="Assigned to">{{ $l->owner?->name ?? 'Nobody' }}</span>
                            </div>
                            @if ($canMove)
                                <label class="pl-touch mt-2 block text-xs text-gray-500">Move to
                                    <select class="ml-1 rounded border-gray-300 py-0.5 text-xs dark:border-white/10 dark:bg-gray-800" x-on:change="$wire.move({{ $l->id }}, $event.target.value)">
                                        @foreach ($cols as $o)<option value="{{ $o['key'] }}" @selected($o['key'] === $c['key'])>{{ $o['label'] }}</option>@endforeach
                                    </select>
                                </label>
                            @endif
                        </article>
                    @empty
                        <p class="px-2 py-6 text-center text-xs text-gray-400">No leads</p>
                    @endforelse
                    @if ($c['count'] > count($c['leads']))
                        <a class="px-2 text-xs text-primary-600 underline" href="{{ \App\Filament\Admin\Resources\LeadResource::getUrl('index', ['tableFilters' => ['status' => ['values' => [$c['key']]]]]) }}">and {{ $c['count'] - count($c['leads']) }} more</a>
                    @endif
                </div>
            </section>
        @endforeach
    </div>
    <style>
        /* Touch screens cannot drag: they get the "Move to" menu instead. */
        .pl-touch{display:none}
        @media (hover:none){.pl-touch{display:block}}
    </style>
</x-filament-panels::page>
