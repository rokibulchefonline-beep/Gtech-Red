<?php

namespace App\Support\Site;

use App\Models\Page;

/**
 * Readable H1s: each says what the visitor gets and keeps the page's main keyword, with no "UK" (GTech Digital is
 * UK-based but serves clients worldwide; location belongs in the paragraphs). An H1 changes only while it is still
 * an earlier template ("… Services of GTech Digital") or a known earlier wording, so edits made in the panel stay.
 */
class Headings
{
    public const H1 = [
        'service~search-engine-optimization' => 'SEO That Turns Searches Into [[Customers]]',
        'service~local-seo' => 'Local SEO That Puts You on the [[Map]]',
        'service~ecommerce-seo' => 'Ecommerce SEO That Grows [[Online Sales]]',
        'service~seo-backlinks' => 'Link Building That Earns [[Real Authority]]',
        'service~google-ads' => 'Google Ads Management That Pays [[for Itself]]',
        'service~paid-media' => 'Paid Media That Brings [[Profitable Customers]]',
        'service~digital-advertising' => 'Digital Advertising That Reaches [[the Right People]]',
        'service~digital-marketing' => 'Digital Marketing That Drives [[Measurable Growth]]',
        'service~content-marketing' => 'Content Marketing That Builds [[Trust and Traffic]]',
        'service~reputation-management' => 'Online Reputation Management That Builds [[Trust]]',
        'service~conversion-rate-optimization' => 'Conversion Rate Optimisation That Turns Visitors Into [[Leads]]',
        'service~social-media-marketing' => 'Social Media Marketing That Builds [[Real Engagement]]',
        'service~facebook-marketing' => 'Facebook Marketing That Brings [[Leads and Sales]]',
        'service~instagram-marketing' => 'Instagram Marketing That Grows [[Your Brand]]',
        'service~linkedin-marketing' => 'LinkedIn Marketing That Reaches [[Decision-Makers]]',
        'service~tiktok-marketing' => 'TikTok Marketing That Gets You [[Seen]]',
        'service~pinterest-marketing' => 'Pinterest Marketing That Inspires [[Buyers]]',
        'service~branding' => 'Branding That Makes You [[Unforgettable]]',
        'service~branding-strategy' => 'Branding and Strategy for [[Brands That Lead]]',
        'service~web-design-development' => 'Web Design and Development That [[Converts]]',
        'service~website-design' => 'Website Design That Wins [[Customers]]',
        'service~website-maintenance' => 'Website Maintenance That Keeps You [[Fast and Secure]]',
        'service~wordpress-development' => 'WordPress Development Built [[to Perform]]',
        'service~cms-development' => 'CMS Development That Makes Editing [[Easy]]',
        'service~ecommerce-development' => 'Ecommerce Development for Stores That [[Sell More]]',
        'service~custom-software-development' => 'Custom Software Development Built [[Around Your Business]]',
        'service~web-application-development' => 'Web Application Development That [[Scales With You]]',
        'service~mobile-app-development' => 'Mobile App Development People [[Love to Use]]',
        'service~saas-product-development' => 'SaaS Development From Idea to [[Paying Customers]]',
        'service~mvp-development' => 'MVP Development That Gets You to Market [[Fast]]',
        'service~laravel-development' => 'Laravel Development for [[Reliable Web Apps]]',
        'service~php-development' => 'PHP Development That Is [[Fast and Secure]]',
        'service~api-system-integration' => 'API and System Integration That [[Connects Everything]]',
        'service~crm-erp-development' => 'Custom CRM and ERP Development That [[Fits How You Work]]',
        'service~print-media' => 'Print Design That [[Gets Noticed]]',
        'service~ui-ux-design' => 'UI/UX Design People Understand [[the First Time]]',
        'industry~travel' => 'Travel Marketing That Fills [[Your Bookings]]',
        'industry~automotive' => 'Automotive Marketing That Drives [[Showroom Visits]]',
        'industry~e-commerce' => 'Ecommerce Marketing That Grows [[Repeat Sales]]',
        'industry~real-estate' => 'Property Marketing That Brings [[Buyers and Sellers]]',
        'industry~b2b-marketing' => 'B2B Marketing That Fills [[Your Pipeline]]',
        'industry~technology-saas' => 'SaaS Marketing That Grows [[Recurring Revenue]]',
        'industry~hospitality-hotels' => 'Hospitality Marketing That Brings [[Direct Bookings]]',
        'page~about' => 'About GTech Digital: Growing Businesses [[Since 2014]]',
        'page~contact' => 'Contact GTech Digital for a [[Free Proposal]]',
        'page~services-hub' => 'Digital Marketing, Web and Software [[Services]]',
        'page~industries-hub' => 'Marketing Built for [[Your Industry]]',
    ];

    private const EARLIER = [
        'SEO Services UK by [[GTech Digital]]',
        'Print Media Design That [[Gets Noticed]]',
        'Designs People Understand [[the First Time]]',
        'UI UX Design People Understand [[the First Time]]',
        'About [[GTech Digital]]: UK Digital Marketing, Web and Software Agency',
        'Contact [[GTech Digital]] for a Free Marketing Audit and Proposal',
        'Marketing, Web and Software Services of [[GTech Digital]]',
    ];

    public static function apply(): void
    {
        foreach (self::H1 as $key => $h1) {
            $p = Page::query()->find($key);
            if (! $p) continue;
            $hero = (array) $p->hero;
            $cur = (string) ($hero['h1'] ?? '');
            if ($cur === '' || str_ends_with($cur, ' of [[GTech Digital]]') || in_array($cur, self::EARLIER, true)) {
                $hero['h1'] = $h1;
                $p->hero = $hero;
                $p->save();
            }
        }
        PageCache::flush();
    }
}
