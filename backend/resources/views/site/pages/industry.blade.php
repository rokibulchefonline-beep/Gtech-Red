{{-- /industries/{slug} (IndustryPage.tsx). $p Page, $ind Industry --}}
@extends('site.layout')
@php($R = \App\Support\Site\Repo::class)
@php($name = $ind->name)
@php(\App\Support\Site\Schema::$modified = $p->updated_at?->format('Y-m-d'))
@php($path = "/industries/{$p->slug}")
@php($kw = \App\Support\Site\Schema::keywords($p->slug))
@php($title0 = trim(explode('|', $p->meta_title)[0]))
@php($seo = \App\Support\Site\Seo::make($path, ['title' => $p->meta_title, 'absolute' => true, 'description' => $p->meta_description, 'og' => ['image' => $p->hero['motion'] ?? '']], [
    \App\Support\Site\Schema::page(['path' => $path, 'name' => $title0, 'description' => $p->meta_description, 'mainEntity' => \App\Support\Site\Schema::abs($path).'#service', 'about' => $kw?->ent, 'image' => $p->hero['motion'] ?? '']),
    \App\Support\Site\Schema::breadcrumb($path, [['Industries', '/industries'], [$name, $path]]),
    \App\Support\Site\Schema::service(['path' => $path, 'slug' => $p->slug, 'name' => $title0, 'description' => $p->meta_description, 'category' => 'Industry marketing', 'audience' => "$name businesses", 'related' => \App\Support\Site\Schema::relatedFor($p->slug, $p->related ?? [])]),
    \App\Support\Site\Schema::faq($path, $p->faqs ?? []),
]))
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
