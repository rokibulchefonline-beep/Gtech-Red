{{-- /<slug>: landing pages built in the panel from the same sections as the service pages. $p Page --}}
@extends('site.layout')
@php($R = \App\Support\Site\Repo::class)
@php($d = (array) $p->data)
@php($name = $p->name)
@php($focus = !empty($d['focus']))
@php($service = ($d['service'] ?? '') ?: $name)
@php(\App\Support\Site\Schema::$modified = $p->updated_at?->format('Y-m-d'))
@php($path = $p->path)
@php($seo = \App\Support\Site\Seo::make($path, ['title' => $p->meta_title ?: $name, 'absolute' => (bool) $p->meta_title, 'description' => $p->meta_description, 'noindex' => !empty($d['noindex']), 'og' => ['image' => $p->hero['motion'] ?? '']], array_values(array_filter([
    \App\Support\Site\Schema::page(['path' => $path, 'name' => trim(explode('|', $p->meta_title ?: $name)[0]), 'description' => $p->meta_description, 'image' => $p->hero['motion'] ?? '']),
    \App\Support\Site\Schema::breadcrumb($path, [[$name, $path]]),
    !empty($p->faqs) ? \App\Support\Site\Schema::faq($path, $p->faqs) : null,
]))))
@section('content')
@include('site.c.sp-hero', ['crumbs' => [], 'second' => [($d['secondHref'] ?? '') ?: '#inquiry', ($d['secondLabel'] ?? '') ?: 'Get a Free Proposal'], 'alt' => ($p->hero['alt'] ?? '') ?: $name, 'icon' => $d['icon'] ?? '', 'cta' => ($d['cta'] ?? '') ?: 'Book a Free Audit', 'service' => $service])
@foreach ($p->sections ?? [] as $s)@include('site.c.block', ['s' => $s, 'slug' => $d['service_slug'] ?? $p->slug, 'name' => $service])@endforeach
@if (!empty($p->faqs))@include('site.c.faq', ['title' => ($d['faqTitle'] ?? '') ?: "Frequently Asked Questions About $name", 'faqs' => $p->faqs])@endif
@if (!empty($p->related))
<section class="sp-sec sp-grey"><div class="wrap">
@include('site.c.head', ['heading' => ($d['relatedTitle'] ?? '') ?: 'Related Services'])
<div class="sp-related">@foreach ($p->related as $r)@php($it = $R::item($r)['item'] ?? null)@if ($it)@include('site.c.rel-card')@endif @endforeach</div>
</div></section>
@endif
@include('site.c.inquiry')
@if (!empty($d['stickyCta']))
@php($tel = $contact['phones'][0]['tel'] ?? '')
<div class="lp-sticky" aria-label="Quick contact">
<a class="btn-red" href="{{ ($d['secondHref'] ?? '') ?: '#inquiry' }}">{{ ($d['cta'] ?? '') ?: 'Book a Free Audit' }}</a>
@if ($tel)<a class="btn-outline" href="tel:{{ $tel }}">@icon('lucide:phone', 16) Call</a>@endif
</div>
@endif
@endsection
