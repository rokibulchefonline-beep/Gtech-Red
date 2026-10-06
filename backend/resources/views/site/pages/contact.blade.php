{{-- /contact (app/contact/page.tsx). $p Page --}}
@extends('site.layout')
@php($next = collect($p->sections ?? [])->firstWhere('id', 'next') ?? ['heading' => '', 'steps' => []])
@section('title', $p->meta_title)
@section('description', $p->meta_description)
@section('content')
<section class="sp-hero compact">
<div class="wrap sp-hero-in">
<nav class="sp-crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><b>Contact</b></nav>
<h1>@hl($p->hero['h1'] ?? '')</h1>
<p class="sp-lead">@rt($p->hero['lead'] ?? '')</p>
<ul class="sp-hero-points">@foreach ($p->hero['points'] ?? [] as $pt)<li>@include('site.c.tick'){{ $pt }}</li>@endforeach</ul>
</div>
</section>
<section id="form" class="sp-sec contact-sec"><div class="wrap contact-grid">
@include('site.partials.contact-form', ['service' => (string) request()->query('service', '')])
<aside class="contact-aside">
<h3>Get in touch</h3>
<ul class="contact-ways">
<li><span class="sp-card-ico solid">@icon('lucide:phone', 20)</span><span><small>Call us</small><a href="tel:{{ preg_replace('/\s/', '', $site['phone']) }}">{{ $site['phone'] }}</a></span></li>
<li><span class="sp-card-ico solid">@icon('lucide:mail', 20)</span><span><small>Email us</small><a href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a></span></li>
<li><span class="sp-card-ico solid">@icon('lucide:clock', 20)</span><span><small>Office hours</small>Mon to Fri, 9am to 6pm</span></li>
<li><span class="sp-card-ico solid">@icon('lucide:map-pin', 20)</span><span><small>Where we work</small>Serving businesses across the UK</span></li>
</ul>
<h3>{{ $next['heading'] }}</h3>
<ol class="contact-next">@foreach ($next['steps'] ?? [] as $i => $n)<li><span>{{ $i + 1 }}</span><div><b>{{ $n['title'] }}</b><p>{{ $n['text'] }}</p></div></li>@endforeach</ol>
</aside>
</div></section>
@include('site.c.partner-strip')
@include('site.c.faq', ['title' => 'Frequently Asked Questions About Getting in Touch', 'faqs' => $p->faqs ?? []])
@endsection
