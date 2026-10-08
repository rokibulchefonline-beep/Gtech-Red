{{-- /contact (app/contact/page.tsx). $p Page --}}
@extends('site.layout')
@php($next = collect($p->sections ?? [])->firstWhere('id', 'next') ?? ['heading' => '', 'steps' => []])
@php(\App\Support\Site\Schema::$modified = $p->updated_at?->format('Y-m-d'))
@php($seo = \App\Support\Site\Seo::make('/contact', ['title' => $p->meta_title, 'absolute' => true, 'description' => $p->meta_description], [
    \App\Support\Site\Schema::page(['path' => '/contact', 'type' => 'ContactPage', 'name' => 'Contact GTech Digital', 'description' => $p->meta_description, 'mainEntity' => \App\Support\Site\Schema::org()]),
    \App\Support\Site\Schema::breadcrumb('/contact', [['Contact', '/contact']]),
    \App\Support\Site\Schema::faq('/contact', $p->faqs ?? []),
]))
@section('d-form')
<section id="form" class="sp-sec contact-sec"><div class="wrap contact-grid">
@include('site.partials.contact-form', ['service' => (string) request()->query('service', '')])
<aside class="contact-aside">
<h3>Get in touch</h3>
<ul class="contact-ways">
@foreach ($contact['phones'] as $ph)<li><span class="sp-card-ico solid">@icon('lucide:phone', 20)</span><span><small>{{ $loop->first ? 'Call us' : 'Or call' }}</small><a href="tel:{{ $ph['tel'] }}">{{ $ph['label'] }}</a></span></li>@endforeach
@if ($contact['email'])<li><span class="sp-card-ico solid">@icon('lucide:mail', 20)</span><span><small>Email us</small><a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></span></li>@endif
@if ($contact['hours'])<li><span class="sp-card-ico solid">@icon('lucide:clock', 20)</span><span><small>Office hours</small>{{ $contact['hours'] }}</span></li>@endif
@if ($contact['showAddress'])<li><span class="sp-card-ico solid">@icon('lucide:map-pin', 20)</span><span><small>Visit us</small><address class="contact-addr">{!! implode('<br>', array_map('e', $contact['address'])) !!}</address>@if ($contact['mapsUrl'])<a class="contact-map" href="{{ $contact['mapsUrl'] }}" target="_blank" rel="noopener">Get directions @icon('lucide:arrow-up-right', 14)</a>@endif</span></li>
@else<li><span class="sp-card-ico solid">@icon('lucide:map-pin', 20)</span><span><small>Where we work</small>Serving businesses across the UK</span></li>@endif
</ul>
<h3>{{ $next['heading'] }}</h3>
<ol class="contact-next">@foreach ($next['steps'] ?? [] as $i => $n)<li><span>{{ $i + 1 }}</span><div><b>{{ $n['title'] }}</b><p>{{ $n['text'] }}</p></div></li>@endforeach</ol>
</aside>
</div></section>
@endsection
@section('d-partners')
@include('site.c.partner-strip')
@endsection
@section('d-faq')
@include('site.c.faq', ['title' => 'Frequently Asked Questions About Getting in Touch', 'faqs' => $p->faqs ?? []])
@endsection
@section('content')
<section class="sp-hero compact">
<div class="wrap sp-hero-in">
<nav class="sp-crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><b>Contact</b></nav>
<h1>@hl($p->hero['h1'] ?? '')</h1>
<p class="sp-lead">@rt($p->hero['lead'] ?? '')</p>
<ul class="sp-hero-points">@foreach ($p->hero['points'] ?? [] as $pt)<li>@include('site.c.tick'){{ $pt }}</li>@endforeach</ul>
</div>
</section>
@include('site.c.layout', ['p' => $p, 'slug' => 'contact', 'name' => 'GTech Digital'])
@endsection
