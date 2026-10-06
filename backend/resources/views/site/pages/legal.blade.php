{{-- /terms, /privacy-policy, /cookie-policy (LegalPage.tsx). $p Page --}}
@extends('site.layout')
@php($slug = fn ($s) => trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($s)), '-'))
@php($secs = $p->sections ?? [])
@php(\App\Support\Site\Schema::$modified = $p->updated_at?->format('Y-m-d'))
@php($seo = \App\Support\Site\Seo::make("/{$p->slug}", ['title' => $p->name, 'description' => $p->meta_description], [
    \App\Support\Site\Schema::page(['path' => "/{$p->slug}", 'name' => $p->name, 'description' => $p->meta_description, 'speakable' => false]),
    \App\Support\Site\Schema::breadcrumb("/{$p->slug}", [[$p->name, "/{$p->slug}"]]),
]))
@section('content')
<section class="sp-hero compact">
<div class="wrap sp-hero-in">
<nav class="sp-crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><b>{{ $p->name }}</b></nav>
<h1>@hl($p->name)</h1>
<p class="sp-lead">{{ $p->hero['lead'] ?? '' }}</p>
<p class="lg-updated">Last updated: {{ $p->data['updated'] ?? '' }}</p>
</div>
</section>
<div class="wrap lg-layout">
<aside><div class="bp-sticky">@include('site.c.toc', ['items' => array_map(fn ($s) => ['id' => $slug($s['heading']), 'text' => $s['heading']], $secs)])
<div class="bp-cta"><p class="bl-side-title light">Questions?</p><p>Contact us about this policy or your data at any time.</p><a class="bp-cta-btn" href="/contact">Contact Us</a></div>
</div></aside>
<article class="bp-body lg-body">
@foreach ($secs as $s)<section><h2 id="{{ $slug($s['heading']) }}">{{ $s['heading'] }}</h2>@foreach ($s['paras'] ?? [] as $t)<p>{{ $t }}</p>@endforeach @if (!empty($s['bullets']))<ul>@foreach ($s['bullets'] as $t)<li>{{ $t }}</li>@endforeach</ul>@endif @foreach ($s['after'] ?? [] as $t)<p>{{ $t }}</p>@endforeach</section>@endforeach
</article>
</div>
@endsection
