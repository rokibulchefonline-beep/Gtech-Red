{{-- /free-audit: the free marketing and website audit, with its own request form (leads with source "audit"). --}}
@extends('site.layout')
@php($faqs = [
    ['q' => 'Is the audit really free?', 'a' => 'Yes. There is no charge and no obligation to work with us. We do a limited number of audits each week so each one gets proper attention from a specialist, not just an automated report.'],
    ['q' => 'What do I get at the end?', 'a' => 'A short written report and a 30-minute call where we take you through it. It lists what is holding your website and marketing back, the quickest wins, and a rough idea of what each fix would take.'],
    ['q' => 'How long does it take?', 'a' => 'Most audits are ready within 3 to 5 working days of your request. We email you to book the call as soon as it is done.'],
    ['q' => 'What do you need from me?', 'a' => 'Just your website address and what you want to achieve. If you share read-only access to Google Analytics, Search Console or your ad accounts, we can go deeper, but it is optional.'],
    ['q' => 'Will you try to sell me something?', 'a' => 'We will tell you honestly what we would do and what it would cost if you asked us to help. Many businesses use the report to make the fixes themselves, and that is fine too.'],
])
@php($seo = \App\Support\Site\Seo::make('/free-audit', ['title' => 'Free Website and Marketing Audit | GTech Digital', 'absolute' => true,
    'description' => 'Get a free audit of your website, SEO, Google Ads and social media from a UK specialist. A clear report and a 30-minute call with the quickest wins, with no obligation.'], [
    \App\Support\Site\Schema::page(['path' => '/free-audit', 'name' => 'Free Website and Marketing Audit', 'description' => 'A free audit of your website, SEO, paid ads and social media, with a written report and a call.']),
    \App\Support\Site\Schema::breadcrumb('/free-audit', [['Free Audit', '/free-audit']]),
    \App\Support\Site\Schema::faq('/free-audit', $faqs),
]))
@php($goals = ['More leads or enquiries', 'More online sales', 'Rank higher on Google', 'Lower ad costs', 'A faster, better website', 'Grow on social media'])
@php($areas = ['Website and speed', 'SEO and Google rankings', 'Google Ads / paid search', 'Social media', 'Local SEO and Google Business Profile', 'Tracking and analytics'])
@section('content')
<section class="sp-hero compact">
<div class="wrap sp-hero-in">
<nav class="sp-crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><b>Free Audit</b></nav>
<p class="sp-hero-eyebrow">Free, no obligation</p>
<h1>@hl('Get a [[Free Audit]] of Your Website and Marketing')</h1>
<p class="sp-lead">A UK specialist reviews your website, search rankings, ads and social media, then shows you exactly what is holding you back and the quickest ways to win more customers.</p>
<ul class="sp-hero-points">@foreach (['Written report in 3 to 5 days', '30-minute walkthrough call', 'No obligation, no lock-in'] as $pt)<li>@include('site.c.tick'){{ $pt }}</li>@endforeach</ul>
</div>
</section>

