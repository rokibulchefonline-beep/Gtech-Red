{{-- /case-studies (app/case-studies/page.tsx) --}}
@extends('site.layout')
@section('title', 'Digital Marketing Case Studies | GTech Digital')
@section('description', 'GTech Digital case studies show measurable results from SEO, Google Ads, social media, web design and custom software projects for UK businesses.')
@section('content')
@include('site.c.page-head', ['title' => 'Digital Marketing Case Studies of GTech Digital', 'sub' => 'GTech Digital case studies show measurable results from SEO, Google Ads, social media, web design and custom software projects for UK businesses.'])
<section class="wrap block"><div class="case-grid">@foreach (\App\Support\Site\Repo::caseStudies() as $i => $d)@include('site.c.case-card', ['doc' => $d, 'index' => $i])@endforeach</div></section>
@endsection
