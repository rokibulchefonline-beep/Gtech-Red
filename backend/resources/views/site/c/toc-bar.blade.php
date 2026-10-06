{{-- Sticky in-page navigation with scroll-spy (behaviour in site.js). $items [id,label,icon?], $label, $class --}}
<nav class="sp-toc {{ $class ?? '' }}" aria-label="{{ $label }}" data-toc><div class="wrap">
@foreach ($items as $it)<a href="#{{ $it['id'] }}"@if ($loop->first) class="on" aria-current="true"@endif>@if (!empty($it['icon']))@icon($it['icon'], 16)@endif{{ $it['label'] }}</a>@endforeach
</div></nav>
