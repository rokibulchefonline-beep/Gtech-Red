{{-- Page builder widget preview for the panel (Page builder widgets). $s example section --}}
@extends('site.layout', ['focus' => true])
@section('title', 'Widget preview | GTech Digital')
@push('head')<meta name="robots" content="noindex, nofollow"><style>.cookie-banner,.cookie,[data-cookie]{display:none!important}</style>@endpush
@section('content')
@if (($s['type'] ?? '') === 'logos' && ! \App\Support\Site\Repo::clients())
<section class="sp-sec"><div class="wrap"><p style="text-align:center;color:#5b606b">No client logos are switched on yet (Website content &gt; Client logos), so this widget is hidden on the website.</p></div></section>
@else
@include('site.c.block', ['s' => $s, 'slug' => 'search-engine-optimization', 'name' => 'SEO'])
@endif
@endsection
