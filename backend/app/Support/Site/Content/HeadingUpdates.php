<?php

namespace App\Support\Site\Content;

use App\Models\Page;
use App\Support\Site\PageCache;
use App\Support\Site\Repo;

/**
 * H1s that start with each page's primary keyword in the form people search for ("Digital Marketing Services",
 * "Local SEO Services", "B2B Marketing Agency"). A heading only changes while it still has its old text, so edits
 * made in the panel stay. Safe to run again.
 */
class HeadingUpdates
{
    /** page key => [old H1, new H1] */
    public const H1 = [
        'page~home' => ['Digital Marketing|Agency [[for Scalable]]|[[Growth]]', 'Digital Marketing|Agency [[for Scalable]]|[[Growth]]'],
        'page~about' => ['About GTech Digital: Growing Businesses [[Since 2014]]', 'About GTech Digital, a London [[Digital Marketing Agency]]'],
        'page~contact' => ['Contact GTech Digital for a [[Free Proposal]]', 'Contact Our Digital Marketing Agency for a [[Free Proposal]]'],
        'page~industries-hub' => ['Marketing Built for [[Your Industry]]', 'Industry-Specific Digital Marketing for [[Your Sector]]'],
        'page~services-hub' => ['Digital Marketing, Web and Software [[Services]]', 'Digital Marketing, Web Design and Software Development [[Services]]'],

        'service~digital-marketing' => ['Digital Marketing That Drives [[Measurable Growth]]', 'Digital Marketing Services That Drive [[Measurable Growth]]'],
        'service~search-engine-optimization' => ['SEO That Turns Searches Into [[Customers]]', 'SEO Services That Turn Searches Into [[Customers]]'],
        'service~local-seo' => ['Local SEO That Puts You on the [[Map]]', 'Local SEO Services That Put You on the [[Map]]'],
        'service~ecommerce-seo' => ['Ecommerce SEO That Grows [[Online Sales]]', 'Ecommerce SEO Services That Grow [[Online Sales]]'],
        'service~google-ads' => ['Google Ads Management That Pays [[for Itself]]', 'Google Ads Management Services That Pay [[for Themselves]]'],
        'service~reputation-management' => ['Online Reputation Management That Builds [[Trust]]', 'Online Reputation Management Services That Build [[Trust]]'],
        'service~content-marketing' => ['Content Marketing That Builds [[Trust and Traffic]]', 'Content Marketing Services That Build [[Trust and Traffic]]'],
        'service~seo-backlinks' => ['Link Building That Earns [[Real Authority]]', 'Link Building Services That Earn [[Real Authority]]'],
        'service~digital-advertising' => ['Digital Advertising That Reaches [[the Right People]]', 'Digital Advertising Agency That Reaches [[the Right People]]'],
        'service~paid-media' => ['Paid Media That Brings [[Profitable Customers]]', 'Paid Media Agency That Brings [[Profitable Customers]]'],
        'service~restaurant-digital-marketing' => ['Restaurant Digital Marketing for [[Full Tables]] and More Direct Orders', 'Restaurant Digital Marketing Services for [[Full Tables]] and Direct Orders'],

        'service~social-media-marketing' => ['Social Media Marketing That Builds [[Real Engagement]]', 'Social Media Marketing Agency That Builds [[Real Engagement]]'],
        'service~facebook-marketing' => ['Facebook Marketing That Brings [[Leads and Sales]]', 'Facebook Marketing and Ads Agency That Brings [[Leads and Sales]]'],
        'service~instagram-marketing' => ['Instagram Marketing That Grows [[Your Brand]]', 'Instagram Marketing Services That Grow [[Your Brand]]'],
        'service~linkedin-marketing' => ['LinkedIn Marketing That Reaches [[Decision-Makers]]', 'LinkedIn Marketing Services That Reach [[Decision-Makers]]'],
        'service~tiktok-marketing' => ['TikTok Marketing That Gets You [[Seen]]', 'TikTok Marketing Agency That Gets You [[Seen]]'],
        'service~pinterest-marketing' => ['Pinterest Marketing That Inspires [[Buyers]]', 'Pinterest Marketing Services That Inspire [[Buyers]]'],

        'service~web-design-development' => ['Web Design and Development That [[Converts]]', 'Web Design and Development Company That [[Converts]]'],
        'service~website-design' => ['Website Design That Wins [[Customers]]', 'Website Design Services That Win [[Customers]]'],
        'service~wordpress-development' => ['WordPress Development Built [[to Perform]]', 'WordPress Development Services Built [[to Perform]]'],
        'service~php-development' => ['PHP Development That Is [[Fast and Secure]]', 'PHP Development Services for [[Fast, Secure]] Websites'],
        'service~cms-development' => ['CMS Development That Makes Editing [[Easy]]', 'CMS Development Services That Make Editing [[Easy]]'],
        'service~laravel-development' => ['Laravel Development for [[Reliable Web Apps]]', 'Laravel Development Services for [[Reliable Web Apps]]'],
        'service~website-maintenance' => ['Website Maintenance That Keeps You [[Fast and Secure]]', 'Website Maintenance Services That Keep Your Site [[Fast and Secure]]'],
        'service~ecommerce-development' => ['Ecommerce Development for Stores That [[Sell More]]', 'Ecommerce Development Services for Stores That [[Sell More]]'],

        'service~custom-software-development' => ['Custom Software Development Built [[Around Your Business]]', 'Custom Software Development Services Built [[Around Your Business]]'],
        'service~web-application-development' => ['Web Application Development That [[Scales With You]]', 'Web Application Development Services for Apps That [[Scale With You]]'],
        'service~mobile-app-development' => ['Mobile App Development People [[Love to Use]]', 'Mobile App Development Services for Apps People [[Love to Use]]'],
        'service~api-system-integration' => ['API and System Integration That [[Connects Everything]]', 'API and System Integration Services That [[Connect Everything]]'],
        'service~crm-erp-development' => ['Custom CRM and ERP Development That [[Fits How You Work]]', 'Custom CRM and ERP Development Services That [[Fit How You Work]]'],
        'service~saas-product-development' => ['SaaS Development From Idea to [[Paying Customers]]', 'SaaS Product Development From Idea to [[Paying Customers]]'],
        'service~mvp-development' => ['MVP Development That Gets You to Market [[Fast]]', 'MVP Development Services That Get You to Market [[Fast]]'],

        'service~branding-strategy' => ['Branding and Strategy for [[Brands That Lead]]', 'Branding and Strategy Services for [[Brands That Lead]]'],
        'service~branding' => ['Branding That Makes You [[Unforgettable]]', 'Branding Agency That Makes Your Brand [[Unforgettable]]'],
        'service~conversion-rate-optimization' => ['Conversion Rate Optimisation That Turns Visitors Into [[Leads]]', 'Conversion Rate Optimisation Services That Turn Visitors Into [[Leads]]'],
        'service~ui-ux-design' => ['Designs People Understand [[the First Time]]', 'UI/UX Design Agency for Products People [[Understand Instantly]]'],
        'service~print-media' => ['Print Media Design That [[Gets Noticed]]', 'Print Design Services That Get Your Brand [[Noticed]]'],

        'industry~automotive' => ['Automotive Marketing That Drives [[Showroom Visits]]', 'Automotive Marketing Agency That Drives [[Showroom Visits]]'],
        'industry~b2b-marketing' => ['B2B Marketing That Fills [[Your Pipeline]]', 'B2B Marketing Agency That Fills [[Your Pipeline]]'],
        'industry~e-commerce' => ['Ecommerce Marketing That Grows [[Repeat Sales]]', 'Ecommerce Marketing Agency That Grows [[Repeat Sales]]'],
        'industry~hospitality-hotels' => ['Hospitality Marketing That Brings [[Direct Bookings]]', 'Hospitality Marketing Agency That Brings [[Direct Bookings]]'],
        'industry~real-estate' => ['Property Marketing That Brings [[Buyers and Sellers]]', 'Property Marketing Agency That Brings [[Buyers and Sellers]]'],
        'industry~technology-saas' => ['SaaS Marketing That Grows [[Recurring Revenue]]', 'SaaS Marketing Agency That Grows [[Recurring Revenue]]'],
        'industry~travel' => ['Travel Marketing That Fills [[Your Bookings]]', 'Travel Marketing Agency That Fills [[Your Bookings]]'],
    ];

