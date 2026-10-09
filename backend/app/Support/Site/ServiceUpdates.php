<?php

namespace App\Support\Site;

use App\Filament\Support\PageBlocks;
use App\Models\CaseStudy;
use App\Models\Page;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Revision;
use App\Models\SeoEntry;
use App\Models\SeoKeyword;
use App\Models\ServiceItem;
use App\Models\Testimonial;

/**
 * The service list changes made by the owner: Marketing Advisory is removed (its page, menu entry, keyword entry,
 * links from other pages and case studies; its address redirects to /services), and UI/UX Design and Print Media are
 * added under Branding & Strategy with their own service pages. Idempotent: it runs from the migration (existing
 * installs) and from gtech:seed-content (new installs), so both end in the same state.
 */
class ServiceUpdates
{
    private const REMOVE = ['marketing-advisory'];

    /** New services: the same sections as the other service pages, each with its own pictures and wording. */
    public static function added(): array
    {
        return [
            'restaurant-digital-marketing' => [
                'name' => 'Restaurant Digital Marketing', 'group' => 'digital-marketing', 'icon' => 'lucide:utensils-crossed', 'sort' => 3,
                'blurb' => 'Fill tables and grow direct orders with restaurant SEO, local search, Meta and Google Ads.',
                'meta_title' => 'Restaurant Digital Marketing Agency UK | SEO, Local SEO and Ads',
                'meta_description' => 'Restaurant digital marketing in the UK: restaurant SEO, Google Business Profile and local SEO, Meta and Google Ads that bring more bookings, walk-ins and commission-free online orders.',
                'keyword' => 'restaurant digital marketing', 'title' => 'Restaurant Digital Marketing Services', 'motion' => '/services/ind-hospitality-hotels.webp',
                'highlight' => 'Full Tables', 'h1' => 'Restaurant Digital Marketing for [[Full Tables]] and More Direct Orders',
                'lead' => 'We help restaurants, cafes and takeaways get found when hungry people search nearby, then turn those searches into bookings, walk-ins and online orders you keep the full margin on.',
                'points' => ['Restaurant SEO and Google Maps', 'Meta and Google Ads within your delivery area', 'More direct orders, less platform commission'],
                'related' => ['local-seo', 'google-ads', 'facebook-marketing', 'instagram-marketing', 'reputation-management', 'website-design'],
                'testimonials' => [],
                'industries' => [],
                'faqs' => [
                    ['q' => 'What does restaurant digital marketing include?', 'a' => 'It covers everything that brings diners to you online: your Google Business Profile and map ranking, your website and menu pages, reviews, Meta (Facebook and Instagram) ads, Google Ads, and the tracking that shows which of them brought bookings and orders. We run them as one plan for your restaurant.'],
                    ['q' => 'How do I get my restaurant to show up in "restaurants near me"?', 'a' => 'Google ranks nearby restaurants mostly on relevance, distance and prominence. You cannot change your distance, so we work on the rest: a complete Google Business Profile with the right categories, menu and photos, a steady flow of good reviews with replies, consistent name, address and phone everywhere, and a fast website with a page for each cuisine and area you serve.'],
                    ['q' => 'Can you help us take more orders without Deliveroo, Just Eat or Uber Eats?', 'a' => 'Yes. We keep you on the platforms where they bring new customers, and grow commission-free orders through your own website and ordering link: Google ordering buttons, Meta ads to past customers, and offers that move regulars to order direct.'],
                    ['q' => 'Are Meta ads or Google Ads better for a restaurant?', 'a' => 'They do different jobs. Google Ads catches people already searching, such as "pizza delivery near me" or "Indian restaurant Shoreditch". Meta ads put your food in front of local people before they search, which suits new openings, set menus and events. Most restaurants do best with a small, tightly targeted budget on both.'],
                    ['q' => 'How much should a restaurant spend on digital marketing?', 'a' => 'Many independent UK restaurants spend roughly 3 to 6 per cent of sales on marketing. A typical starting point is a monthly management fee plus an ad budget of a few hundred pounds, focused on a 2 to 5 mile radius. We size it to your covers, average spend and delivery area.'],
                    ['q' => 'How quickly will we see results?', 'a' => 'Ads can bring bookings and orders within days. Google Business Profile and review work usually shows in map rankings within 4 to 8 weeks, and restaurant SEO builds over 3 to 6 months.'],
                ],
                'sections' => [
                    ['type' => 'logos', 'id' => 'clients'],
                    ['type' => 'text', 'id' => 'what-is', 'heading' => 'What is [[restaurant digital marketing]]?',
                        'paras' => ['Most people choose where to eat on their phone, often within an hour of eating. They search "near me", check the map, scan photos and reviews, look at the menu, then book, order or walk in.',
                            'Restaurant digital marketing makes sure you are the obvious choice at each of those moments, and that the order or booking comes to you directly wherever possible.'],
                        'bullets' => ['Show up on Google Maps and in "near me" searches', 'Menus, photos and reviews that make people choose you', 'Ads that reach hungry people within your delivery radius', 'Bookings and orders tracked back to where they came from']],
                    ['type' => 'impact', 'id' => 'in-numbers', 'heading' => 'Restaurant marketing, in [[numbers]]', 'text' => 'What we focus on for every restaurant.',
                        'stats' => [['value' => '3', 'label' => 'map pack spots we aim for in your area'], ['value' => '2-5', 'label' => 'mile radius most ad budgets focus on'], ['value' => '0%', 'label' => 'commission on the direct orders we grow']]],
                    ['type' => 'media', 'id' => 'restaurant-seo', 'heading' => 'Restaurant SEO: be found for what people crave', 'image' => '/pages/seo/onpage.webp',
                        'alt' => 'Restaurant website page optimised for cuisine and area searches',
                        'paras' => ['People search by craving and place: "best ramen in Manchester", "halal burgers near me", "vegan brunch Brighton". We build pages for each cuisine, dish and area you serve, with your menu as real text Google can read rather than a PDF.',
                            'We add restaurant schema (opening hours, menu, price range, cuisine), speed up the site for phones, and earn local links from food guides and neighbourhood sites.'],
                        'bullets' => ['Cuisine, dish and area pages', 'Crawlable menu with restaurant schema', 'Fast mobile pages with clear Book and Order buttons'], 'flip' => false],
                    ['type' => 'media', 'id' => 'local-seo', 'heading' => 'Local SEO and Google Business Profile for restaurants', 'image' => '/pages/local-seo/gbp.webp',
                        'alt' => 'Google Business Profile for a restaurant with menu, photos, reviews and order button', 'tone' => 'grey',
                        'paras' => ['Your Google Business Profile is often seen more than your website. We set the right primary category, add your menu, dishes and fresh photos every week, turn on booking and ordering links, and post offers and events.',
                            'We also fix your listings on Apple Maps, Bing, TripAdvisor and food directories so your name, address and phone match everywhere, which helps you climb the map pack.'],
                        'bullets' => ['Profile categories, menu and attributes', 'Weekly photos and posts', 'Consistent citations across maps and directories']],
                    ['type' => 'media', 'id' => 'reviews-growth', 'heading' => 'Reviews that win the table', 'image' => '/pages/local-seo/reviews.webp',
                        'alt' => 'Restaurant reviews dashboard with ratings and replies', 'flip' => true,
                        'paras' => ['Diners compare star ratings before they compare menus. We set up a simple way for happy guests to leave a Google review (QR codes on bills and follow-up messages), and reply to every review in your voice, so a bad night does not define you.'],
                        'bullets' => ['QR codes and follow-ups for more reviews', 'Replies to every review', 'Alerts for negative reviews the same day']],
                    ['type' => 'media', 'id' => 'meta-ads', 'heading' => 'Meta ads: put your food in front of local diners', 'image' => '/pages/facebook/ads.webp',
                        'alt' => 'Facebook and Instagram ads for a restaurant targeted to a local radius', 'tone' => 'grey',
                        'paras' => ['Facebook and Instagram are where food sells on looks. We run short video and photo ads of your best dishes to people within a few miles, timed for lunch and dinner, with offers for new openings, set menus, events and quiet nights.',
                            'Past customers and website visitors see reminder ads that bring them back to order direct.'],
                        'bullets' => ['Radius and time-of-day targeting', 'Dish videos and Reels', 'Retargeting past customers to order direct']],
                    ['type' => 'media', 'id' => 'google-ads', 'heading' => 'Google Ads for bookings and orders right now', 'image' => '/pages/google-ads/search.webp',
                        'alt' => 'Google search ad for a restaurant with call and directions buttons', 'flip' => true,
                        'paras' => ['When someone searches "takeaway near me" at 7pm, they are ready to order. Google Ads puts you at the top with call, directions and order buttons, limited to your delivery area and opening hours so no budget is wasted when you are closed.',
                            'Performance Max and local campaigns add Google Maps and YouTube, and every call, booking and order is tracked.'],
                        'bullets' => ['Search ads for high-intent food searches', 'Ads only during opening hours', 'Calls, bookings and orders tracked']],
                    ['type' => 'cards', 'id' => 'services', 'heading' => 'What is [[included]] for your restaurant', 'cards' => [
                        ['icon' => 'lucide:search', 'title' => 'Restaurant SEO', 'text' => 'Cuisine and area pages, a crawlable menu and restaurant schema.'],
                        ['icon' => 'lucide:map-pin', 'title' => 'Local SEO', 'text' => 'Google Business Profile, map pack rankings and citations.'],
                        ['icon' => 'lucide:star', 'title' => 'Reviews', 'text' => 'More Google reviews and a reply to every one.'],
                        ['icon' => 'simple-icons:meta', 'title' => 'Meta ads', 'text' => 'Facebook and Instagram ads for local diners and past customers.'],
                        ['icon' => 'simple-icons:googleads', 'title' => 'Google Ads', 'text' => 'Search and Maps ads for people ready to book or order.'],
                        ['icon' => 'lucide:shopping-bag', 'title' => 'Direct ordering', 'text' => 'More commission-free orders through your own website.'],
                        ['icon' => 'lucide:camera', 'title' => 'Food content', 'text' => 'Photos, short videos and posts that make your dishes sell.'],
                        ['icon' => 'lucide:chart-line', 'title' => 'Tracking and reports', 'text' => 'Bookings, orders and calls by channel, every month.'],
                    ]],
                    ['type' => 'steps', 'id' => 'process', 'heading' => 'How we grow your restaurant, [[step by step]]', 'steps' => [
                        ['title' => 'Audit', 'text' => 'Your profile, website, reviews, ads and competitors nearby.'],
                        ['title' => 'Fix the basics', 'text' => 'Google Business Profile, menu, tracking and booking links.'],
                        ['title' => 'Launch ads', 'text' => 'Meta and Google Ads within your radius and opening hours.'],
                        ['title' => 'Grow', 'text' => 'SEO pages, reviews and content every month.'],
                        ['title' => 'Report', 'text' => 'Bookings, orders and cost per cover, in plain English.'],
                    ]],
                    ['type' => 'cases', 'id' => 'case-studies', 'heading' => 'Results from our [[marketing]] work', 'service' => '*'],
                    ['type' => 'table', 'id' => 'compare', 'heading' => 'Delivery apps or direct orders: where should your marketing send people?',
                        'columns_text' => ' | Delivery apps | Your own website and ordering',
                        'rows_text' => "Commission per order | Typically 15 to 35 per cent | None, only card fees\nCustomer data | Kept by the platform | Yours, for offers and repeat orders\nNew customers | Good for discovery | Grows with SEO, ads and reviews\nBest use | A source of new diners | Where regulars should order",
                        'note' => 'Most restaurants keep the apps for discovery and use marketing to move regulars to order direct.'],
                    ['type' => 'cards', 'id' => 'pricing', 'heading' => 'What shapes the cost of [[restaurant marketing]]', 'cards' => [
                        ['icon' => 'lucide:store', 'title' => 'Number of sites', 'text' => 'One restaurant or several locations.'],
                        ['icon' => 'lucide:radius', 'title' => 'Area', 'text' => 'Your delivery radius and how busy your area is.'],
                        ['icon' => 'lucide:megaphone', 'title' => 'Ad budget', 'text' => 'How much goes to Meta and Google each month.'],
                        ['icon' => 'lucide:camera', 'title' => 'Content', 'text' => 'Whether we shoot food photos and videos for you.'],
                    ]],
                    ['type' => 'reviews', 'id' => 'reviews', 'heading' => 'What our [[clients]] say'],
                ],
            ],
            'ui-ux-design' => [
                'name' => 'UI/UX Design', 'group' => 'branding-strategy', 'icon' => 'lucide:layout-dashboard', 'sort' => 3,
                'blurb' => 'Research-led interfaces that are easy to use and turn visitors into customers.',
                'meta_title' => 'UI/UX Design Agency UK | Website and App Design',
                'meta_description' => 'UK UI/UX design agency for websites and apps. User research, wireframes, prototypes and usability testing that make your product easier to use and convert better.',
                'keyword' => 'UI UX design', 'title' => 'UI/UX Design for Websites and Apps', 'motion' => '/services/uiux.webp',
                'highlight' => 'the First Time', 'h1' => 'Designs People Understand [[the First Time]]',
                'lead' => 'We design websites and apps around how real people think and act. We map the journey, prototype the screens and test them with users before a line of code is written.',
                'points' => ['User research and journey mapping', 'Wireframes and clickable prototypes', 'Usability testing with real users'],
                'related' => ['website-design', 'web-application-development', 'conversion-rate-optimization', 'mobile-app-development', 'saas-product-development'],
                'testimonials' => ['Imran K'],
                'industries' => [
                    ['e-commerce', 'Product pages and checkout flows that cut abandoned baskets.'],
                    ['technology-saas', 'Onboarding and dashboards that keep users coming back.'],
                    ['real-estate', 'Property search and enquiry forms that are quick on a phone.'],
                    ['travel', 'Booking journeys that make the next step obvious.'],
                    ['hospitality-hotels', 'Room booking and menu pages that work on any device.'],
                    ['b2b-marketing', 'Demo request and lead forms that people actually finish.'],
                ],
                'faqs' => [
                    ['q' => 'What is the difference between UI and UX design?', 'a' => 'UX design is how a product works: the steps a visitor takes, what they see first and whether they can finish what they came to do. UI design is how it looks and responds: layout, colour, type and the buttons they press. We design both together so the look supports the experience.'],
                    ['q' => 'Do you design for both websites and apps?', 'a' => 'Yes. We design websites, web applications and mobile apps, and we check that each design works on phones, tablets and desktops.'],
                    ['q' => 'How many people do you test with?', 'a' => 'Usually five people per round, and most projects run at least two rounds. Five users reveal most of the common problems in a design, and each round shows whether the fixes worked.'],
                    ['q' => 'Can you improve an existing website or app?', 'a' => 'Yes. We start by seeing how people use what you have now, find the points where they get stuck, and redesign those parts first. Every change is tested before it is built.'],
                    ['q' => 'Will you hand the designs to our developers?', 'a' => 'Yes. You receive the final designs, a design system and specifications the developers can build from. We can also work alongside your developers during the build.'],
                    ['q' => 'How long does a UI/UX project take?', 'a' => 'It depends on the scope. A single landing page can take a few weeks and a full web application several months. We agree a timeline in the proposal before work starts.'],
                ],
                'sections' => [
                    ['type' => 'logos', 'id' => 'clients'],
                    ['type' => 'text', 'id' => 'what-is-ux', 'heading' => 'What is UI/UX design, in [[plain English]]?',
                        'paras' => ['UX design is how a product works: the steps a visitor takes, what they see first, and whether they can finish what they came to do. UI design is how it looks and responds: layout, colour, type and the buttons they press.',
                            'Good design is not decoration. It removes the questions that stop people buying, booking or signing up.'],
                        'bullets' => ['Navigation people understand without help', 'Forms that ask only for what is needed', 'Buttons and messages that say what happens next']],
                    ['type' => 'impact', 'id' => 'in-numbers', 'heading' => 'How we design, in [[numbers]]', 'text' => 'These are the standards every UI/UX project follows.',
                        'stats' => [['value' => '5', 'label' => 'users in each usability test round'], ['value' => '3', 'label' => 'rounds of testing before we build'], ['value' => '100%', 'label' => 'of designs tested with users before development']]],
                    ['type' => 'media', 'id' => 'journey', 'heading' => 'Mapping the journey to find where people drop off', 'image' => '/pages/ux/journey.webp',
                        'alt' => 'User journey map with five stages, each with the problem a visitor meets there',
                        'paras' => ['We follow a visitor from the first search result to the enquiry or sale, and note every point where they hesitate, get lost or leave.', 'Each problem becomes a design task with a clear owner and priority.'],
                        'bullets' => ['Personas based on real customer research', 'Stage-by-stage problem list', 'A prioritised list of fixes'], 'flip' => false],
                    ['type' => 'media', 'id' => 'prototype', 'heading' => 'From wireframe to a prototype people can click', 'image' => '/pages/ux/wire.webp',
                        'alt' => 'A wireframe layout beside the finished prototype screen', 'tone' => 'grey',
                        'paras' => ['We start with simple wireframes to agree the structure. Then we design a clickable prototype in your brand, so you can see the real experience before any development starts.']],
                    ['type' => 'media', 'id' => 'testing', 'heading' => 'Usability testing: watch real people use it', 'image' => '/pages/ux/test.webp',
                        'alt' => 'Usability test results showing where users click and how many finish the task', 'flip' => true,
                        'paras' => ['Five people try the prototype on set tasks while we watch and listen. We record where they click, where they stop and what they say.', 'We fix what they struggle with, then test again.'],
                        'bullets' => ['Task success rate', 'Time to complete each task', 'Where people hesitate']],
                    ['type' => 'media', 'id' => 'system', 'heading' => 'A design system so every screen stays consistent', 'image' => '/pages/ux/system.webp',
                        'alt' => 'Design system with brand colours, the type scale, buttons and form fields', 'tone' => 'grey',
                        'paras' => ['Colours, type, buttons and form fields are set up once and reused. Your developers build from the same parts, so the product stays consistent as it grows.']],
                    ['type' => 'cards', 'id' => 'services', 'heading' => 'What is [[included]] in a UI/UX project', 'cards' => [
                        ['icon' => 'lucide:users', 'title' => 'User research', 'text' => 'Interviews, a review of your analytics and the questions your customers ask most.'],
                        ['icon' => 'lucide:map', 'title' => 'Journey mapping', 'text' => 'The stages a visitor goes through, with the points where they hesitate.'],
                        ['icon' => 'lucide:pen-tool', 'title' => 'Wireframes and prototypes', 'text' => 'Structure first, then a clickable prototype in your brand.'],
                        ['icon' => 'lucide:flask-conical', 'title' => 'Usability testing', 'text' => 'Real people try the design, and we change it based on what they do.'],
                        ['icon' => 'lucide:layout-dashboard', 'title' => 'Design system', 'text' => 'Reusable parts and a style guide for your developers.'],
                        ['icon' => 'lucide:book-open', 'title' => 'Handover pack', 'text' => 'Final files and specifications, so the build matches the design.'],
                    ]],
                    ['type' => 'steps', 'id' => 'process', 'heading' => 'Our UI/UX process, [[step by step]]', 'steps' => [
                        ['title' => 'Discover', 'text' => 'Interviews, analytics and a review of what you have now.'],
                        ['title' => 'Map', 'text' => 'Journeys and problems, agreed with you before any screens are drawn.'],
                        ['title' => 'Design', 'text' => 'Wireframes, then a clickable prototype in your brand.'],
                        ['title' => 'Test', 'text' => 'Five users try the prototype and we refine what they struggle with.'],
                        ['title' => 'Hand over', 'text' => 'Final designs, a design system and specifications for your developers.'],
                    ]],
                    ['type' => 'cases', 'id' => 'case-studies', 'heading' => 'Results from our [[design and marketing]] work', 'service' => '*'],
                    ['type' => 'table', 'id' => 'compare', 'heading' => 'UI/UX design, website design or a usability audit: which do you need?',
                        'columns_text' => ' | UI/UX design | Website design | Usability audit',
                        'rows_text' => "Main goal | Make it easy to use and finish tasks | Build a new, good-looking website | Find what is stopping people today\nYou get | Tested prototypes and a design system | A built website | A report with prioritised fixes\nBest when | Launching or redesigning a product | Starting a new site from scratch | Your current site is not converting",
                        'note' => 'Not sure which one you need? Start with a usability audit. It costs less and shows what to fix first.'],
                    ['type' => 'cards', 'id' => 'pricing', 'heading' => 'What shapes the price of [[UI/UX design]]', 'cards' => [
                        ['icon' => 'lucide:layers', 'title' => 'Scope', 'text' => 'How many pages, screens or user journeys we design.'],
                        ['icon' => 'lucide:users', 'title' => 'Research', 'text' => 'Whether we interview users or work from your analytics and feedback.'],
                        ['icon' => 'lucide:flask-conical', 'title' => 'Testing', 'text' => 'How many rounds of testing and how many people take part.'],
                        ['icon' => 'lucide:layout-dashboard', 'title' => 'Design system', 'text' => 'Whether we build a reusable system or design the screens only.'],
                    ]],
                    ['type' => 'reviews', 'id' => 'reviews', 'heading' => 'What our [[clients]] say'],
                    ['type' => 'industries', 'id' => 'industries', 'heading' => 'UI/UX design for your [[sector]]'],
                ],
            ],
            'print-media' => [
                'name' => 'Print Media', 'group' => 'branding-strategy', 'icon' => 'lucide:newspaper', 'sort' => 4,
                'blurb' => 'Brochures, stationery, posters and packaging that look as good in hand as on screen.',
                'meta_title' => 'Print Design Agency UK | Brochures, Stationery and Packaging',
                'meta_description' => 'UK print media design for brochures, flyers, business cards, posters, signage and packaging. Print-ready files, colour-checked and delivered on time.',
                'keyword' => 'print design', 'title' => 'Print Media Design for Your Brand', 'motion' => '/services/print.webp',
                'highlight' => 'Gets Noticed', 'h1' => 'Print Media Design That [[Gets Noticed]]',
                'lead' => 'Brochures, stationery, posters and packaging designed for the place each piece will be used, with print-ready files set up correctly for your printer.',
                'points' => ['Brochures and flyers', 'Business cards and stationery', 'Posters, signage and packaging'],
                'related' => ['branding', 'branding-strategy', 'digital-advertising', 'content-marketing'],
                'testimonials' => ['James T'],
                'industries' => [
                    ['hospitality-hotels', 'Menus, table cards and event posters.'],
                    ['real-estate', 'Property brochures and board-ready signs.'],
                    ['automotive', 'Showroom posters, brochures and offer leaflets.'],
                    ['e-commerce', 'Product packaging and shipping labels.'],
                    ['b2b-marketing', 'Trade show banners and sales sheets.'],
                    ['travel', 'Travel brochures and destination posters.'],
                ],
                'faqs' => [
                    ['q' => 'Do you handle printing as well as design?', 'a' => 'We design the files and can recommend trusted UK printers and help you choose paper and finishes. You can order directly, or we can place the order for you.'],
                    ['q' => 'What file do I need to send to the printer?', 'a' => 'A print-ready PDF at the finished size, with bleed, crop marks and the right colour mode. We prepare this for you, so there are no surprises at the proof stage.'],
                    ['q' => 'Can you match my existing brand?', 'a' => 'Yes. If you have a brand guide we follow it. If not, we start with a short review so your print matches your website and social media.'],
                    ['q' => 'How many proofs will I see?', 'a' => 'Three across a project: a concept, a refined layout, and a final print-ready proof for your approval. Nothing is printed until you have signed off the proof.'],
                    ['q' => 'Can you design packaging for a product that already exists?', 'a' => 'Yes. We take the dimensions from the product or your supplier, build the dieline (the flat plan that folds into the box) and place the artwork on each panel.'],
                    ['q' => 'What is bleed, and why does it matter?', 'a' => 'Bleed is extra colour that runs past the trim line, usually 3 mm. It stops thin white edges showing when the printed sheet is cut.'],
                ],
                'sections' => [
                    ['type' => 'logos', 'id' => 'clients'],
                    ['type' => 'text', 'id' => 'what-is-print', 'heading' => 'What makes print work in [[the real world]]?',
                        'paras' => ['A brochure is read on a train, a poster is seen from across a car park and a box is picked up in a shop. Each one needs its own layout, size and finish.',
                            'We design for the place the print will be used, then prepare the files so the printer delivers exactly what was approved.'],
                        'bullets' => ['A layout that suits the size and how it is held or viewed', 'Colours checked for print, not just a screen', 'Files set up with bleed, crop marks and the right colour mode']],
                    ['type' => 'impact', 'id' => 'in-numbers', 'heading' => 'How we prepare print, in [[numbers]]', 'text' => 'These are the checks every print project goes through.',
                        'stats' => [['value' => '3', 'label' => 'proofs before anything is printed'], ['value' => '3 mm', 'label' => 'bleed on every print-ready file'], ['value' => '100%', 'label' => 'of files checked before they reach the printer']]],
                    ['type' => 'media', 'id' => 'files', 'heading' => 'Print-ready files, set up before they reach the printer', 'image' => '/pages/print/bleed.webp',
                        'alt' => 'Print file with the bleed, the trim line and crop marks', 'flip' => true,
                        'paras' => ['Every file is built at the finished size with bleed and crop marks, so the printer trims exactly where it should.', 'We check fonts, images and colours before anything is sent to print.'],
                        'bullets' => ['Bleed and crop marks', 'Images at print resolution', 'Fonts embedded or outlined']],
                    ['type' => 'media', 'id' => 'stationery', 'heading' => 'Stationery that looks like one family', 'image' => '/pages/print/stationery.webp',
                        'alt' => 'Letterhead, envelope and business card in the same brand style', 'tone' => 'grey',
                        'paras' => ['Business cards, letterheads and envelopes share one set of colours, fonts and layouts, so your brand looks consistent wherever it appears.']],
                    ['type' => 'media', 'id' => 'poster', 'heading' => 'Posters and signage: one message, read at a [[distance]]', 'image' => '/pages/print/poster.webp',
                        'alt' => 'Poster with one headline, a strong colour and a clear grid', 'flip' => true,
                        'paras' => ['We design for the distance people will read from: a headline they can see across the room, one picture and one clear next step.']],
                    ['type' => 'media', 'id' => 'packaging', 'heading' => 'Packaging: from a flat plan to a box on the shelf', 'image' => '/pages/print/pack.webp',
                        'alt' => 'Flat packaging plan showing lid, sides, front panel and base, with cut and fold lines', 'tone' => 'grey',
                        'paras' => ['We build the dieline, the flat plan that folds into the box, and place the artwork on each panel, so the box looks right once it is made up.']],
                    ['type' => 'cards', 'id' => 'services', 'heading' => 'What is [[included]] in print design', 'cards' => [
                        ['icon' => 'lucide:file-text', 'title' => 'Brochures and flyers', 'text' => 'Leaflets, catalogues and flyers laid out to be read quickly.'],
                        ['icon' => 'lucide:palette', 'title' => 'Business cards and stationery', 'text' => 'Cards, letterheads and envelopes that match your brand.'],
                        ['icon' => 'lucide:newspaper', 'title' => 'Posters and signage', 'text' => 'Posters, banners and shop signs designed for how far they will be read.'],
                        ['icon' => 'lucide:package', 'title' => 'Packaging design', 'text' => 'Box and label artwork, with the dieline checked before print.'],
                        ['icon' => 'lucide:pen-tool', 'title' => 'Brand consistency', 'text' => 'One look across every printed item and on your website.'],
                        ['icon' => 'lucide:layers', 'title' => 'Print preparation', 'text' => 'Print-ready files, proofs and checks before the order goes in.'],
                    ]],
                    ['type' => 'steps', 'id' => 'process', 'heading' => 'How a print project [[runs]]', 'steps' => [
                        ['title' => 'Brief', 'text' => 'What the piece is for, the quantity, the size and the deadline.'],
                        ['title' => 'Concept', 'text' => 'Two or three design directions to choose from.'],
                        ['title' => 'Refine', 'text' => 'Revisions on the chosen direction, with the copy agreed.'],
                        ['title' => 'Proof', 'text' => 'A print-ready proof for your approval before anything is printed.'],
                        ['title' => 'Deliver', 'text' => 'Print-ready files and a checked order handed to your printer.'],
                    ]],
                    ['type' => 'cases', 'id' => 'case-studies', 'heading' => 'Results from our [[design and marketing]] work', 'service' => '*'],
                    ['type' => 'table', 'id' => 'compare', 'heading' => 'Brochure, poster or packaging: which do you need?',
                        'columns_text' => ' | Brochure | Poster | Packaging',
                        'rows_text' => "Best for | Explaining an offer in detail | Catching attention in a shop or street | Products that sell on a shelf\nTypical size | A4 or A5, folded or flat | A3 up to large format | A flat plan made up into a box\nDesign focus | Layout and reading order | One headline and a strong picture | Structure, labels and the unboxing",
                        'note' => 'Unsure? Tell us where the piece will be seen and we will recommend the format.'],
                    ['type' => 'cards', 'id' => 'pricing', 'heading' => 'What shapes the price of [[print]]', 'cards' => [
                        ['icon' => 'lucide:layers', 'title' => 'Format', 'text' => 'The size, the number of pages or panels, and any folds or die-cuts.'],
                        ['icon' => 'lucide:palette', 'title' => 'Finishes', 'text' => 'Paper choice, coatings, foil or embossing.'],
                        ['icon' => 'lucide:file-text', 'title' => 'Designs', 'text' => 'How many different pieces we design, and how many revisions.'],
                        ['icon' => 'lucide:package', 'title' => 'Quantity', 'text' => 'The print run. Larger runs lower the cost of each piece.'],
                    ]],
                    ['type' => 'reviews', 'id' => 'reviews', 'heading' => 'What our [[clients]] say'],
                    ['type' => 'industries', 'id' => 'industries', 'heading' => 'Print for your [[sector]]'],
                ],
            ],
            'aeo-geo' => [
                'name' => 'AEO & GEO', 'group' => 'digital-marketing', 'icon' => 'lucide:sparkles', 'sort' => 9,
                'blurb' => 'Get your business cited in ChatGPT, Google AI Overviews, Perplexity and Gemini answers.',
                'meta_title' => 'AEO and GEO Services UK | Get Cited in AI Answers',
                'meta_description' => 'AEO and GEO services from GTech Digital get UK businesses cited in ChatGPT, Google AI Overviews, Perplexity and Gemini, with monthly AI visibility tracking.',
                'keyword' => 'aeo and geo services', 'title' => 'AEO and GEO Services', 'motion' => '/services/aeo.webp',
                'highlight' => 'AI Answers', 'h1' => 'AEO and GEO Services That Get You Into [[AI Answers]]',
                'lead' => 'AEO and GEO services from GTech Digital get your business cited in AI answers from ChatGPT, Google AI Overviews, Perplexity and Gemini, with answer-first content, structured data and the trust signals AI tools look for.',
                'points' => ['Answer engine optimisation (AEO)', 'Generative engine optimisation (GEO)', 'AI visibility tracking'],
                'related' => ['search-engine-optimization', 'content-marketing', 'local-seo', 'seo-backlinks', 'reputation-management'],
                'testimonials' => ['Derek L'],
                'industries' => [
                    ['e-commerce', 'Product answers and comparisons that AI shopping results quote.'],
                    ['technology-saas', 'Cited in "best tool for" answers your buyers ask.'],
                    ['real-estate', 'Local answers about areas, prices and agents.'],
                    ['hospitality-hotels', 'Recommended when guests ask where to stay or eat.'],
                    ['travel', 'Featured in AI trip planning and destination answers.'],
                    ['b2b-marketing', 'Named in AI answers when buyers shortlist suppliers.'],
                ],
                'faqs' => [
                    ['q' => 'What is AEO?', 'a' => 'Answer engine optimisation (AEO) makes your content the direct answer to a question, in featured snippets, voice assistants and AI answers. It uses clear question-and-answer content, structured data and pages that answer one question well.'],
                    ['q' => 'What is GEO?', 'a' => 'Generative engine optimisation (GEO) helps your brand get mentioned and cited by AI tools such as ChatGPT, Google AI Overviews, Perplexity and Gemini. It focuses on being a trusted, well-described source those tools can rely on.'],
                    ['q' => 'Is AEO and GEO different from SEO?', 'a' => 'They build on SEO. SEO helps you rank in search results; AEO and GEO help you be the answer and the cited source. Strong SEO foundations make AEO and GEO work faster, so we usually do them together.'],
                    ['q' => 'How do you measure AI visibility?', 'a' => 'We track a set of real questions your customers ask, check which AI tools mention or cite you, and report the share of answers you appear in each month, alongside traffic from AI tools.'],
                    ['q' => 'How long does it take to appear in AI answers?', 'a' => 'Some changes, such as clearer answers and structured data, can show within weeks. Building the authority AI tools rely on usually takes a few months of steady work.'],
                    ['q' => 'Can you guarantee ChatGPT will recommend us?', 'a' => 'No one can honestly guarantee what an AI tool will say. GTech Digital improves the signals these tools rely on, such as clear answers, structured data, reviews and mentions, tracks the results every month and focuses on the questions that bring you customers.'],
                ],
                'sections' => [
                    ['type' => 'logos', 'id' => 'clients'],
                    ['type' => 'text', 'id' => 'what-is-aeo-geo', 'heading' => 'What are AEO and GEO, and why do they [[matter now]]?',
                        'paras' => ['Answer engine optimisation (AEO) makes your content the direct answer to a question. Generative engine optimisation (GEO) gets your brand mentioned and cited in AI-written answers from tools like ChatGPT, Google AI Overviews, Perplexity and Gemini.',
                            'When a customer asks an AI tool for a recommendation, it names only a few businesses. AEO and GEO help make sure one of them is yours.'],
                        'bullets' => ['Be the source AI answers quote', 'Win featured snippets and voice answers', 'AI search optimisation for ChatGPT visibility', 'AI Overviews optimisation for Google', 'Track your visibility across AI tools']],
                    ['type' => 'impact', 'id' => 'in-numbers', 'heading' => 'How we work on AI visibility, in [[numbers]]', 'text' => 'The standards every AEO and GEO project follows.',
                        'stats' => [['value' => '4', 'label' => 'AI tools tracked: ChatGPT, AI Overviews, Perplexity, Gemini'], ['value' => '50+', 'label' => 'real customer questions tracked'], ['value' => '1', 'label' => 'clear AI visibility report every month']]],
                    ['type' => 'media', 'id' => 'ai-answers', 'heading' => 'Get named when customers ask AI for a [[recommendation]]', 'image' => '/pages/aeo/answer.webp',
                        'alt' => 'An AI answer recommending your brand, with your website and reviews shown as sources',
                        'paras' => ['AI tools build answers from sources they trust. We make your website, reviews and listings clear, consistent and easy to quote, so you are the business they name.'],
                        'bullets' => ['Clear service and location pages', 'Consistent details across the web', 'Reviews and mentions AI tools can find']],
                    ['type' => 'media', 'id' => 'answer-content', 'heading' => 'AEO and GEO content: answer-first pages AI tools can quote', 'image' => '/pages/aeo/faqcontent.webp',
                        'alt' => 'Answer-first page layout with a short answer, schema types and trust signals', 'tone' => 'grey', 'flip' => true,
                        'paras' => ['Each page answers one question clearly in the first lines, then gives the detail. We add FAQ and service schema, author details and sources, so the answer is easy to find and trust.'],
                        'bullets' => ['Questions as headings, answers in 40-60 words', 'FAQ, Service and Organization schema', 'Named authors and updated dates']],
                    ['type' => 'media', 'id' => 'entity', 'heading' => 'GEO for your brand: a clear [[entity]] AI understands', 'image' => '/pages/aeo/entity.webp',
                        'alt' => 'Your brand connected to its services, location, reviews, experts and FAQs',
                        'paras' => ['AI tools understand businesses as entities: who you are, what you do, where and for whom. We connect these facts across your website, Google Business Profile, directories and social profiles.']],
                    ['type' => 'media', 'id' => 'tracking', 'heading' => 'AEO and GEO tracking across every AI tool', 'image' => '/pages/aeo/engines.webp',
                        'alt' => 'Example share of tracked questions mentioning your brand in ChatGPT, Google AI Overviews, Perplexity and Gemini', 'tone' => 'grey', 'flip' => true,
                        'paras' => ['We track the questions your customers ask in each AI tool and report how often you are mentioned or cited, so you can see progress month by month.']],
                    ['type' => 'cards', 'id' => 'services', 'heading' => 'What is [[included]] in AEO and GEO', 'cards' => [
                        ['icon' => 'lucide:search', 'title' => 'Question research', 'text' => 'The real questions customers ask search and AI tools.'],
                        ['icon' => 'lucide:file-text', 'title' => 'Answer-first content', 'text' => 'Pages and FAQs that answer each question clearly.'],
                        ['icon' => 'lucide:layers', 'title' => 'Structured data', 'text' => 'Schema that describes your business, services and answers.'],
                        ['icon' => 'lucide:map-pin', 'title' => 'Entity and listings', 'text' => 'Consistent details on your website, Google and directories.'],
                        ['icon' => 'lucide:star', 'title' => 'Trust signals', 'text' => 'Reviews, author bios, sources and mentions AI tools rely on.'],
                        ['icon' => 'lucide:sparkles', 'title' => 'AI visibility tracking', 'text' => 'Monthly reports on mentions and citations in AI answers.'],
                    ]],
                    ['type' => 'steps', 'id' => 'process', 'heading' => 'Our AEO and GEO process, [[step by step]]', 'steps' => [
                        ['title' => 'Audit', 'text' => 'Check where you appear in AI answers today.'],
                        ['title' => 'Research', 'text' => 'Find the questions that bring you customers.'],
                        ['title' => 'Optimise', 'text' => 'Answer-first content, schema and entity fixes.'],
                        ['title' => 'Build trust', 'text' => 'Reviews, mentions and listings AI tools rely on.'],
                        ['title' => 'Track', 'text' => 'Monthly AI visibility report and next steps.'],
                    ]],
                    ['type' => 'cases', 'id' => 'case-studies', 'heading' => 'Results from our [[search]] work', 'service' => 'search-engine-optimization'],
                    ['type' => 'table', 'id' => 'compare', 'heading' => 'SEO, AEO or GEO: which do you need?',
                        'columns_text' => ' | SEO | AEO | GEO',
                        'rows_text' => "Goal | Rank in search results | Be the direct answer | Be cited in AI answers\nWhere it shows | Google results pages | Snippets, voice and AI answers | ChatGPT, AI Overviews, Perplexity, Gemini\nMain work | Technical, content and links | Question-led content and schema | Entity, trust and mentions",
                        'note' => 'Most businesses need all three. Strong SEO makes AEO and GEO work faster.'],
                    ['type' => 'cards', 'id' => 'pricing', 'heading' => 'What shapes the price of [[AEO and GEO]]', 'cards' => [
                        ['icon' => 'lucide:search', 'title' => 'Questions tracked', 'text' => 'How many customer questions and AI tools we monitor.'],
                        ['icon' => 'lucide:file-text', 'title' => 'Content', 'text' => 'How many pages and answers we write or improve.'],
                        ['icon' => 'lucide:map-pin', 'title' => 'Locations', 'text' => 'One location, several branches or the whole UK.'],
                        ['icon' => 'lucide:star', 'title' => 'Authority', 'text' => 'How much work your reviews and mentions need.'],
                    ]],
                    ['type' => 'reviews', 'id' => 'reviews', 'heading' => 'What our [[clients]] say'],
                    ['type' => 'industries', 'id' => 'industries', 'heading' => 'AEO and GEO for your [[sector]]'],
                ],
            ],
        ];
    }

