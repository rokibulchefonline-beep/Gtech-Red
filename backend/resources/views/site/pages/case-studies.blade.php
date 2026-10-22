{{-- /case-studies (app/case-studies/page.tsx) --}}
@extends('site.layout')
@php($desc = 'Real results from SEO, Google Ads, social media, web design and software projects for businesses in the UK and worldwide: more traffic, leads and sales.')
@php($seo = \App\Support\Site\Seo::make('/case-studies', ['title' => 'Digital Marketing Case Studies | GTech Digital', 'absolute' => true, 'description' => $desc], [
    \App\Support\Site\Schema::page(['path' => '/case-studies', 'type' => 'CollectionPage', 'name' => 'Digital Marketing Case Studies of GTech Digital', 'description' => $desc, 'mainEntity' => \App\Support\Site\Schema::abs('/case-studies').'#list']),
    \App\Support\Site\Schema::breadcrumb('/case-studies', [['Case Studies', '/case-studies']]),
    \App\Support\Site\Schema::itemList('/case-studies', 'GTech Digital case studies', \App\Support\Site\Repo::caseStudies()->map(fn ($d) => [$d->title, "/case-studies/{$d->slug}"])->all()),
]))
@section('content')
@include('site.c.page-head', ['title' => 'Digital Marketing Case Studies and [[Results]]', 'sub' => 'GTech Digital case studies show measurable results from SEO, Google Ads, social media, web design and custom software projects for businesses across the UK and worldwide.'])
@php($all = \App\Support\Site\Repo::caseStudies())
@php($cats = collect(\App\Models\CaseStudy::CATEGORIES)->filter(fn ($l, $k) => $all->contains('category', $k)))
@php($cat = $cats->has(request('category')) ? request('category') : '')
<section class="wrap block"><h2 class="sr-only">Client results</h2>
@if ($cats->count() > 1)
<nav class="cs-filter" aria-label="Filter case studies by category" data-cs-filter>
<a href="/case-studies" data-cat=""@if (!$cat) aria-current="true"@endif>All <span>{{ $all->count() }}</span></a>
@foreach ($cats as $k => $l)<a href="/case-studies?category={{ $k }}" data-cat="{{ $k }}"@if ($cat === $k) aria-current="true"@endif>{{ $l }} <span>{{ $all->where('category', $k)->count() }}</span></a>@endforeach
</nav>
@endif
<div class="case-grid">@foreach ($all as $i => $d)@include('site.c.case-card', ['doc' => $d, 'index' => $i, 'hidden' => $cat && $d->category !== $cat])@endforeach</div></section>
@include('site.c.inquiry')
@endsection