<section id="request" class="sp-sec contact-sec"><div class="wrap contact-grid">
<form class="iq-form contact-form audit-form" data-form="audit"><h2 class="iq-form-title">Request your <span class="red">free audit</span></h2><div class="alert" hidden></div>
<input type="text" name="hp_field" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
<div class="cf-grid">
<div class="iq-field cf-wide">@icon('lucide:globe', 18)<input type="text" inputmode="url" name="website" required placeholder="Your website address*" aria-label="Your website address" autocomplete="url"></div>
<div class="iq-field">@icon('lucide:briefcase-business', 18)<input name="business" required placeholder="Business name*" aria-label="Business name" autocomplete="organization"></div>
<div class="iq-field">@icon('lucide:user', 18)<input name="name" required placeholder="Your name*" aria-label="Your name" autocomplete="name"></div>
<div class="iq-field">@icon('lucide:mail', 18)<input type="email" name="email" required placeholder="Email address*" aria-label="Email address" autocomplete="email"></div>
<div class="iq-field">@icon('lucide:phone', 18)<input type="tel" name="phone" required placeholder="Phone number*" aria-label="Phone number" autocomplete="tel"></div>
</div>
<fieldset class="au-pick"><legend>What do you want to achieve?</legend>@foreach ($goals as $g)<label><input type="checkbox" name="goals" value="{{ $g }}"><span>{{ $g }}</span></label>@endforeach</fieldset>
<fieldset class="au-pick"><legend>What should we look at first?</legend>@foreach ($areas as $a)<label><input type="checkbox" name="areas" value="{{ $a }}"><span>{{ $a }}</span></label>@endforeach</fieldset>
<div class="cf-grid">
<div class="iq-field cf-wide">@icon('lucide:wallet', 18)<select name="budget" aria-label="Monthly marketing budget"><option value="" selected>Monthly budget (optional)</option>@foreach ($budgets as $b)<option>{{ $b }}</option>@endforeach</select></div>
<div class="iq-field cf-wide">@icon('lucide:users', 18)<input name="competitors" placeholder="Main competitors, if you know them (websites or names)" aria-label="Main competitors"></div>
<div class="iq-field cf-wide">@icon('lucide:message-square', 18)<textarea name="message" rows="3" placeholder="Anything else we should know?" aria-label="Anything else we should know"></textarea></div>
</div>
@if ($recaptcha && ! $recaptchaV3)<div class="g-recaptcha" data-sitekey="{{ $recaptcha }}"></div>@endif
<button class="btn iq-submit" type="submit" data-label="Request my free audit">Request my free audit</button>@if ($formNotice)<p class="form-notice">{!! $formNotice !!}</p>@endif</form>
<aside class="contact-aside">
<h3>What you get</h3>
<ul class="contact-ways">
<li><span class="sp-card-ico solid">@icon('lucide:file-text', 20)</span><span><small>A written report</small>Clear findings in plain English, ranked by impact</span></li>
<li><span class="sp-card-ico solid">@icon('lucide:zap', 20)</span><span><small>Quick wins</small>Fixes you can make this month, with or without us</span></li>
<li><span class="sp-card-ico solid">@icon('lucide:video', 20)</span><span><small>A 30-minute call</small>A specialist takes you through it and answers questions</span></li>
<li><span class="sp-card-ico solid">@icon('lucide:shield-check', 20)</span><span><small>No obligation</small>Your data stays private and you owe us nothing</span></li>
</ul>
</aside>
</div></section>

@include('site.c.block', ['slug' => 'free-audit', 'name' => 'GTech Digital', 's' => ['type' => 'cards', 'id' => 'covers', 'heading' => 'What the [[Audit]] Covers',
    'intro' => 'We look at the same things your customers and Google see, and check how well each part turns visits into enquiries and sales.',
    'cards' => [
        ['icon' => 'lucide:gauge', 'title' => 'Website and speed', 'text' => 'Load times and Core Web Vitals, mobile use, broken pages, and how easy it is to call, enquire or buy.'],
        ['icon' => 'lucide:search', 'title' => 'SEO and rankings', 'text' => 'Which searches you rank for and miss, technical problems, content gaps and how you compare with your competitors.'],
        ['icon' => 'lucide:map-pin', 'title' => 'Local search', 'text' => 'Your Google Business Profile, reviews, map rankings and the local listings customers check before they call.'],
        ['icon' => 'lucide:mouse-pointer-click', 'title' => 'Google Ads and paid social', 'text' => 'Wasted spend, keywords and audiences, ad copy, landing pages and what each lead or sale really costs you.'],
        ['icon' => 'lucide:share-2', 'title' => 'Social media', 'text' => 'Which channels suit your customers, what is working in your content, and how competitors are reaching them.'],
        ['icon' => 'lucide:chart-line', 'title' => 'Tracking and analytics', 'text' => 'Whether calls, forms and sales are tracked correctly, so you can see which marketing actually pays.'],
    ]]])

@include('site.c.block', ['slug' => 'free-audit', 'name' => 'GTech Digital', 's' => ['type' => 'steps', 'id' => 'how', 'heading' => 'How the Free Audit Works',
    'steps' => [
        ['title' => 'Tell us about your business', 'text' => 'Send the form with your website and goals. It takes about two minutes.'],
        ['title' => 'We review everything', 'text' => 'A specialist checks your website, search, ads and social, and compares you with your competitors.'],
        ['title' => 'Get your report and call', 'text' => 'Within 3 to 5 working days you get the report and a 30-minute call to go through the quickest wins.'],
    ]]])

@include('site.c.faq', ['title' => 'Questions About the Free Audit', 'faqs' => $faqs])
@endsection
