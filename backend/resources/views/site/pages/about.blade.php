{{-- /about (app/about/page.tsx). $p Page --}}
@extends('site.layout')
@php($R = \App\Support\Site\Repo::class)
@php($secs = collect($p->sections ?? []))
@php($cut = $secs->search(fn ($s) => $s['id'] === 'reviews'))
@php($cut = $cut === false ? $secs->count() - 1 : $cut)
@php($cases = $R::caseStudies(6))
@section('title', $p->meta_title)
@section('description', $p->meta_description)
@section('content')
<section class="sp-hero">
<div class="wrap sp-hero-in">
<nav class="sp-crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><b>About Us</b></nav>
<h1>@hl($p->hero['h1'] ?? '')</h1>
<p class="sp-lead">@rt($p->hero['lead'] ?? '')</p>
<div class="sp-hero-btns">
<a class="sp-btn-red" href="/contact">Work With Us</a>
<a class="sp-btn-line" href="/case-studies">See Our Work</a>
</div>
<ul class="sp-hero-points">@foreach ($p->hero['points'] ?? [] as $pt)<li>@include('site.c.tick'){{ $pt }}</li>@endforeach</ul>
<div class="sp-hero-show about-video">@include('site.c.intro-video')</div>
</div>
</section>
<div class="sp-after-hero">@include('site.c.partner-strip')</div>
@foreach ($secs->slice(0, $cut) as $s)@include('site.c.block', ['s' => $s, 'slug' => 'about', 'name' => 'GTech Digital'])@endforeach
@if ($cases->count())
<section id="case-studies" class="sp-sec cases"><div class="wrap">
@include('site.c.head', ['heading' => 'Case Studies and Client Results'])
@include('site.c.case-carousel', ['docs' => $cases])
<p class="cases-all"><a class="btn-dark" href="/case-studies">View All Case Studies</a></p>
</div></section>
@endif
@foreach ($secs->slice($cut) as $s)@include('site.c.block', ['s' => $s, 'slug' => 'about', 'name' => 'GTech Digital'])@endforeach
@include('site.c.faq', ['title' => 'Frequently Asked Questions About GTech Digital', 'faqs' => $p->faqs ?? []])
@include('site.c.inquiry')
@endsection
