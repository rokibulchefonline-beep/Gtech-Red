@extends('site.layout')
@section('title', 'Blade preview | GTech Digital')
@push('head')<meta name="robots" content="noindex">@endpush
@section('content')
<section class="sp-hero compact"><div class="wrap sp-hero-in"><nav class="sp-crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><b>Preview</b></nav><h1>@hl('Blade Layout Preview of [[GTech Digital]]')</h1><p class="sp-lead">Header, footer, contact popup, cookie banner, back-to-top and scroll motion rendered by Laravel Blade.</p><div class="sp-hero-btns"><a class="sp-btn-red" href="/contact?service=Local SEO">Open the contact popup</a></div></div></section>
<section class="sp-sec"><div class="wrap"><div class="sp-head center"><h2>@hl('Request a [[Free Proposal]]')</h2></div><div style="max-width:560px;margin:0 auto">@include('site.partials.inquiry-form')</div></div></section>
<section class="sp-sec"><div class="wrap" style="min-height:900px"><div class="sp-head center"><h2>@hl('Scroll Down to Test the Motion and Back to Top')</h2></div></div></section>
@endsection
