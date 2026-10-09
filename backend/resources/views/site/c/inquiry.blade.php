{{-- "Request a Free Proposal" with the short form. $compact (service pages): a shorter band, the service chosen already. --}}
@php($h = \App\Support\Site\Repo::homeSection('inquiry'))
@php($h = ['heading' => $heading ?? $h['heading'], 'paras' => isset($intro) ? [strip_tags((string) $intro)] : $h['paras']])
@php($compact = false) {{-- One design on every page; $service (on service pages) is chosen in the form already. --}}
<section class="iq{{ $compact ? ' iq-compact' : '' }}" id="{{ $id ?? 'inquiry' }}">
<div class="wrap iq-grid">
<div class="iq-copy">
@if ($compact)
<h2>@hl('Get a Free [[Proposal]]')</h2>
<span class="iq-rule"></span>
<p>Tell us about your business and we'll reply with a clear {{ $service ?? '' }} plan and price.</p>
@else
<h2>@if (isset($heading) || str_contains($h['heading'], '[['))@hl($h['heading'])@else{{ $h['heading'] }}@endif</h2>
<span class="iq-rule"></span>
<p>@rt($h['paras'][0] ?? '')</p>
<a class="btn" href="/contact">Schedule a meeting</a>
@endif
</div>
@include('site.partials.inquiry-form', ['compact' => $compact, 'service' => $service ?? null])
</div>
</section>
