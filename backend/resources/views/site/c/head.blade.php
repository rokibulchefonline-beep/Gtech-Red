{{-- Section heading (Head in ServicePage.tsx). $heading, $center (default true), $intro --}}
<div class="{{ ($center ?? true) ? 'sp-head center' : 'sp-head' }}">
<h2>@hl($heading)</h2>
@if (!empty($intro))<p class="sp-intro">@rt($intro)</p>@endif
</div>
