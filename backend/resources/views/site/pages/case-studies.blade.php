{{-- /case-studies (app/case-studies/page.tsx) --}}
@extends('site.layout')
@php($desc = 'Real results from SEO, Google Ads, social media, web design and software projects: more traffic, leads, revenue and sales for UK businesses.')
@php($seo = \App\Support\Site\Seo::make('/case-studies', ['title' => 'Digital Marketing Case Studies | GTech Digital', 'absolute' => true, 'description' => $desc], [
    \App\Support\Site\Schema::page(['path' => '/case-studies', 'type' => 'CollectionPage', 'name' => 'Digital Marketing Case Studies of GTech Digital', 'description' => $desc, 'mainEntity' => \App\Support\Site\Schema::abs('/case-studies').'#list']),
    \App\Support\Site\Schema::breadcrumb('/case-studies', [['Case Studies', '/case-studies']]),
    \App\Support\Site\Schema::itemList('/case-studies', 'GTech Digital case studies', \App\Support\Site\Repo::caseStudies()->map(fn ($d) => [$d->title, "/case-studies/{$d->slug}"])->all()),
]))
@section('content')
@include('site.c.page-head', ['title' => 'Digital Marketing Case Studies and [[Results]]', 'sub' => 'GTech Digital case studies show measurable results from SEO, Google Ads, social media, web design and custom software projects for UK businesses.'])
<section class="wrap block"><h2 class="sr-only">Client results</h2><div class="case-grid">@foreach (\App\Support\Site\Repo::caseStudies() as $i => $d)@include('site.c.case-card', ['doc' => $d, 'index' => $i])@endforeach</div></section>
@endsection
