{{-- Day / Night switch in the top bar (the same setting as the avatar menu's theme buttons, remembered per browser). --}}
<div x-data="{ t: localStorage.getItem('theme') || @js(filament()->getDefaultThemeMode()->value),
        set(v) { this.t = v; window.dispatchEvent(new CustomEvent('theme-changed', { detail: v })) },
        isDark() { return this.t === 'dark' || (this.t === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches) } }"
    x-on:theme-changed.window="t = $event.detail"
    class="me-3 hidden items-center rounded-lg p-0.5 ring-1 ring-gray-950/10 sm:flex dark:ring-white/20" role="group" aria-label="Colour mode">
    <button type="button" x-on:click="set('light')" :aria-pressed="! isDark()" title="Day mode (white)"
        class="flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-semibold transition"
        :class="! isDark() ? 'bg-primary-600 text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'">
        <x-filament::icon icon="heroicon-m-sun" class="h-4 w-4" /> Day
    </button>
    <button type="button" x-on:click="set('dark')" :aria-pressed="isDark()" title="Night mode (dark)"
        class="flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-semibold transition"
        :class="isDark() ? 'bg-primary-600 text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'">
        <x-filament::icon icon="heroicon-m-moon" class="h-4 w-4" /> Night
    </button>
</div>