    public static function apply(): void
    {
        foreach (self::REMOVE as $slug) self::remove($slug);
        foreach (self::added() as $slug => $s) self::add($slug, $s);
        self::placeAfter('aeo-geo', 'search-engine-optimization');
        self::placeAfter('restaurant-digital-marketing', 'local-seo');
        PageCache::flush();
        Repo::flush();
    }

    /** Puts a service straight after another one in its menu column, keeping the others in order. */
    private static function placeAfter(string $slug, string $after): void
    {
        $item = ServiceItem::query()->where('slug', $slug)->first();
        if (! $item) return;
        $list = ServiceItem::query()->where('group_slug', $item->group_slug)->where('slug', '!=', $slug)->orderBy('sort')->orderBy('id')->get()->values();
        $pos = $list->search(fn ($i) => $i->slug === $after);
        $list->splice($pos === false ? $list->count() : $pos + 1, 0, [$item]);
        foreach ($list->values() as $n => $i) if ((int) $i->sort !== $n) $i->forceFill(['sort' => $n])->save();
    }

    private static function add(string $slug, array $s): void
    {
        ServiceItem::query()->updateOrCreate(['slug' => $slug], ['group_slug' => $s['group'], 'name' => $s['name'], 'blurb' => $s['blurb'], 'icon' => $s['icon'], 'sort' => $s['sort']]);
        $path = "/services/$slug";
        Page::query()->updateOrCreate(['key' => "service~$slug"], [
            'kind' => 'service', 'slug' => $slug, 'name' => $s['name'], 'path' => $path, 'sort' => 40, 'published' => true,
            'meta_title' => $s['meta_title'], 'meta_description' => $s['meta_description'], 'focus_keyword' => $s['keyword'],
            'hero' => ['keyword' => $s['keyword'], 'title' => $s['title'], 'highlight' => $s['highlight'], 'lead' => $s['lead'],
                'motion' => $s['motion'], 'points' => $s['points'], 'h1' => $s['h1']],
            'sections' => self::sections($s + ['slug' => $slug]),
            'faqs' => [...$s['faqs'], ...(self::extras($slug)['faqs'] ?? [])], 'related' => $s['related'], 'data' => [],
        ]);
    }

