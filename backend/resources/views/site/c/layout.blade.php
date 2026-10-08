{{-- Sections of a designed page in the order set in the panel. Designed parts come from @section('d-<key>') in the page view. $p Page, $slug, $name --}}
@foreach (\App\Support\Site\Layout::items($p) as $it)
@if (($it['type'] ?? '') === 'designed')
@if (str_starts_with($it['key'], 'sec:'))
@php($s = collect($p->sections ?? [])->firstWhere('id', substr($it['key'], 4)))
@if ($s)@include('site.c.block', ['s' => $s, 'slug' => $slug, 'name' => $name])@endif
@else
@yield('d-'.$it['key'])
@endif
@else
@include('site.c.block', ['s' => $it, 'slug' => $slug, 'name' => $name])
@endif
@endforeach
