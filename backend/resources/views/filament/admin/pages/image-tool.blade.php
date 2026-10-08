<x-filament-panels::page>
<p class="text-sm text-gray-600 dark:text-gray-400">{{ $this->intro() }}</p>
<form wire:submit="run" class="space-y-4">
    {{ $this->form }}
    <x-filament::button type="submit" icon="heroicon-o-sparkles" wire:loading.attr="disabled" wire:target="run">
        <span wire:loading.remove wire:target="run">{{ $this->getTitle() === 'Image converter' ? 'Convert' : 'Resize' }}</span>
        <span wire:loading wire:target="run">Working…</span>
    </x-filament::button>
</form>

@if ($results)
<x-filament::section heading="Results" description="Download them, or save them to the Media library to use on the website. Results are kept for a day.">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($results as $i => $r)
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-white/10">
            <div class="flex h-44 items-center justify-center bg-gray-50 dark:bg-white/5"><img src="{{ $r['url'] }}" alt="" class="max-h-44 max-w-full object-contain"></div>
            <div class="space-y-2 p-3 text-sm">
                <p class="truncate font-medium" title="{{ $r['name'] }}">{{ $r['name'] }}</p>
                <p class="text-gray-600 dark:text-gray-400">{{ $r['w'] }} × {{ $r['h'] }} px ·
                    {{ static::kb($r['before']) }} → <b class="{{ $r['after'] <= \App\Support\ImageTools::MAX_BYTES ? 'text-success-600' : 'text-warning-600' }}">{{ static::kb($r['after']) }}</b>
                    @if ($r['before'] > 0)<span class="text-xs">({{ $r['after'] <= $r['before'] ? '-'.round(100 - $r['after'] / $r['before'] * 100) : '+'.round($r['after'] / $r['before'] * 100 - 100) }}%)</span>@endif
                </p>
                <div class="flex flex-wrap gap-2">
                    <x-filament::button size="sm" color="gray" icon="heroicon-o-arrow-down-tray" tag="a" :href="$r['url']" :download="$r['name']">Download</x-filament::button>
                    @if (empty($r['saved']))
                    <x-filament::button size="sm" icon="heroicon-o-photo" wire:click="save({{ $i }})">Save to Media library</x-filament::button>
                    @else
                    <x-filament::badge color="success" icon="heroicon-o-check">Saved</x-filament::badge>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</x-filament::section>
@endif
</x-filament-panels::page>
