@if (isset($b))<ul class="sp-list">@foreach ($b as $x)<li>@include('site.c.tick')<span>@rt($x)</span></li>@endforeach</ul>@endif
