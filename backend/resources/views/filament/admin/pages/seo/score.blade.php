{{-- A score tile. $n 0-100, $label, optional $sub --}}
@php($tone = $n >= 85 ? 'text-success-600 dark:text-success-400' : ($n >= 65 ? 'text-warning-600 dark:text-warning-400' : 'text-danger-600 dark:text-danger-400'))
<div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
    <p class="mt-1 text-3xl font-semibold {{ $tone }}">{{ $n }}<span class="text-base font-normal text-gray-400">/100</span></p>
    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10" role="presentation"><div class="h-full rounded-full {{ $n >= 85 ? 'bg-success-500' : ($n >= 65 ? 'bg-warning-500' : 'bg-danger-500') }}" style="width: {{ $n }}%"></div></div>
    @isset($sub)<p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $sub }}</p>@endisset
</div>
