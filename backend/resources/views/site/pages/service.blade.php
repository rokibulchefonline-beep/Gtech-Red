{{-- /services/{slug} (ServicePage.tsx). $p Page, $found [group,item]|null, $groupPage ServiceGroup|null --}}
@extends('site.layout')
@php($R = \App\Support\Site\Repo::class)
@php($name = $found['item']->name ?? $groupPage?->title ?? '')
@php($short = $p->name ?: $name)
@php(\App\Support\Site\Schema::$modified = $p->updated_at?->format('Y-m-d'))
@php($path = "/services/{$p->slug}")
@php($kw = \App\Support\Site\Schema::keywords($p->slug))
@php($seo = \App\Support\Site\Seo::make($path, ['title' => $p->meta_title, 'absolute' => true, 'description' => $p->meta_description, 'og' => ['image' => $p->hero['motion'] ?? '']], [
    \App\Support\Site\Schema::page(['path' => $path, 'name' => trim(explode('|', $p->meta_title)[0]), 'description' => $p->meta_description, 'mainEntity' => \App\Support\Site\Schema::abs($path).'#service', 'about' => $kw?->ent, 'image' => $p->hero['motion'] ?? '']),
    \App\Support\Site\Schema::breadcrumb($path, [...($found ? [[$found['group']->title, '/services/'.$found['group']->slug]] : []), [$name, $path]]),
    \App\Support\Site\Schema::service(['path' => $path, 'slug' => $p->slug, 'name' => $name, 'description' => $p->meta_description, 'category' => $found['group']->title ?? null, 'related' => \App\Support\Site\Schema::relatedFor($p->slug, $p->related ?? [])]),
    \App\Support\Site\Schema::faq($path, $p->faqs ?? []),
]))
@section('content')
@include('site.c.sp-hero', ['crumbs' => $found ? [[$found['group']->title, '/services/'.$found['group']->slug]] : [], 'second' => ['#case-studies', 'View Case Studies'], 'alt' => "$name results dashboard", 'icon' => $found['item']->icon ?? $groupPage?->icon ?? ''])
@foreach ($p->sections ?? [] as $s)@include('site.c.block', ['s' => $s, 'slug' => $p->slug, 'name' => $short])@endforeach
@include('site.c.faq', ['title' => "Frequently Asked Questions About $short", 'faqs' => $p->faqs ?? []])
<section class="sp-sec sp-grey"><div class="wrap">
@include('site.c.head', ['heading' => "Services Related to $short"])
<div class="sp-related">@foreach ($p->related ?? [] as $r)@php($it = $R::item($r)['item'] ?? null)@if ($it)@include('site.c.rel-card')@endif @endforeach</div>
</div></section>
@include('site.c.inquiry', ['compact' => true, 'service' => $name])
@endsection
