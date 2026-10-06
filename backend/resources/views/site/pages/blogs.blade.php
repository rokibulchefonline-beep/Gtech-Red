{{-- /blogs (app/blogs/page.tsx). Search (?q=) and category (?category=) are applied on the server. --}}
@extends('site.layout')
@php($B = \App\Support\Site\Blog::class)
@php($all = \App\Support\Site\Repo::posts())
@php($cats = $all->map(fn ($p) => $p->category ?: 'Insights')->unique()->values())
@php($q = (string) request()->query('q', ''))
@php($category = (string) request()->query('category', ''))
@php($term = mb_strtolower(trim($q)))
@php($list = $all->filter(fn ($p) => (!$category || $B::slugify($p->category ?: 'Insights') === $category) && (!$term || str_contains(mb_strtolower("{$p->title} {$p->excerpt} {$p->category}"), $term)))->values())
@php($filtered = $term !== '' || $category !== '')
@php($featured = $filtered ? null : ($list->firstWhere('featured', true) ?? $list->first()))
@php($rest = $list->reject(fn ($p) => $featured && $p->is($featured))->values())
@php($catName = $cats->first(fn ($c) => $B::slugify($c) === $category))
@section('title', 'Digital Marketing Blog | GTech Digital')
@section('description', 'The GTech Digital blog shares practical guides on SEO, AI search, Google Ads, social media marketing, web design and custom software, written by our UK specialists.')
@section('content')
<section class="bl-hero-wrap">
<div class="bl-hero">
<h1>Digital Marketing Blog of <span class="hl">GTech Digital</span></h1>
<p>The GTech Digital blog shares practical guides on SEO, AI search, Google Ads, social media marketing, web design and custom software, written by our UK specialists.</p>
<form class="bl-search" action="/blogs" role="search">@if ($category)<input type="hidden" name="category" value="{{ $category }}">@endif @icon('lucide:search', 18)<label class="sr-only" for="bl-q">Search articles</label><input id="bl-q" name="q" value="{{ $q }}" placeholder="Search articles..."><button type="submit">Search</button></form>
<nav class="bl-cats" aria-label="Categories"><a href="/blogs" class="{{ !$category ? 'on' : '' }}">All Posts</a>@foreach ($cats as $c)<a href="/blogs?category={{ $B::slugify($c) }}" class="{{ $category === $B::slugify($c) ? 'on' : '' }}">{{ $c }}</a>@endforeach</nav>
</div>
</section>
<section class="wrap bl-main">
<div class="bl-list">
@if ($filtered)<p class="bl-results">{{ $list->count() }} {{ $list->count() === 1 ? 'article' : 'articles' }}{{ $catName ? " in $catName" : '' }}{{ $term ? " for “{$q}”" : '' }} · <a href="/blogs">Clear</a></p>@endif
@if ($featured)@include('site.c.post-card', ['p' => $featured, 'wide' => true])@endif
@if ($rest->count())<div class="bl-grid">@foreach ($rest as $p)@include('site.c.post-card', ['p' => $p, 'wide' => false])@endforeach</div>@endif
@if (!$list->count())<p class="bl-empty">No articles found. Try another search or browse all posts.</p>@endif
</div>
<aside class="bl-side">
<div class="bl-box"><p class="bl-side-title">Popular Posts</p><ol class="bl-popular">@foreach ($all->take(4) as $i => $p)<li><span>{{ $i + 1 }}</span><a href="/blogs/{{ $p->slug }}">{{ $p->title }}</a></li>@endforeach</ol></div>
@include('site.c.newsletter')
<div class="bl-box"><p class="bl-side-title">Browse Topics</p><ul class="bl-topics">@foreach ($cats as $c)<li><a href="/blogs?category={{ $B::slugify($c) }}">{{ $c }}<span>{{ $all->filter(fn ($p) => ($p->category ?: 'Insights') === $c)->count() }}</span></a></li>@endforeach</ul></div>
</aside>
</section>
@endsection
