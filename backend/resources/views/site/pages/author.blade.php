{{-- /blogs/author/{slug}: an author's profile and articles (12 a page). $a Author --}}
@extends('site.layout')
@php($B = \App\Support\Site\Blog::class)
@php($S = \App\Support\Site\Schema::class)
@php($path = $a->path())
@php($posts = \App\Support\Site\Repo::posts()->where('author', $a->name)->values())
@php($perPage = 12)
@php($page = max(1, (int) request()->query('page', 1)))
@php($pages = max(1, (int) ceil($posts->count() / $perPage)))
@php(abort_if($page > $pages || (request()->has('page') && ! ctype_digit((string) request()->query('page'))), 404))
@php($list = $posts->slice(($page - 1) * $perPage, $perPage)->values())
@php($pageUrl = fn (int $n) => $path.($n > 1 ? "?page=$n" : ''))
@php($role = $a->job_title ? "{$a->job_title} at GTech Digital" : 'Author at GTech Digital')
@php($desc = \App\Support\Site\Seo::fillDescription((string) $a->bio, "Read articles by {$a->name}, $role, on SEO, marketing, web design and software for UK businesses."))
@php($seo = \App\Support\Site\Seo::make($path, ['title' => "{$a->name}, $role".($page > 1 ? " – Page $page" : ''), 'description' => $desc, 'canonical' => $pageUrl($page),
    'og' => ['type' => 'profile', 'image' => $a->photo ?: '']], [
    $S::page(['path' => $path, 'type' => 'ProfilePage', 'name' => $a->name, 'description' => $desc, 'mainEntity' => $S::abs($path).'#person']),
    $S::breadcrumb($path, [['Blog', '/blogs'], [$a->name, $path]]),
    $S::person($a),
]))
@section('content')
<section class="au-hero"><div class="wrap au-hero-in">
<nav class="sp-crumbs left" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><a href="/blogs">Blog</a><span>/</span><b>{{ $a->name }}</b></nav>
<div class="au-card">
@if ($a->photo)<img class="au-photo" src="{{ $a->photo }}" alt="{{ $a->name }}" width="140" height="140" fetchpriority="high">@else<span class="au-photo au-initial" aria-hidden="true">{{ mb_substr($a->name, 0, 1) }}</span>@endif
<div>
<h1>{{ $a->name }}</h1>
<p class="au-role">{{ $role }}</p>
@if ($a->bio)<p class="au-bio">{{ $a->bio }}</p>@endif
@if ($a->expertise)<ul class="au-topics" aria-label="Topics">@foreach ((array) $a->expertise as $t)<li>{{ $t }}</li>@endforeach</ul>@endif
@if ($a->profiles())<p class="au-links">@foreach (['linkedin' => ['LinkedIn', 'simple-icons:linkedin'], 'x' => ['X', 'simple-icons:x'], 'website' => ['Website', 'lucide:globe']] as $k => [$label, $ic])@if (preg_match('#^https://#', (string) $a->$k))<a href="{{ $a->$k }}" target="_blank" rel="noopener noreferrer me">@icon($ic, 16) {{ $label }}</a>@endif @endforeach</p>@endif
</div>
</div>
</div></section>
<section class="wrap bl-main au-main"><div class="bl-list">
<h2 class="au-h2">Articles by {{ $a->name }} <small>({{ $posts->count() }})</small></h2>
@if ($list->count())<div class="bl-grid">@foreach ($list as $p)@include('site.c.post-card', ['p' => $p, 'wide' => false])@endforeach</div>@else<p class="bl-empty">No articles yet.</p>@endif
@if ($pages > 1)
<nav class="bl-pager" aria-label="Article pages">
@if ($page > 1)<a href="{{ $pageUrl($page - 1) }}" rel="prev">@icon('lucide:arrow-left', 16) Newer</a>@endif
<ol>@foreach (range(1, $pages) as $n)@if ($n === 1 || $n === $pages || abs($n - $page) <= 2)<li>@if ($n === $page)<span aria-current="page">{{ $n }}</span>@else<a href="{{ $pageUrl($n) }}">{{ $n }}</a>@endif</li>@elseif (abs($n - $page) === 3)<li class="gap" aria-hidden="true">…</li>@endif @endforeach</ol>
@if ($page < $pages)<a href="{{ $pageUrl($page + 1) }}" rel="next">Older @icon('lucide:arrow-right', 16)</a>@endif
</nav>
@endif
</div></section>
@include('site.c.inquiry')
@endsection
