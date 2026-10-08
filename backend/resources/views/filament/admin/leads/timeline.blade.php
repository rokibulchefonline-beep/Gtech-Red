{{-- A lead's whole history, newest first: enquiry, follow-ups (calls, emails, meetings), notes, status and summary changes. --}}
@php($lead = $getRecord())
@php($items = $lead->activities()->with('user')->get())
@php($colors = ['created' => '#2563eb', 'call' => '#16a34a', 'email' => '#16a34a', 'meeting' => '#16a34a', 'note' => '#6b7280', 'summary' => '#7c3aed', 'status' => '#d97706', 'assigned' => '#0891b2', 'follow_up' => '#e8202f', 'value' => '#059669'])
@php($fuNumber = $items->whereIn('type', \App\Models\LeadActivity::FOLLOW_UPS)->sortBy('created_at')->values()->mapWithKeys(fn ($a, $i) => [$a->id => $i + 1]))
@if ($items->isEmpty())
<p class="text-sm text-gray-500">Nothing on the timeline yet.</p>
@else
<ol class="relative ms-3 border-s border-gray-200 dark:border-white/10">
@foreach ($items->groupBy(fn ($a) => $a->created_at?->format('Y-m-d')) as $day => $acts)
    <li class="mb-2 ms-6"><p class="pt-1 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ \Illuminate\Support\Carbon::parse($day)->isToday() ? 'Today' : (\Illuminate\Support\Carbon::parse($day)->isYesterday() ? 'Yesterday' : \Illuminate\Support\Carbon::parse($day)->format('l j F Y')) }}</p></li>
    @foreach ($acts as $a)
    <li class="mb-5 ms-6">
        <span class="absolute -start-3 flex h-6 w-6 items-center justify-center rounded-full ring-4 ring-white dark:ring-gray-900" style="background:{{ $colors[$a->type] ?? '#6b7280' }}">
            <x-filament::icon :icon="\App\Models\LeadActivity::ICONS[$a->type] ?? 'heroicon-o-information-circle'" class="h-3.5 w-3.5 text-white" />
        </span>
        <div class="rounded-lg border border-gray-200 p-3 dark:border-white/10">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-sm font-semibold">{{ $a->label() }}@if (isset($fuNumber[$a->id])) <span class="ms-1 rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">Follow-up #{{ $fuNumber[$a->id] }}</span>@endif</p>
                <p class="text-xs text-gray-500"><time title="{{ $a->created_at?->format('D j M Y, H:i') }}">{{ $a->created_at?->format('H:i') }}</time> · {{ $a->user?->name ?? 'Automatic' }}</p>
            </div>
            @if (trim((string) $a->body) !== '')<p class="mt-1 whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($a->body, 600) }}</p>@endif
        </div>
    </li>
    @endforeach
@endforeach
</ol>
@endif