    /**
     * Per service: the "On this page" labels, four headline numbers, six process steps, the pricing intro and
     * cards, the page whose client reviews are shown, and the extra FAQs, so each page matches the others.
     */
    private static function extras(string $slug): array
    {
        return [
            'restaurant-digital-marketing' => [
                'nav' => ['what-is' => 'Overview', 'restaurant-seo' => 'Restaurant SEO', 'local-seo' => 'Local SEO', 'reviews-growth' => 'Reviews', 'meta-ads' => 'Meta ads',
                    'google-ads' => 'Google Ads', 'services' => "What's included", 'process' => 'Process', 'case-studies' => 'Case studies', 'compare' => 'Apps vs direct', 'pricing' => 'Pricing', 'reviews' => 'Reviews'],
                'stats' => [['value' => '3', 'label' => 'Map pack spots we target'], ['value' => '2-5', 'label' => 'Mile ad radius'], ['value' => '0%', 'label' => 'Commission on direct orders'], ['value' => '100%', 'label' => 'Bookings and orders tracked']],
                'steps' => [
                    ['title' => 'Audit', 'text' => 'Profile, website, reviews, ads and nearby competitors.'],
                    ['title' => 'Basics', 'text' => 'Google Business Profile, menu, booking and order links.'],
                    ['title' => 'Tracking', 'text' => 'Calls, bookings and orders measured by channel.'],
                    ['title' => 'Ads', 'text' => 'Meta and Google Ads within your radius and hours.'],
                    ['title' => 'SEO and reviews', 'text' => 'Cuisine pages, citations and more Google reviews.'],
                    ['title' => 'Report', 'text' => 'Monthly results and the next month\'s plan.'],
                ],
                'pricing_intro' => 'Most independent UK restaurants invest from about £500 a month in management plus a few hundred pounds of ad budget; groups with several sites spend more. Your fixed quote depends on:',
                'pricing' => [
                    ['icon' => 'lucide:store', 'title' => 'Number of sites', 'text' => 'One restaurant or a group of locations.'],
                    ['icon' => 'lucide:map-pin', 'title' => 'Area and competition', 'text' => 'How many restaurants compete near you.'],
                    ['icon' => 'lucide:megaphone', 'title' => 'Ad budget', 'text' => 'Your monthly spend on Meta and Google Ads.'],
                    ['icon' => 'lucide:camera', 'title' => 'Food content', 'text' => 'Photos and short videos, shot by us or supplied.'],
                ],
                'reviews_from' => 'service~local-seo',
                'faqs' => [
                    ['q' => 'Do you work with takeaways and cafes as well as restaurants?', 'a' => 'Yes. We work with restaurants, takeaways, cafes, bars and small groups. The plan changes with how you sell: takeaways focus more on direct orders and delivery areas, sit-down restaurants on bookings and reviews.'],
                ],
            ],
            'ui-ux-design' => [
                'nav' => ['what-is-ux' => 'UI/UX design', 'journey' => 'Journey mapping', 'prototype' => 'Prototyping', 'testing' => 'Usability testing', 'system' => 'Design system',
                    'services' => "What's included", 'process' => 'Process', 'case-studies' => 'Case studies', 'compare' => 'UI/UX vs web design', 'pricing' => 'Pricing', 'reviews' => 'Reviews', 'industries' => 'Industries'],
                'stats' => [['value' => '5', 'label' => 'Users in every test round'], ['value' => '3', 'label' => 'Test rounds before build'], ['value' => '100%', 'label' => 'Screens tested on mobile'], ['value' => '1', 'label' => 'Design system per project']],
                'steps' => [
                    ['title' => 'Discover', 'text' => 'Goals, users and a review of your current site or app.'],
                    ['title' => 'Research', 'text' => 'Interviews, analytics and the questions customers ask.'],
                    ['title' => 'Map', 'text' => 'User journeys and the points where people get stuck.'],
                    ['title' => 'Design', 'text' => 'Wireframes, then a clickable prototype in your brand.'],
                    ['title' => 'Test', 'text' => 'Real users try it; we fix what they struggle with.'],
                    ['title' => 'Hand over', 'text' => 'Final designs, design system and specs for developers.'],
                ],
                'pricing_intro' => 'Most UK UI/UX projects range from about £2,500 for a focused redesign to £15,000+ for a full web or mobile app. Your fixed quote depends on:',
                'pricing' => [
                    ['icon' => 'lucide:layers', 'title' => 'Number of screens', 'text' => 'A landing page, a full website or a whole app.'],
                    ['icon' => 'lucide:users', 'title' => 'Research depth', 'text' => 'Analytics review only, or interviews with your customers.'],
                    ['icon' => 'lucide:flask-conical', 'title' => 'Testing rounds', 'text' => 'How many rounds of usability testing we run.'],
                    ['icon' => 'lucide:layout-dashboard', 'title' => 'Design system', 'text' => 'Screens only, or a reusable system for future work.'],
                ],
                'reviews_from' => 'service~website-design',
                'faqs' => [
                    ['q' => 'How much does UI/UX design cost in the UK?', 'a' => 'A focused redesign of key pages usually starts from about £2,500, and a full web or mobile app design can be £15,000 or more. We give a fixed quote after a short discovery call, based on the number of screens, research and testing rounds.'],
                    ['q' => 'What do I receive at the end of a UI/UX project?', 'a' => 'Final designs for every screen, a clickable prototype, a design system with colours, type and components, and specifications your developers can build from. You own all the files.'],
                ],
            ],
            'print-media' => [
                'nav' => ['what-is-print' => 'Print design', 'files' => 'Print-ready files', 'stationery' => 'Stationery', 'poster' => 'Posters and signage', 'packaging' => 'Packaging',
                    'services' => "What's included", 'process' => 'Process', 'case-studies' => 'Case studies', 'compare' => 'Which format', 'pricing' => 'Pricing', 'reviews' => 'Reviews', 'industries' => 'Industries'],
                'stats' => [['value' => '3', 'label' => 'Proofs before print'], ['value' => '3 mm', 'label' => 'Bleed on every file'], ['value' => '300 dpi', 'label' => 'Image resolution checked'], ['value' => '100%', 'label' => 'Files checked before printing']],
                'steps' => [
                    ['title' => 'Brief', 'text' => 'What the piece is for, quantity, size and deadline.'],
                    ['title' => 'Concept', 'text' => 'Two or three design directions to choose from.'],
                    ['title' => 'Copy', 'text' => 'Headlines and text agreed and checked.'],
                    ['title' => 'Refine', 'text' => 'Revisions on the chosen direction.'],
                    ['title' => 'Proof', 'text' => 'A print-ready proof for your sign-off.'],
                    ['title' => 'Print', 'text' => 'Files sent to the printer and the order checked.'],
                ],
                'pricing_intro' => 'Print design in the UK typically ranges from about £150 for a flyer or business card to £1,500+ for a multi-page brochure or packaging range. Printing is quoted separately. Your fixed design quote depends on:',
                'pricing' => [
                    ['icon' => 'lucide:file-text', 'title' => 'Number of pieces', 'text' => 'A single flyer or a full set of stationery.'],
                    ['icon' => 'lucide:layers', 'title' => 'Pages and format', 'text' => 'Page count, folds, die-cuts and special sizes.'],
                    ['icon' => 'lucide:palette', 'title' => 'Brand work', 'text' => 'Using your brand guide, or creating the look first.'],
                    ['icon' => 'lucide:package', 'title' => 'Packaging', 'text' => 'Dielines, labels and print checks for each box.'],
                ],
                'reviews_from' => 'service~branding',
                'faqs' => [
                    ['q' => 'How much does print design cost?', 'a' => 'A flyer or business card design typically starts from about £150, and a multi-page brochure or packaging range can be £1,500 or more. Printing is quoted separately, so you can compare printers.'],
                    ['q' => 'How long does a print project take?', 'a' => 'A business card or flyer usually takes a few days from brief to approved proof. A brochure or packaging range takes two to four weeks. Printing and delivery are added on top.'],
                ],
            ],
            'aeo-geo' => [
                'nav' => ['what-is-aeo-geo' => 'AEO & GEO', 'ai-answers' => 'AI recommendations', 'answer-content' => 'Answer-first content', 'entity' => 'Entity', 'tracking' => 'AI tracking',
                    'services' => "What's included", 'process' => 'Process', 'case-studies' => 'Case studies', 'compare' => 'SEO vs AEO vs GEO', 'pricing' => 'Pricing', 'reviews' => 'Reviews', 'industries' => 'Industries'],
                'stats' => [['value' => '4', 'label' => 'AI tools tracked'], ['value' => '50+', 'label' => 'Customer questions tracked'], ['value' => '12', 'label' => 'Monthly AI visibility reports a year'], ['value' => '10+', 'label' => 'Years in search']],
                'steps' => [
                    ['title' => 'Audit', 'text' => 'Where you appear in AI answers today.'],
                    ['title' => 'Research', 'text' => 'The questions that bring you customers.'],
                    ['title' => 'Structure', 'text' => 'Schema, entity details and consistent listings.'],
                    ['title' => 'Optimise', 'text' => 'Answer-first pages and FAQs AI tools can quote.'],
                    ['title' => 'Build trust', 'text' => 'Reviews, mentions and citations from trusted sites.'],
                    ['title' => 'Track', 'text' => 'Monthly AI visibility report and next steps.'],
                ],
                'pricing_intro' => 'Most UK AEO and GEO programmes range from about £750 to £4,000+ per month, often alongside SEO. First changes usually show within 4 to 8 weeks, with steady gains over 3 to 6 months. Your fixed quote depends on:',
                'pricing' => [
                    ['icon' => 'lucide:search', 'title' => 'Questions tracked', 'text' => 'How many customer questions and AI tools we monitor.'],
                    ['icon' => 'lucide:file-text', 'title' => 'Content needed', 'text' => 'New answer pages and FAQs written each month.'],
                    ['icon' => 'lucide:map-pin', 'title' => 'Location scope', 'text' => 'One town, several branches or the whole UK.'],
                    ['icon' => 'lucide:star', 'title' => 'Authority gap', 'text' => 'How many reviews and mentions you need to catch up.'],
                ],
                'reviews_from' => 'service~search-engine-optimization',
                'faqs' => [
                    ['q' => 'How much do AEO and GEO cost in the UK?', 'a' => 'Most programmes range from about £750 to £4,000+ per month, depending on how many questions and AI tools we track, the content needed and your locations. They are often combined with SEO for better value.'],
                    ['q' => 'Which AI tools do you optimise for?', 'a' => 'ChatGPT, Google AI Overviews and AI Mode, Perplexity, Gemini and Microsoft Copilot. We track your mentions and citations in each one every month.'],
                ],
            ],
        ][$slug] ?? [];
    }

