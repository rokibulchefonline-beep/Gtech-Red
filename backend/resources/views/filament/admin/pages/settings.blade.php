<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}
        @if ($this::canEdit())
            <x-filament::button type="submit">Save settings</x-filament::button>
        @else
            <p class="text-sm text-gray-500">You can view these settings. Ask an admin to change them.</p>
        @endif
    </form>
</x-filament-panels::page>
