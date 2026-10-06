{{-- Client logos in two auto-scrolling rows (BrandGrid.tsx). Same fixed-seed shuffle as the website. --}}
@php($shuffle = function (array $a, int $seed = 7) { $s = $seed; for ($i = count($a) - 1; $i > 0; $i--) { $s = ($s * 1664525 + 1013904223) % 4294967296; $j = $s % ($i + 1); [$a[$i], $a[$j]] = [$a[$j], $a[$i]]; } return $a; })
@php($logos = $shuffle(\App\Support\Site\Repo::clients()))
@php($half = (int) ceil(count($logos) / 2))
@php($rows = [array_slice($logos, 0, $half), array_slice($logos, $half) ?: $shuffle($logos, 11)])
@php($h = \App\Support\Site\Repo::homeSection('brands'))
<section class="brands">
<div class="wrap"><h2>@if (str_contains($h['heading'], '[['))@hl($h['heading'])@else{{ $h['heading'] }}@endif</h2></div>
@foreach ($rows as $r => $row)
@php($base = array_merge(...array_fill(0, max(1, (int) ceil(12 / max(1, count($row)))), $row)))
<div class="brand-marquee"><div class="brand-track{{ $r ? ' rev' : '' }}">@foreach (array_merge($base, $base) as $i => $b)<div class="brand-cell"@if ($i >= count($row)) aria-hidden="true"@endif><img src="{{ $b['logo'] }}" alt="{{ $i >= count($row) ? '' : $b['name'] }}" loading="lazy"></div>@endforeach</div></div>
@endforeach
</section>
