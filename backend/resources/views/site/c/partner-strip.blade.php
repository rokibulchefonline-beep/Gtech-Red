{{-- Platform partner badges (PartnerStrip.tsx). --}}
<section class="pstrip" aria-label="Our partners">
<div class="wrap">
<h2>@hl('Our [[Platform Partners]] and Certifications')</h2>
<div class="pstrip-row">@foreach (\App\Support\Site\Repo::partners() as $pt)<div class="pstrip-tile">@if ($pt['url'])<a href="{{ $pt['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $pt['name'] }}"><img src="{{ $pt['logo'] }}" alt="{{ $pt['name'] }}" loading="lazy"></a>@else<img src="{{ $pt['logo'] }}" alt="{{ $pt['name'] }}" loading="lazy">@endif</div>@endforeach</div>
</div>
</section>
