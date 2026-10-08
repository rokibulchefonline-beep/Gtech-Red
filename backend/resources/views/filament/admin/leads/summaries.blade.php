{{-- Every saved version of the lead's summary notes, newest first. --}}
@php($versions = $getRecord()->activities()->where('type', 'summary')->with('user')->get())
<div class="space-y-3">
@foreach ($versions as $i => $v)
    <div class="rounded-lg border p-3 {{ $i === 0 ? 'border-primary-300 bg-primary-50/40 dark:border-primary-500/40 dark:bg-primary-500/5' : 'border-gray-200 dark:border-white/10' }}">
        <p class="text-xs text-gray-500">{{ $i === 0 ? 'Current' : 'Version '.($versions->count() - $i) }} · {{ $v->created_at?->format('D j M Y, H:i') }} · {{ $v->user?->name ?? 'Automatic' }}</p>
        <p class="mt-1 whitespace-pre-line text-sm">{{ $v->body }}</p>
    </div>
@endforeach
</div>