    /** The page's sections in the page builder's format; reviews and industries come from the site's own data. */
    private static function sections(array $s): array
    {
        $x = self::extras($s['slug'] ?? '');
        return PageBlocks::fromBuilder(array_map(function (array $sec) use ($s, $x) {
            $data = $sec;
            if (isset($x['nav'][$sec['id']])) $data['nav'] = $x['nav'][$sec['id']];
            if ($sec['type'] === 'impact' && isset($x['stats'])) $data['stats'] = $x['stats'];
            if ($sec['type'] === 'steps' && isset($x['steps'])) $data['steps'] = $x['steps'];
            if ($sec['id'] === 'pricing' && isset($x['pricing'])) { $data['cards'] = $x['pricing']; $data['intro'] = $x['pricing_intro']; }
            if ($sec['type'] === 'reviews' && isset($x['reviews_from'])) {
                $from = collect((array) Page::query()->find($x['reviews_from'])?->sections)->firstWhere('type', 'reviews');
                if (! empty($from['reviews'])) { $data['reviews'] = $from['reviews']; return ['type' => 'reviews', 'data' => $data]; }
            }
            if ($sec['type'] === 'reviews') $data['reviews'] = self::reviews($s['testimonials'] ?? []);
            if ($sec['type'] === 'industries') $data['items'] = array_map(fn ($i) => ['slug' => $i[0], 'text' => $i[1]], $s['industries'] ?? []);
            return ['type' => $sec['type'], 'data' => $data];
        }, $s['sections']));
    }

