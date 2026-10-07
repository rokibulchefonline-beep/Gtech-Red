<x-filament-panels::page>
    @php($c = $this->comparison())
    <style>
        .vh-diff del{background:#fde2e2;color:#991b1b;text-decoration:line-through}
        .vh-diff ins{background:#dcfce7;color:#166534;text-decoration:none}
        .dark .vh-diff del{background:rgba(239,68,68,.2);color:#fca5a5}
        .dark .vh-diff ins{background:rgba(34,197,94,.2);color:#86efac}
        .vh-diff p{margin:0 0 .5em}
    </style>
    @if ($c)
        <x-filament::section>
            <x-slot name="heading">Changes since the version of {{ $c['rev']->created_at?->format('D j M Y, H:i') }}</x-slot>
            <x-slot name="description">Struck-through text is in that version only; highlighted text was added since, up to {{ $c['against'] }}.</x-slot>
            @if (! $c['rows'])
                <p class="text-sm text-gray-500">No differences: this version is the same as {{ $c['against'] }}.</p>
            @else
                <dl class="vh-diff divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($c['rows'] as $row)
                        <div class="grid gap-1 py-3 md:grid-cols-4 md:gap-4">
                            <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $row['label'] }}</dt>
                            <dd class="text-sm leading-6 text-gray-900 dark:text-gray-100 md:col-span-3" style="overflow-wrap:anywhere">{!! $row['html'] !!}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif
        </x-filament::section>
    @endif
    {{ $this->table }}
</x-filament-panels::page>
