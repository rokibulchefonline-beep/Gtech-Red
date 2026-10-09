{{-- /services (app/services/page.tsx). $p Page --}}
@extends('site.layout')
@php($R = \App\Support\Site\Repo::class)
@php($info = require resource_path('data/services-hub.php'))
@php($sec = fn ($id) => collect($p->sections)->firstWhere('id', $id) ?? ['heading' => ''])
@php($all = $R::allItems())
@php(\App\Support\Site\Schema::$modified = $p->updated_at?->format('Y-m-d'))
@php($seo = \App\Support\Site\Seo::make('/services', ['title' => $p->meta_title, 'absolute' => true, 'description' => $p->meta_description], [
    \App\Support\Site\Schema::page(['path' => '/services', 'type' => 'CollectionPage', 'name' => 'GTech Digital Services', 'description' => $p->meta_description, 'mainEntity' => \App\Support\Site\Schema::abs('/services').'#list', 'about' => ['Digital marketing', 'Web design and development', 'Custom software development', 'Branding']]),
    \App\Support\Site\Schema::breadcrumb('/services', [['Services', '/services']]),
    \App\Support\Site\Schema::itemList('/services', 'GTech Digital services', $R::groups()->flatMap(fn ($g) => [[$g->title, "/services/{$g->slug}"], ...$g->items->map(fn ($it) => [$it->name, "/services/{$it->slug}"])->all()])->all()),
    \App\Support\Site\Schema::faq('/services', $p->faqs ?? []),
]))
@section('d-groups')
@include('site.c.toc-bar', ['class' => 'sh-tabs', 'label' => 'Service categories', 'items' => $R::groups()->map(fn ($g) => ['id' => $g->slug, 'label' => $sec("group-{$g->slug}")['nav'] ?? $g->title, 'icon' => $g->icon])->all()])
@foreach ($R::groups() as $n => $g)
@php($e = $sec("group-{$g->slug}"))
@php($title = $e['nav'] ?? $g->title)
@php($shown = collect($info[$g->slug]['cards'] ?? $g->items->pluck('slug')->all())->map(fn ($s) => $all[$s] ?? null)->filter()->take(6))
<section id="{{ $g->slug }}" class="sz {{ $n % 2 ? 'flip alt' : '' }}"><div class="wrap"><div class="sz-in">
<div class="sz-media"><img src="{{ $info[$g->slug]['image'] ?? '' }}" alt="{{ $title }} results dashboard" loading="lazy" width="800" height="600"></div>
<div class="sz-copy">
<h2>@hl($e['heading'] ?: "$title Services")</h2>
<p>@rt($e['paras'][0] ?? $g->intro ?? '')</p>
<ul class="sz-points">@foreach ($e['bullets'] ?? [] as $pt)<li>@include('site.c.tick')<span>@rt($pt)</span></li>@endforeach</ul>
<div class="sz-btns"><a class="sz-btn" href="/services/{{ $g->slug }}" aria-label="View all {{ $title }} services">View All {{ $g->items->count() }} Services @icon('lucide:arrow-right', 18)</a></div>
</div>
</div>
<div class="sz-grid">@foreach ($shown as $it)<a href="/services/{{ $it->slug }}" class="sz-card"><span class="sz-card-ico">@icon($it->icon, 22)</span><h3>{{ $it->name }}</h3><p>{{ $it->blurb }}</p><span class="sz-card-more">Learn more @icon('lucide:arrow-right', 16)</span></a>@endforeach</div>
</div></section>
@endforeach
@endsection
@section('d-why')
@php($why = $sec('why'))
<section class="sh-why"><div class="wrap">
<div class="sp-head center"><h2>@hl($why['heading'])</h2></div>
<div class="sh-why-grid">@foreach ($why['cards'] ?? [] as $w)<div class="sh-why-card"><span class="sp-card-ico solid">@icon($w['icon'], 22)</span><h3>{{ $w['title'] }}</h3><p>{{ $w['text'] }}</p></div>@endforeach</div>
</div></section>
@endsection
@section('d-process')
@include('site.c.block', ['slug' => 'services', 'name' => 'GTech Digital', 's' => ['type' => 'steps', 'id' => 'process', 'heading' => $sec('process')['heading'], 'steps' => $sec('process')['steps'] ?? []]])
@endsection
@section('d-faq')
@include('site.c.faq', ['title' => 'Frequently Asked Questions About Our Services', 'faqs' => $p->faqs ?? []])
@endsection
@section('d-inquiry')
@include('site.c.inquiry')
@endsection
@section('content')
<section class="sp-hero">
<div class="wrap sp-hero-in">
<nav class="sp-crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><b>Services</b></nav>
<h1>@hl($p->hero['h1'] ?? '')</h1>
<p class="sp-lead">@rt($p->hero['lead'] ?? '')</p>
<div class="sp-hero-btns">
<a class="sp-btn-red" href="/free-audit">Book a Free Audit</a>
<a class="sp-btn-line" href="#digital-marketing">Explore Services</a>
</div>
<ul class="sp-hero-points">@foreach ($p->hero['points'] ?? [] as $pt)<li>@include('site.c.tick'){{ $pt }}</li>@endforeach</ul>
<div class="sp-hero-show">
<img src="/services/hub.webp" alt="GTech Digital results across marketing, web and software" width="800" height="600">
<span class="sp-float a">@icon('lucide:layout-grid', 20)All Services</span>
<span class="sp-float b">@icon('lucide:trending-up', 20)Revenue-focused</span>
</div>
</div>
</section>
@include('site.c.layout', ['p' => $p, 'slug' => 'services', 'name' => 'GTech Digital'])
@endsection
