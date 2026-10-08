{{-- /blogs: search (?q=), topic (?category=) and page (?page=), applied on the server. Each topic page and numbered
     page is its own indexable address (self-canonical); search results are not indexed. --}}
@extends('site.layout')
@php($B = \App\Support\Site\Blog::class)
@php($all = \App\Support\Site\Repo::posts())
@php($cats = $all->map(fn ($p) => $p->category ?: 'Insights')->unique()->values())
@php($q = (string) request()->query('q', ''))
@php($category = (string) request()->query('category', ''))
@php($term = mb_strtolower(trim($q)))
@php($list = $all->filter(fn ($p) => (!$category || $B::slugify($p->category ?: 'Insights') === $category) && (!$term || str_contains(mb_strtolower("{$p->title} {$p->excerpt} {$p->category}"), $term)))->values())
@php($filtered = $term !== '' || $category !== '')
@php($perPage = 12)
@php($page = max(1, (int) request()->query('page', 1)))
@php($pages = max(1, (int) ceil($list->count() / $perPage)))
@php(abort_if($page > $pages || (request()->has('page') && ! ctype_digit((string) request()->query('page'))), 404))
@php($featured = $filtered || $page > 1 ? null : ($list->firstWhere('featured', true) ?? $list->first()))
@php($ordered = $featured ? collect([$featured])->merge($list->reject(fn ($p) => $p->is($featured)))->values() : $list)
@php($rest = $ordered->slice(($page - 1) * $perPage, $perPage)->reject(fn ($p) => $featured && $p->is($featured))->values())
@php($catName = $cats->first(fn ($c) => $B::slugify($c) === $category))
@php($pageUrl = fn (int $n) => '/blogs'.(($qs = http_build_query(array_filter(['category' => $category, 'page' => $n > 1 ? $n : null]))) ? "?$qs" : ''))
@php($desc = 'Practical guides on SEO, Google Ads, social media, web design and software from the GTech Digital team, written for UK businesses.')
@php($seo = \App\Support\Site\Seo::make('/blogs', ['title' => ! $catName && $page === 1 ? 'GTech Digital Blog | SEO, Marketing, Web & Software Insights' : ($catName ? "$catName Articles" : 'Blog').($page > 1 ? " – Page $page" : '').' | GTech Digital', 'absolute' => true,
    'description' => $desc.($page > 1 ? " Page $page of $pages." : ''), 'canonical' => $term !== '' ? '/blogs' : $pageUrl($page), 'noindex' => $term !== ''], [
    \App\Support\Site\Schema::page(['path' => '/blogs', 'type' => ['CollectionPage', 'Blog'], 'name' => 'GTech Digital Blog', 'description' => $desc, 'mainEntity' => \App\Support\Site\Schema::abs('/blogs').'#list']),
    \App\Support\Site\Schema::breadcrumb('/blogs', [['Blog', '/blogs']]),
    \App\Support\Site\Schema::itemList('/blogs', 'GTech Digital blog posts', $all->take(30)->map(fn ($x) => [$x->title, "/blogs/{$x->slug}"])->values()->all()),
]))
@section('content')
<section class="bl-hero-wrap">
<div class="bl-hero">
<h1>Digital Marketing <span class="hl">Insights and Guides</span></h1>
<p>The GTech Digital blog shares practical guides on SEO, AI search, Google Ads, social media marketing, web design and custom software, written by our UK specialists.</p>
<form class="bl-search" action="/blogs" role="search">@if ($category)<input type="hidden" name="category" value="{{ $category }}">@endif @icon('lucide:search', 18)<label class="sr-only" for="bl-q">Search articles</label><input id="bl-q" name="q" value="{{ $q }}" placeholder="Search articles..."><button type="submit">Search</button></form>
<nav class="bl-cats" aria-label="Categories"><a href="/blogs" class="{{ !$category ? 'on' : '' }}">All Posts</a>@foreach ($cats as $c)<a href="/blogs?category={{ $B::slugify($c) }}" class="{{ $category === $B::slugify($c) ? 'on' : '' }}">{{ $c }}</a>@endforeach</nav>
</div>
</section>
<section class="wrap bl-main">
<div class="bl-list">
<h2 class="sr-only">{{ $catName ? "$catName articles" : ($term !== '' ? 'Search results' : 'Latest articles') }}</h2>
@if ($filtered)<p class="bl-results">{{ $list->count() }} {{ $list->count() === 1 ? 'article' : 'articles' }}{{ $catName ? " in $catName" : '' }}{{ $term ? " for “{$q}”" : '' }} · <a href="/blogs">Clear</a></p>@endif
@if ($featured)@include('site.c.post-card', ['p' => $featured, 'wide' => true])@endif
@if ($rest->count())<div class="bl-grid">@foreach ($rest as $p)@include('site.c.post-card', ['p' => $p, 'wide' => false])@endforeach</div>@endif
@if (!$list->count())<p class="bl-empty">No articles found. Try another search or browse all posts.</p>@endif
@if ($pages > 1 && $term === '')
<nav class="bl-pager" aria-label="Blog pages">
@if ($page > 1)<a href="{{ $pageUrl($page - 1) }}" rel="prev">@icon('lucide:arrow-left', 16) Newer</a>@endif
<ol>@foreach (range(1, $pages) as $n)@if ($n === 1 || $n === $pages || abs($n - $page) <= 2)<li>@if ($n === $page)<span aria-current="page">{{ $n }}</span>@else<a href="{{ $pageUrl($n) }}">{{ $n }}</a>@endif</li>@elseif (abs($n - $page) === 3)<li class="gap" aria-hidden="true">…</li>@endif @endforeach</ol>
@if ($page < $pages)<a href="{{ $pageUrl($page + 1) }}" rel="next">Older @icon('lucide:arrow-right', 16)</a>@endif
</nav>
@endif
</div>
<aside class="bl-side">
<div class="bl-box"><p class="bl-side-title">Popular Posts</p><ol class="bl-popular">@foreach ($all->take(4) as $i => $p)<li><span>{{ $i + 1 }}</span><a href="/blogs/{{ $p->slug }}">{{ $p->title }}</a></li>@endforeach</ol></div>
@include('site.c.newsletter')
<div class="bl-box"><p class="bl-side-title">Browse Topics</p><ul class="bl-topics">@foreach ($cats as $c)<li><a href="/blogs?category={{ $B::slugify($c) }}">{{ $c }}<span>{{ $all->filter(fn ($p) => ($p->category ?: 'Insights') === $c)->count() }}</span></a></li>@endforeach</ul></div>
</aside>
</section>
@endsection
