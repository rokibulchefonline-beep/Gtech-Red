<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Titles that fit in Google's ~60 characters and descriptions of 120-160 characters for the pages flagged by the SEO
 * report. A text is only replaced while it is still the original one, so edits made in the panel are kept.
 */
return new class extends Migration
{
    private const CHANGES = [
        'page~about' => [
            'meta_title' => ['About GTech Digital | UK Digital Marketing, Web & Software Agency', 'About GTech Digital | UK Digital Marketing & Software Agency'],
            'meta_description' => ['Meet GTech Digital, a UK agency combining digital marketing, web design and custom software under one roof, with certified specialists and a focus on measurable growth.',
                'Meet GTech Digital, a UK agency combining digital marketing, web design and custom software under one roof, with certified specialists focused on growth.'],
        ],
        'page~contact' => [
            'meta_title' => ['Contact GTech Digital | Get a Free Proposal | UK Digital Agency', 'Contact GTech Digital | Free Proposal from a UK Agency'],
        ],
        'page~industries-hub' => [
            'meta_title' => ['Industries We Serve | Sector Marketing & Software | GTech Digital', 'Industries We Serve | Sector Marketing | GTech Digital'],
            'meta_description' => ['GTech Digital helps UK businesses in ecommerce, healthcare, hospitality, property, finance, education, travel, automotive, B2B and SaaS grow with tailored marketing, websites and software.',
                'GTech Digital helps UK businesses in ecommerce, hospitality, property, travel, automotive, B2B and SaaS grow with tailored marketing, websites and software.'],
        ],
        'legal~cookie-policy' => [
            'meta_title' => ['', 'Cookie Policy: How GTech Digital Uses Cookies'],
            'meta_description' => ['What cookies are, which ones the GTech Digital website uses and how you can control them.',
                'What cookies are, which ones the GTech Digital website uses, why we use them, and how you can accept, refuse or change your cookie choices at any time.'],
        ],
        'legal~privacy-policy' => [
            'meta_title' => ['', 'Privacy Policy: How GTech Digital Handles Your Data'],
            'meta_description' => ['How GTech Digital collects, uses and protects your personal data, and the rights you have under UK data protection law.',
                'How GTech Digital collects, uses and protects your personal data, how long we keep it, and the rights you have under UK data protection law.'],
        ],
        'legal~terms' => [
            'meta_title' => ['', 'Terms and Conditions for GTech Digital Services'],
            'meta_description' => ['The terms that apply when you use the GTech Digital website and when you engage us for services.',
                'The terms that apply when you use the GTech Digital website and when you engage us for digital marketing, web design or software services in the UK.'],
        ],
    ];

    public function up(): void
    {
        foreach (self::CHANGES as $key => $fields) {
            $p = Page::query()->find($key);
            if (! $p) continue;
            foreach ($fields as $field => [$old, $new]) if ((string) $p->$field === $old) $p->$field = $new;
            if ($p->isDirty()) $p->save();
        }
        \App\Support\Site\PageCache::flush();
    }

    public function down(): void
    {
    }
};
