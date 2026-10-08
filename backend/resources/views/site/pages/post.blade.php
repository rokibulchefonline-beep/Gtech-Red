{{-- /blogs/{slug} (app/blogs/[slug]/page.tsx). $p Post --}}
@extends('site.layout')
@php($B = \App\Support\Site\Blog::class)
@php($R = \App\Support\Site\Repo::class)
@php($all = $R::posts())
@php($html = $B::isHtml($p))
@php($cat = $p->category ?: 'Insights')
@php($toc = $B::toc($p))
@php($others = $all->reject(fn ($x) => $x->slug === $p->slug))
@php($recent = $others->take(5))
@php($more = $others->filter(fn ($x) => ($x->category ?: 'Insights') === $cat)->concat($others->filter(fn ($x) => ($x->category ?: 'Insights') !== $cat))->take(3))
@php($url = config('gtech.public_url').'/blogs/'.$p->slug)
@php($mins = $B::readTime((string) $p->body, $html))
@php($path = "/blogs/{$p->slug}")
@php($date = ($p->date ?? $p->created_at)?->format('Y-m-d'))
@php($tags = array_values(array_filter((array) $p->tags)))
@php($t = $p->meta_title ?: $p->title)
@php($dsc = \App\Support\Site\Seo::fillDescription((string) ($p->meta_description ?: $p->excerpt), '', 0))
@php($by = $p->author ?: $B::AUTHOR)
@php($person = \App\Models\Author::forPost($p))
@php($modified = collect([$date, $p->updated_at?->format('Y-m-d')])->filter()->max())
@php($seo = \App\Support\Site\Seo::make($path, ['title' => $t, 'description' => $dsc, 'canonical' => $p->canonical ?: $path, 'noindex' => (bool) $p->noindex, 'keywords' => $tags, 'og' => ['type' => 'article', 'title' => $t, 'description' => $dsc, 'image' => $p->image ?: '/posts/default.webp', 'published' => $date, 'author' => $by]], [
    \App\Support\Site\Schema::page(['path' => $path, 'name' => $p->title, 'description' => (string) $p->excerpt, 'mainEntity' => \App\Support\Site\Schema::abs($path).'#article', 'image' => $p->image ?: '/posts/default.webp', 'published' => $date, 'modified' => $modified]),
    \App\Support\Site\Schema::breadcrumb($path, [['Blog', '/blogs'], [$p->title, $path]]),
    \App\Support\Site\Schema::article(['path' => $path, 'type' => 'BlogPosting', 'headline' => $p->title, 'description' => $dsc, 'image' => $p->image ?: '/posts/default.webp', 'published' => $date, 'modified' => $modified, 'section' => $cat, 'keywords' => $tags, 'words' => $B::words((string) $p->body, $html), 'author' => $by, 'person' => $person]),
    ...($person ? [\App\Support\Site\Schema::person($person)] : []),
]))
@section('content')
<header class="bp-top"><div class="wrap bp-top-in">
<div class="bp-top-copy">
<nav class="sp-crumbs left" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><a href="/blogs">Blog</a><span>/</span><b>{{ $cat }}</b></nav>
<a class="bl-tag" href="/blogs?category={{ $B::slugify($cat) }}">{{ $cat }}</a>
<h1>{{ $p->title }}</h1>
<p class="bp-excerpt">{{ $p->excerpt }}</p>
<div class="bp-meta">@if ($person?->photo)<img class="bp-avatar" src="{{ $person->photo }}" alt="" width="44" height="44">@else<span class="bp-avatar" aria-hidden="true">{{ mb_substr($by, 0, 1) }}</span>@endif<span><b>@if ($person)<a href="{{ $person->path() }}" rel="author">{{ $person->name }}</a>@else{{ $by }}@endif</b><small>@icon('lucide:calendar-days', 14)<time datetime="{{ $date }}">{{ $B::date($p) }}</time>@if ($modified > $date) · Updated <time datetime="{{ $modified }}">{{ \Illuminate\Support\Carbon::parse($modified)->format('j M Y') }}</time>@endif @icon('lucide:clock', 14){{ $mins }} min read</small></span></div>
@include('site.c.share', ['url' => $url, 'title' => $p->title])
</div>
<figure class="bp-top-img"><img src="{{ $p->image ?: '/posts/default.webp' }}" alt="{{ $p->image_alt ?: $p->title }}" width="1200" height="675" fetchpriority="high" decoding="async">@if ($p->image_caption)<figcaption class="bp-caption">{{ $p->image_caption }}</figcaption>@endif</figure>
</div></header>
<div class="wrap bp-layout">
<aside class="bp-left"><div class="bp-sticky">
@if ($toc)@include('site.c.toc', ['items' => $toc])@endif
<div class="bp-cta"><p class="bl-side-title light">Free Growth Audit</p><p>Find out what is holding your website and marketing back. Free, with no obligation.</p><a class="bp-cta-btn" href="/contact">Get My Free Audit</a></div>
</div></aside>
<article class="bp-body">
@if ($html)<div class="bp-html">{!! $B::captions(\App\Support\Site\Sanitizer::clean($p->body)) !!}</div>@else @include('site.c.post-body', ['blocks' => $B::parse((string) $p->body)])@endif
<div class="bp-share-end">@include('site.c.share', ['url' => $url, 'title' => $p->title])</div>
<section class="bp-author" aria-label="About the author">
@if ($person)
@if ($person->photo)<img class="bp-author-logo" src="{{ $person->photo }}" alt="{{ $person->name }}" width="72" height="72" loading="lazy">@else<span class="bp-author-logo" aria-hidden="true">{{ mb_substr($person->name, 0, 1) }}</span>@endif
<div>
<p class="bp-author-label">Written by</p>
<h2><a href="{{ $person->path() }}" rel="author">{{ $person->name }}</a></h2>
@if ($person->job_title)<p class="bp-author-role">{{ $person->job_title }}, GTech Digital</p>@endif
@if ($person->bio)<p>{{ $person->bio }}</p>@endif
<div class="bp-socials">@foreach (['linkedin' => ['LinkedIn', 'simple-icons:linkedin'], 'x' => ['X', 'simple-icons:x'], 'website' => ['website', 'lucide:globe']] as $k => [$label, $ic])@if (preg_match('#^https://#', (string) $person->$k))<a href="{{ $person->$k }}" target="_blank" rel="noopener noreferrer me" aria-label="{{ $person->name }} on {{ $label }}">@icon($ic, 16)</a>@endif @endforeach<a class="bp-author-more" href="{{ $person->path() }}">More from {{ \Illuminate\Support\Str::before($person->name, ' ') }} @icon('lucide:arrow-right', 14)</a></div>
</div>
@else
<span class="bp-author-logo" aria-hidden="true">G</span>
<div>
<p class="bp-author-label">Written by</p>
<h2>{{ $B::AUTHOR }}</h2>
<p>{{ $B::BIO }}</p>
<div class="bp-socials">@foreach ($site['socials'] as $s)<a href="{{ $s['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="GTech Digital on {{ $s['name'] }}">@icon($s['icon'], 16)</a>@endforeach</div>
</div>
@endif
</section>
</article>
<aside class="bp-right"><div class="bp-sticky">
<div class="bl-box"><p class="bl-side-title">Recent Posts</p><ul class="bp-recent">@foreach ($recent as $r)<li><a href="/blogs/{{ $r->slug }}"><img src="{{ $r->image ?: '/posts/default.webp' }}" alt="" width="96" height="54" loading="lazy"><span><b>{{ $r->title }}</b><small>{{ $B::date($r) }}</small></span></a></li>@endforeach</ul></div>
<div class="bl-box"><p class="bl-side-title">Our Services</p><ul class="bp-services">@foreach ($R::groups() as $g)<li><a href="/services/{{ $g->slug }}">@icon($g->icon, 18){{ $g->title }}</a></li>@endforeach</ul></div>
</div></aside>
</div>
@if ($more->count())
<section class="bp-more"><div class="wrap">
<h2>@hl('More Digital Marketing Guides')</h2>
<div class="bl-grid three">@foreach ($more as $m)@include('site.c.post-card', ['p' => $m, 'wide' => false])@endforeach</div>
</div></section>
@endif
@include('site.c.inquiry')
@endsection
