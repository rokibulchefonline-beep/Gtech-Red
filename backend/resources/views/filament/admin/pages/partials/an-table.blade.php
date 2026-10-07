{{-- One report table. $title, $hint, $rows (arrays), $cols [[key, label, type]], $filterKey (click a row to filter), $empty --}}
@php($fmtTime = fn ($s) => $s >= 60 ? floor($s / 60).'m '.($s % 60).'s' : ((int) $s).'s')
<x-filament::section :heading="$title" :description="$hint ?? null" compact>
    @if (count($rows))
        <div class="overflow-x-auto -mx-2">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 dark:text-gray-400">
                @foreach ($cols as [$k, $label, $type])<th class="px-2 py-1.5 font-medium {{ $type === 'text' ? '' : 'text-right' }}">{{ $label }}</th>@endforeach
            </tr></thead>
            <tbody>
            @foreach ($rows as $row)
                <tr class="border-t border-gray-100 dark:border-white/5">
                    @foreach ($cols as $i => [$k, $label, $type])
                        @php($v = $row[$k] ?? '')
                        <td class="px-2 py-1.5 {{ $type === 'text' ? 'max-w-xs truncate' : 'text-right tabular-nums' }}">
                            @if ($i === 0 && !empty($filterKey))
                                <button type="button" wire:click="filter('{{ $filterKey }}', @js((string) $v))" class="text-left text-primary-600 hover:underline dark:text-primary-400 truncate max-w-full" title="Show only {{ $v ?: '(none)' }}">{{ $v !== '' ? $v : '(none)' }}</button>
                            @elseif ($type === 'time'){{ $fmtTime((int) round((float) $v)) }}
                            @elseif ($type === 'dec'){{ number_format((float) $v, 1) }}
                            @elseif ($type === 'num'){{ number_format((int) $v) }}
                            @elseif ($type === 'when'){{ $v ? \Illuminate\Support\Carbon::parse($v)->diffForHumans() : '' }}
                            @elseif ($type === 'link')<a href="{{ $v }}" target="_blank" rel="noopener noreferrer nofollow" class="text-primary-600 hover:underline dark:text-primary-400">{{ \Illuminate\Support\Str::limit(preg_replace('#^https?://(www\.)?#', '', (string) $v), 60) }}</a>
                            @else{{ $v }}@endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $empty ?? 'No data for this period yet.' }}</p>
    @endif
</x-filament::section>
