<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@isset($seo)
@include('site.c.seo-head')
@else
<title>@yield('title', $site['name'].' | '.$site['tagline'])</title>
<meta name="description" content="@yield('description', $site['tagline'])">
@endisset
@stack('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
{{-- Web fonts load without holding up the first paint (text shows in the fallback font for a moment). --}}
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap"></noscript>
<link rel="icon" href="/favicon.ico" sizes="any"><link rel="apple-touch-icon" href="/apple-touch-icon.png"><link rel="manifest" href="/site.webmanifest"><meta name="theme-color" content="#e8202f">
<link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ @filemtime(public_path('css/site.css')) }}">
{{-- Google Consent Mode v2 defaults: everything non-essential denied until the visitor opts in. --}}
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}window.gtag=gtag;gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'denied',wait_for_update:500});</script>
@if ($site['gtm'] || $site['ga4'] || $site['pixel'])
<script>
@if ($site['gtm'])(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s);j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ $site['gtm'] }}');
@endif
@if ($site['ga4'])var g=document.createElement('script');g.async=true;g.src='https://www.googletagmanager.com/gtag/js?id={{ $site['ga4'] }}';document.head.appendChild(g);gtag('js',new Date());gtag('config','{{ $site['ga4'] }}');
@endif
@if ($site['pixel'])!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src='https://connect.facebook.net/en_US/fbevents.js';s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script');fbq('init','{{ $site['pixel'] }}');fbq('track','PageView');
@endif
</script>
@endif
</head>
<body>
@if ($site['gtm'])<!-- Google Tag Manager (noscript) --><noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $site['gtm'] }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif
<div id="scroll-progress" aria-hidden="true"></div>
@unless (!empty($focus))@include('site.partials.header')@endunless
<main>@yield('content')</main>
@unless (!empty($focus))@include('site.partials.footer')@endunless
@include('site.partials.contact-modal')
@include('site.partials.cookie-banner')
<button type="button" class="to-top" aria-label="Back to top" tabindex="-1"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"></path></svg></button>
<a class="float-talk" href="/contact" aria-label="Let&#x27;s talk" title="Let&#x27;s talk">@icon('lucide:message-circle', 24)</a>
<script src="{{ asset('js/site.js') }}?v={{ @filemtime(public_path('js/site.js')) }}" defer></script>
@if ($recaptcha)<script>window.gtRecaptcha = {!! json_encode(['key' => $recaptcha, 'v3' => $recaptchaV3]) !!};</script><script src="https://www.google.com/recaptcha/api.js{{ $recaptchaV3 ? '?render='.$recaptcha : '' }}" async defer></script>@endif
@stack('scripts')
</body>
</html>