    /** Real client reviews already on the site, by name (never written out here). */
    private static function reviews(array $names): array
    {
        return Testimonial::query()->whereIn('name', $names)->orderBy('sort')->get()
            ->map(fn (Testimonial $t) => ['name' => $t->name, 'role' => '', 'text' => (string) $t->text])->values()->all();
    }

    private static function remove(string $slug): void
    {
        $path = "/services/$slug";
        ServiceItem::query()->where('slug', $slug)->delete();
        $keys = Page::query()->where('key', "service~$slug")->pluck('key');
        Revision::query()->where('model', 'page')->whereIn('model_key', $keys)->delete();
        Page::query()->whereIn('key', $keys)->delete();
        SeoEntry::query()->where('key', SeoEntry::keyFor($path))->delete();
        SeoKeyword::query()->where('slug', $slug)->delete();

        $targets = [$path, $slug];
        foreach (SeoKeyword::query()->get() as $k) {
            $links = array_values(array_filter((array) $k->links, fn ($l) => ! in_array($l['target'] ?? '', $targets, true)));
            if (count($links) !== count((array) $k->links)) { $k->links = $links; $k->save(); }
        }
        foreach (Page::query()->get() as $p) {
            $sections = array_map(function ($s) use ($slug, $path) {
                if (($s['type'] ?? '') === 'cases' && ($s['service'] ?? '') === $slug) $s['service'] = '';
                return self::unlinkDeep($s, [$path]);
            }, (array) $p->sections);
            $related = array_values(array_filter((array) $p->related, fn ($r) => $r !== $slug));
            $hero = self::unlinkDeep((array) $p->hero, [$path]);
            $faqs = self::unlinkDeep((array) $p->faqs, [$path]);
            if ($sections !== (array) $p->sections || $related !== (array) $p->related || $hero !== (array) $p->hero || $faqs !== (array) $p->faqs) {
                $p->forceFill(['sections' => $sections, 'related' => $related, 'hero' => $hero, 'faqs' => $faqs])->save();
            }
        }
        foreach (CaseStudy::query()->get() as $c) {
            $new = ['services' => array_values(array_diff((array) $c->services, [$slug]))];
            foreach (['body', 'challenge', 'solution'] as $f) $new[$f] = self::unlink((string) $c->$f, [$path]);
            $c->forceFill($new);
            if ($c->isDirty()) $c->save();
        }
        foreach (Post::query()->where('body', 'like', "%$path%")->get() as $post) {
            $post->body = self::unlink((string) $post->body, [$path]);
            if ($post->isDirty()) $post->save();
        }
        Redirect::query()->where('to_path', $path)->update(['to_path' => '/services']);
        Redirect::query()->updateOrCreate(['from_path' => $path], ['to_path' => '/services', 'status_code' => 301, 'automatic' => true]);
    }

    /** Links to the removed addresses become plain text (HTML and Markdown links). */
    public static function unlink(string $text, array $paths): string
    {
        if ($text === '' || ! str_contains($text, '/services/')) return $text;
        $alt = implode('|', array_map(fn ($p) => preg_quote($p, '#'), $paths));
        $text = preg_replace('#<a\b[^>]*href\s*=\s*["\'](?:https?://[^/"\']+)?(?:'.$alt.')/?(?:[?\#][^"\']*)?["\'][^>]*>(.*?)</a>#is', '$1', $text);
        return preg_replace('#\[([^\]]*)\]\((?:https?://[^/)]+)?(?:'.$alt.')/?\)#i', '$1', $text);
    }

    private static function unlinkDeep(mixed $v, array $paths): mixed
    {
        if (is_string($v)) return self::unlink($v, $paths);
        if (is_array($v)) return array_map(fn ($x) => self::unlinkDeep($x, $paths), $v);
        return $v;
    }
}
