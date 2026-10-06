{{-- /services/{slug} (ServicePage.tsx). $p Page, $found [group,item]|null, $groupPage ServiceGroup|null --}}
@extends('site.layout')
@php($R = \App\Support\Site\Repo::class)
@php($name = $found['item']->name ?? $groupPage?->title ?? '')
@php($short = $p->name ?: $name)
@section('title', $p->meta_title)
@section('description', $p->meta_description)
@section('content')
@include('site.c.sp-hero', ['crumbs' => $found ? [[$found['group']->title, '/services/'.$found['group']->slug]] : [], 'second' => ['#case-studies', 'View Case Studies'], 'alt' => "$name results dashboard", 'icon' => $found['item']->icon ?? $groupPage?->icon ?? ''])
@foreach ($p->sections ?? [] as $s)@include('site.c.block', ['s' => $s, 'slug' => $p->slug, 'name' => $short])@endforeach
@include('site.c.faq', ['title' => "Frequently Asked Questions About $short", 'faqs' => $p->faqs ?? []])
<section class="sp-sec sp-grey"><div class="wrap">
@include('site.c.head', ['heading' => "Services Related to $short"])
<div class="sp-related">@foreach ($p->related ?? [] as $r)@php($it = $R::item($r)['item'] ?? null)@if ($it)@include('site.c.rel-card')@endif @endforeach</div>
</div></section>
@include('site.c.inquiry')
@endsection
