{{-- Case study slider (controls added by site.js when the cards overflow). --}}
<div class="case-wrap" data-carousel><div class="case-track">@foreach ($docs as $i => $d)@include('site.c.case-card', ['doc' => $d, 'index' => $i])@endforeach</div></div>