    /** First-round H1s (with "UK" or awkward grammar) => their corrected text. */
    private const CORRECTED = [
        'Digital Marketing|Agency UK [[for Scalable]]|[[Growth]]' => 'Digital Marketing|Agency [[for Scalable]]|[[Growth]]',
        'SEO Services UK That Turn Searches Into [[Customers]]' => 'SEO Services That Turn Searches Into [[Customers]]',
        'Branding Agency UK That Makes You [[Unforgettable]]' => 'Branding Agency That Makes Your Brand [[Unforgettable]]',
        'Website Maintenance Services That Keep You [[Fast and Secure]]' => 'Website Maintenance Services That Keep Your Site [[Fast and Secure]]',
        'PHP Development Services That Are [[Fast and Secure]]' => 'PHP Development Services for [[Fast, Secure]] Websites',
        'Print Design Services That [[Get Noticed]]' => 'Print Design Services That Get Your Brand [[Noticed]]',
        'Web Application Development Services That [[Scale With You]]' => 'Web Application Development Services for Apps That [[Scale With You]]',
    ];

    public static function apply(): void
    {
        foreach (self::H1 as $key => [$old, $new]) {
            $p = Page::query()->find($key);
            if (! $p) continue;
            $hero = (array) $p->hero;
            $cur = $hero['h1'] ?? '';
            if ($cur !== $old && (self::CORRECTED[$cur] ?? null) !== $new) continue;
            $hero['h1'] = $new;
            $p->hero = $hero;
            $p->save();
        }
        PageCache::flush();
        Repo::flush();
    }
}
