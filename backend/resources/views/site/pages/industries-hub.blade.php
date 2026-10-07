{{-- /industries (app/industries/page.tsx). $p Page --}}
@extends('site.layout')
@php($R = \App\Support\Site\Repo::class)
@php($how = [['lucide:search', 'Sector research', 'We study your market, buyers and competitors first.'], ['lucide:shield-check', 'Compliance-aware', 'Campaigns that respect the rules of your industry.'], ['lucide:trending-up', 'Tracked to revenue', 'Every channel measured against leads and sales.']])
@php(\App\Support\Site\Schema::$modified = $p->updated_at?->format('Y-m-d'))
@php($seo = \App\Support\Site\Seo::make('/industries', ['title' => $p->meta_title, 'absolute' => true, 'description' => $p->meta_description], [
    \App\Support\Site\Schema::page(['path' => '/industries', 'type' => 'CollectionPage', 'name' => 'Industry Marketing Services of GTech Digital', 'description' => $p->meta_description, 'mainEntity' => \App\Support\Site\Schema::abs('/industries').'#list']),
    \App\Support\Site\Schema::breadcrumb('/industries', [['Industries', '/industries']]),
    \App\Support\Site\Schema::itemList('/industries', 'Industries GTech Digital serves', $R::industries()->map(fn ($i) => [$i->name, "/industries/{$i->slug}"])->all()),
]))
@section('content')
<section class="ih-hero">
<div class="wrap ih-hero-in">
<nav class="sp-crumbs left" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><b>Industries</b></nav>
<h1>@hl($p->hero['h1'] ?? '')</h1>
<p class="ih-lead">@rt($p->hero['lead'] ?? '')</p>
<ul class="ih-points">@foreach ($p->hero['points'] ?? [] as $pt)<li>@include('site.c.tick'){{ $pt }}</li>@endforeach</ul>
<div class="ih-btns"><a class="sp-btn-red" href="/contact">Book a Free Audit</a><a class="sp-btn-line light" href="#sectors">Explore Industries</a></div>
</div>
</section>
<section class="ih-how"><h2 class="sr-only">How we work with every sector</h2><div class="wrap ih-how-grid">@foreach ($how as [$ic, $t, $x])<div><span class="ih-how-ico">@icon($ic, 22)</span><h3>{{ $t }}</h3><p>{{ $x }}</p></div>@endforeach</div></section>
<section id="sectors" class="ih-sec"><div class="wrap">
<div class="ih-grid">@foreach ($R::industries() as $i)<a href="/industries/{{ $i->slug }}" class="ih-card"><span class="ih-img"><img src="/pages/industries/{{ $i->slug }}/growth.webp" alt="{{ $i->name }} marketing results dashboard" loading="lazy" width="800" height="600"><span class="ih-chip">@icon($i->icon, 18)</span></span><span class="ih-body"><h2>{{ $i->name }}</h2><p>{{ preg_replace('/<[^>]+>/', '', $R::page("industry~{$i->slug}")?->hero['lead'] ?? '') }}</p><span class="ih-more">Explore {{ $i->name }} @icon('lucide:arrow-right', 16)</span></span></a>@endforeach</div>
</div></section>
@include('site.c.inquiry')
@endsection
