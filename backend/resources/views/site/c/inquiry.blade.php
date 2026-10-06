@php($h = \App\Support\Site\Repo::homeSection('inquiry'))
<section class="iq" id="inquiry">
<div class="wrap iq-grid">
<div class="iq-copy">
<h2>@if (str_contains($h['heading'], '[['))@hl($h['heading'])@else{{ $h['heading'] }}@endif</h2>
<span class="iq-rule"></span>
<p>@rt($h['paras'][0] ?? '')</p>
<a class="btn" href="/contact">Schedule a meeting</a>
</div>
@include('site.partials.inquiry-form')
</div>
</section>
