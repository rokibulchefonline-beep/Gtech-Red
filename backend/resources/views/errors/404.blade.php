{{-- Not found (app/not-found.tsx). --}}
@extends('site.layout')
@section('title', 'Page not found | GTech Digital')
@push('head')<meta name="robots" content="noindex">@endpush
@section('content')
@include('site.c.page-head', ['title' => 'Page not found'])
<section class="wrap block"><a class="btn" href="/">Back home</a></section>
@endsection
