{{-- /industries/{slug} (IndustryPage.tsx). $p Page, $ind Industry --}}
@extends('site.layout')
@php($R = \App\Support\Site\Repo::class)
@php($name = $ind->name)
@section('title', $p->meta_title)
@section('description', $p->meta_description)
@section('content')
@include('site.c.sp-hero', ['crumbs' => [['Industries', '/industries']], 'second' => ['#services', 'See What We Do'], 'alt' => "$name marketing results dashboard", 'icon' => $ind->icon])
@foreach ($p->sections ?? [] as $s)@include('site.c.block', ['s' => $s, 'slug' => $p->slug, 'name' => $name])@endforeach
@include('site.c.faq', ['title' => "Frequently Asked Questions About {$p->name}", 'faqs' => $p->faqs ?? []])
<section class="sp-sec sp-grey"><div class="wrap">
@include('site.c.head', ['heading' => "Recommended Services for $name Businesses"])
<div class="sp-related">@foreach ($p->related ?? [] as $r)@php($it = $R::item($r)['item'] ?? null)@if ($it)@include('site.c.rel-card')@endif @endforeach</div>
<div class="ind-others">
<p>Other industries we serve:</p>
@foreach ($R::industries()->where('slug', '!=', $p->slug) as $o)<a href="/industries/{{ $o->slug }}">@icon($o->icon, 16){{ $o->name }}</a>@endforeach
</div>
</div></section>
@include('site.c.inquiry')
@endsection
