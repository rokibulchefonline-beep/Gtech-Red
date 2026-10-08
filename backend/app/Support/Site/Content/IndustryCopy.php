<?php

namespace App\Support\Site\Content;

/**
 * Industry page copy (October 2026 content review). Written from UK SERP research (Ahrefs: who ranks, People Also
 * Ask, search volumes): each page answers the questions buyers in that sector actually ask, names the platforms
 * and rules of the sector (entities), and covers cost and "which option" intent, instead of repeating the keyword.
 */
class IndustryCopy
{
    public static function all(): array
    {
        return [
            'hospitality-hotels' => [
                'aud' => 'Hotels, Restaurants and Venues',
                'title' => 'Hospitality Marketing Agency UK | Hotels & Restaurants',
                'desc' => 'Hospitality marketing for UK hotels, restaurants and venues: more direct bookings, less OTA commission, top of Google Maps and social content that fills tables.',
                'lead' => 'GTech Digital is a hospitality marketing agency that helps UK hotels, restaurants and venues win more direct bookings, rely less on OTAs such as Booking.com and Expedia, and fill quiet nights with Google, social and email campaigns.',
                'overview' => [
                    'heading' => 'What Is Hospitality Marketing?',
                    'paras' => [
                        'Hospitality marketing is how a hotel, restaurant, pub or venue gets found, chosen and booked. Guests now plan on Google Maps, Instagram, TikTok and review sites, then book through whichever option is easiest, often an online travel agency (OTA) that keeps 15 to 25 per cent of the room rate as commission.',
                        'Good hospitality marketing makes your own website the easiest and best-value place to book, keeps your venue visible for "near me" and "things to do" searches, and brings past guests back with email and offers. It is measured in bookings, covers and revenue, not likes.',
                    ],
                    'bullets' => ['Independent and group hotels', 'Restaurants, bars and pubs', 'Wedding, event and conference venues', 'Serviced apartments and holiday lets'],
                ],
                'media' => [
                    'growth' => ['Win More Direct Bookings and Pay Less Commission', ['Every booking you move from an OTA to your own website keeps the commission in your business. We combine Google Hotel Ads, brand search campaigns and a clear best-rate promise on your site so guests who find you on Booking.com still book with you directly.'], ['Google Hotel Ads and metasearch', 'Brand protection search campaigns', 'Best-rate and perks messaging', 'Booking revenue tracked to each channel']],
                    'channels' => ['Be Found on Google Maps, Instagram and TikTok', ['Most diners and guests choose a venue within minutes of searching. We keep your Google Business Profile complete and active, rank you for your area and cuisine, and create short videos that show the food, rooms and atmosphere people are looking for.'], ['Google Business Profile management', 'Local SEO for area, cuisine and occasion', 'Instagram Reels and TikTok content', 'Email and SMS for repeat guests']],
                    'journey' => ['A Website and Booking Engine That Converts Browsers Into Guests', ['Slow pages, hidden prices and long booking forms send guests back to the OTAs. We design fast, mobile-first hospitality websites with clear menus, room pages and offers, connected to your booking engine or table system so the booking takes seconds.'], ['Booking engine and table booking integration', 'Room, menu, events and offers pages', 'Gift vouchers and packages online', 'Speed and mobile checkout improvements']],
                    'trust' => ['Reviews, Reputation and Seasonal Campaigns', ['Ratings on Google and TripAdvisor decide many bookings. We reply to reviews within 24 hours, encourage happy guests to leave one, and plan campaigns around the moments that matter for hospitality: Valentine\'s, Mother\'s Day, summer, Christmas parties and New Year.'], ['Review replies within 24 hours', 'Review requests after every stay or meal', 'Seasonal campaign calendar', 'Events, weddings and Christmas promotions']],
                ],
                'who' => ['Who We Work With in Hospitality', 'Marketing that fits how your guests book.', [
                    ['lucide:hotel', 'Independent hotels', 'Direct bookings, Google Hotel Ads and fewer OTA nights.'],
                    ['lucide:building-2', 'Hotel groups', 'Brand search, group websites and channel reporting.'],
                    ['lucide:utensils-crossed', 'Restaurants and cafés', 'Google Maps, social content and table bookings.'],
                    ['lucide:beer', 'Pubs and bars', 'Events, sports nights and local visibility.'],
                    ['lucide:party-popper', 'Event and wedding venues', 'Enquiries for weddings, parties and conferences.'],
                    ['lucide:house', 'Holiday lets and apartments', 'Direct bookings away from Airbnb fees.'],
                ]],
                'cost' => ['How Much Does Hospitality Marketing Cost?', 'Most independent UK hotels and restaurants invest £750 to £3,000 a month in marketing management, plus their ad budget. Hotels often aim to spend a little less on marketing than they currently pay in OTA commission. Your quote depends on:', [
                    ['lucide:bed-double', 'Rooms or covers', 'How many nights or tables you need to fill.'],
                    ['lucide:map', 'Locations', 'One venue or several sites.'],
                    ['lucide:megaphone', 'Channels', 'Search, hotel ads, social and email.'],
                    ['lucide:camera', 'Content', 'Photo and video shoots each month.'],
                ]],
                'compare' => ['OTA Bookings vs Direct Bookings', ['', 'Booking through an OTA', 'Booking on your website'], [
                    ['Commission', 'Typically 15 to 25 per cent', 'None (only your marketing cost)'],
                    ['Guest data', 'Kept by the OTA', 'Yours, for email and loyalty'],
                    ['Upsells', 'Limited', 'Packages, extras and room upgrades'],
                    ['Best for', 'Reach new markets and fill gaps', 'Repeat and loyal guests, higher margin'],
                ], 'Most hotels keep some OTA listings for reach and use marketing to move repeat and local guests to direct booking.'],
                'faqs' => [
                    ['How much should a hotel spend on marketing?', 'Many UK hotels spend 3 to 6 per cent of room revenue on marketing. A useful check is your OTA commission: if moving even a third of those bookings to your website saves more than the campaign costs, the marketing pays for itself. GTech Digital tracks this saving in every monthly report.'],
                    ['What is a hospitality marketing agency?', 'A hospitality marketing agency helps hotels, restaurants and venues attract guests through search, social media, ads, email and their website. GTech Digital works as a hotel marketing agency for independent hotels and groups, and as a restaurant marketing agency for restaurants, pubs and bars, measured on bookings, covers and revenue.'],
                    ['How do restaurants get more customers from social media?', 'Short videos of signature dishes, behind-the-scenes clips and local events work best on Instagram and TikTok. Pair them with a complete Google Business Profile and a one-tap booking link so people who see the content can reserve a table straight away.'],
                ],
                'sec' => ['hotel marketing agency', 'restaurant marketing agency'],
                'ent' => ['Google Hotel Ads', 'Booking.com', 'Expedia', 'TripAdvisor', 'Google Business Profile', 'booking engine', 'OTA commission', 'OpenTable'],
            ],
            'real-estate' => [
                'aud' => 'Estate and Letting Agents',
                'title' => 'Property Marketing Agency UK | Estate & Letting Agents',
                'desc' => 'Property marketing for UK estate agents, letting agents and developers: more valuation leads, local visibility for every branch and less reliance on portals.',
                'lead' => 'GTech Digital is a property marketing agency that helps UK estate agents, letting agents and developers win more valuation requests and instructions with local SEO, instant valuation tools, paid social and content that sellers and landlords trust.',
                'overview' => [
                    'heading' => 'What Is Property Marketing?',
                    'paras' => [
                        'Property marketing is how an estate agency, letting agency or developer wins new instructions and sells or lets them quickly. Rightmove, Zoopla and OnTheMarket bring buyers and tenants, but vendors and landlords choose an agent long before they list, usually after searching "estate agents near me" or "how much is my house worth".',
                        'We help you own that moment: ranking each branch locally, offering an instant online valuation that captures the lead, and following up with content and email until they are ready to instruct. Results are measured in valuations booked and instructions won.',
                    ],
                    'bullets' => ['Independent estate agents', 'Letting and property management agents', 'Multi-branch agency groups', 'New-build developers and land promoters'],
                ],
                'media' => [
                    'growth' => ['More Valuation Requests and New Instructions', ['Vendors and landlords are your most valuable leads, and the portals do not hand them to you. We drive them to an instant valuation tool on your website, then follow up quickly so more of those valuations turn into instructions.'], ['Instant online valuation landing pages', 'Seller and landlord lead campaigns', 'Fast lead alerts to the right branch', 'Valuation-to-instruction tracking']],
                    'channels' => ['Local Visibility for Every Branch', ['Each branch should appear for searches in its own towns and postcodes. We optimise every Google Business Profile, build area pages with real local insight and run geo-targeted ads on Google, Facebook and Instagram around your patch.'], ['Google Business Profile for each branch', 'Area guides and street-level local SEO', 'Geo-targeted Meta and Google campaigns', 'Review generation after every completion']],
                    'journey' => ['A Website That Turns Browsers Into Valuations', ['Your website should do more than repeat the portal listings. Clear calls to action, area guides, sold-price stories and a simple valuation form give vendors a reason to contact you rather than the agent down the road.'], ['Property search connected to your feed', 'Vendor and landlord landing pages', 'Sold and let case studies', 'Mobile-first, fast-loading pages']],
                    'trust' => ['Portal Feeds, CRM and Compliance', ['We connect your website and campaigns to the systems you already use, such as Reapit, Alto, Street or Jupix, and feed listings to the portals. Material information and advertising rules are respected in every advert, so your marketing stays compliant with Trading Standards guidance.'], ['Property CRM and portal feed integration', 'Material information on every listing', 'Lead routing to negotiators', 'Reporting by branch and source']],
                ],
                'who' => ['Who We Work With in Property', 'Marketing shaped around how you win business.', [
                    ['lucide:house', 'Independent estate agents', 'Valuation leads and local reputation.'],
                    ['lucide:key-round', 'Letting agents', 'Landlord leads and property management.'],
                    ['lucide:building', 'Multi-branch groups', 'Branch-level visibility and reporting.'],
                    ['lucide:hard-hat', 'Developers', 'Off-plan launches and reservations.'],
                    ['lucide:briefcase', 'Commercial agents', 'B2B leads for property and land.'],
                    ['lucide:store', 'Hybrid and online agents', 'National reach with local trust.'],
                ]],
                'cost' => ['How Much Does Estate Agent Marketing Cost?', 'Most UK estate and letting agents invest £800 to £3,500 a month in marketing management, plus their ad budget, depending on how many branches they run. Your quote depends on:', [
                    ['lucide:map-pin', 'Branches', 'How many areas need local visibility.'],
                    ['lucide:target', 'Lead goals', 'Valuations and landlords you need each month.'],
                    ['lucide:megaphone', 'Ad budget', 'Search and social spend by area.'],
                    ['lucide:file-text', 'Content', 'Area guides, videos and email.'],
                ]],
                'compare' => ['Property Portals vs Your Own Website', ['', 'Portals (Rightmove, Zoopla)', 'Your own website and marketing'], [
                    ['Main job', 'Reach buyers and tenants', 'Win vendors and landlords'],
                    ['Who owns the lead', 'Shared with every agent listed', 'Only your agency'],
                    ['Cost', 'Monthly listing fee per branch', 'Your marketing and ad budget'],
                    ['Builds your brand', 'Very little', 'Yes, every visit and valuation'],
                ], 'Agents need both: portals sell and let the stock, and your own marketing wins the instructions.'],
                'faqs' => [
                    ['What are the best marketing strategies for estate agents?', 'The strongest mix for UK agents is local SEO for every branch, an instant online valuation tool, geo-targeted social ads aimed at homeowners, and regular review requests. Together they bring vendors and landlords to you before they compare agents on the portals.'],
                    ['How do estate agents get more instructions?', 'Instructions come from valuations, so the aim is more valuation requests and a faster follow-up. GTech Digital drives local homeowners to your valuation tool, alerts the right branch within minutes, and nurtures people who are not ready yet with email until they are.'],
                    ['Do estate agents need SEO if they are on Rightmove?', 'Yes. Rightmove helps buyers find your properties, but vendors usually search for agents on Google first. Ranking for "estate agents in [town]" and "how much is my house worth" puts your agency in front of people about to sell.'],
                ],
                'sec' => ['estate agent marketing', 'real estate marketing agency', 'letting agent marketing', 'valuation leads'],
                'ent' => ['Rightmove', 'Zoopla', 'OnTheMarket', 'instant valuation', 'Reapit', 'Google Business Profile', 'material information', 'Propertymark'],
            ],
            'automotive' => [
                'aud' => 'Car Dealers and Garages',
                'title' => 'Automotive Marketing Agency UK | Car Dealer Marketing',
                'desc' => 'Automotive marketing for UK car dealers, garages and dealer groups: more enquiries and test drives from your stock, local search and FCA-compliant finance ads.',
                'lead' => 'GTech Digital is an automotive marketing agency that helps UK franchised dealers, independent dealers and garages win more vehicle enquiries, test drives and service bookings from their own website, not only from AutoTrader.',
                'overview' => [
                    'heading' => 'What Is Automotive Marketing?',
                    'paras' => [
                        'Automotive marketing is how a dealership or garage brings buyers to its stock and customers to its workshop. Most car buyers research online for weeks, comparing listings on AutoTrader, Motors and CarGurus, then visit only one or two dealers before they buy.',
                        'We make sure your dealership is one of them. Your stock appears in Google Vehicle Ads and paid social, your website shows every car with finance options and real photos, and every enquiry is tracked to the sale, so you know which channel sold which car.',
                    ],
                    'bullets' => ['Franchised main dealers', 'Independent used car dealers', 'Dealer groups', 'Garages, MOT and service centres'],
                ],
                'media' => [
                    'growth' => ['More Enquiries, Test Drives and Cars Sold', ['Marketplaces are useful, but you compete there on price next to hundreds of similar cars. We build your own enquiry pipeline: campaigns that send in-market buyers straight to your vehicle pages, with finance quotes, part-exchange valuations and test drive booking ready to go.'], ['Stock-led search and social campaigns', 'Test drive and part-exchange forms', 'Finance enquiry tracking', 'Cost per enquiry and per sale reporting']],
                    'channels' => ['Stock Ads, Local Search and Marketplaces Working Together', ['We feed your live stock into Google Vehicle Ads and Meta automotive inventory ads, keep each site\'s Google Business Profile up to date, and help your listings on AutoTrader, Motors and CarGurus stand out, so buyers see your cars wherever they look.'], ['Google Vehicle Ads and Performance Max', 'Meta automotive inventory ads', 'Local SEO for each site and brand', 'Marketplace listing optimisation']],
                    'journey' => ['A Dealer Website That Works Like an Online Showroom', ['Buyers want to see every angle, the full spec, the monthly price and whether the car is still available. We build fast dealer websites connected to your stock feed, with clear finance examples, reservation deposits and simple forms.'], ['Live stock feed integration', 'Finance calculators and reservation deposits', 'Vehicle video and 360 photo pages', 'Service and MOT booking']],
                    'trust' => ['Finance Ads That Follow FCA Rules', ['Finance promotions are regulated. Every advert we write shows the representative example and wording the Financial Conduct Authority (FCA) requires, and we work with your compliance team so campaigns go live without risk to your licence.'], ['FCA-compliant finance promotions', 'Representative APR examples', 'Approval workflow with your compliance team', 'Clear terms on every landing page']],
                ],
                'who' => ['Who We Work With in Automotive', 'Marketing for every part of the forecourt.', [
                    ['lucide:car', 'Franchised dealers', 'New and approved used car campaigns.'],
                    ['lucide:car-front', 'Independent dealers', 'Your own enquiries, beyond AutoTrader.'],
                    ['lucide:building-2', 'Dealer groups', 'Multi-site reporting and brand campaigns.'],
                    ['lucide:wrench', 'Garages and MOT centres', 'Service and repair bookings locally.'],
                    ['lucide:zap', 'EV specialists', 'Electric car buyers and charging questions.'],
                    ['lucide:truck', 'Van and commercial dealers', 'Business buyers and fleet enquiries.'],
                ]],
                'cost' => ['How Much Does Car Dealer Marketing Cost?', 'Most UK independent dealers invest £800 to £3,000 a month in marketing management, plus ad spend; franchised and multi-site groups typically invest more. Your quote depends on:', [
                    ['lucide:car', 'Stock volume', 'How many cars you need to sell each month.'],
                    ['lucide:map-pin', 'Sites', 'One forecourt or a group.'],
                    ['lucide:megaphone', 'Ad budget', 'Search, vehicle ads and social spend.'],
                    ['lucide:wrench', 'Aftersales', 'Service and MOT campaigns as well.'],
                ]],
                'compare' => ['Marketplaces vs Your Own Dealer Marketing', ['', 'AutoTrader and marketplaces', 'Your website and campaigns'], [
                    ['Competition', 'Hundreds of similar cars side by side', 'Only your stock'],
                    ['Cost', 'Package fees per car or month', 'Your ad and marketing budget'],
                    ['Customer data', 'Limited', 'Yours, for follow-up and service'],
                    ['Best for', 'Reach and price comparison', 'Margin, loyalty and repeat buyers'],
                ], 'Most dealers keep marketplace listings and use their own marketing to lower cost per sale.'],
                'faqs' => [
                    ['How can a car dealership get more leads online?', 'Put your stock in front of in-market buyers with Google Vehicle Ads and Meta inventory ads, rank each site locally, and make every vehicle page answer the buyer\'s questions with finance, part-exchange and test drive options. GTech Digital tracks every enquiry to the sale.'],
                    ['Is AutoTrader enough for a car dealer?', 'AutoTrader brings a lot of buyers, but you compete on price with every similar car and pay for each listing. Good car dealership marketing also builds your own website traffic and enquiries, which usually lowers your cost per sale and keeps customers coming back for servicing.'],
                    ['What does an automotive marketing agency do?', 'An automotive marketing agency plans and runs the campaigns that sell cars and fill workshops: search and vehicle ads, social media, dealer websites, local SEO and email. GTech Digital also makes sure finance adverts meet FCA rules.'],
                ],
                'sec' => ['car dealer marketing', 'car dealership marketing'],
                'ent' => ['AutoTrader', 'Motors.co.uk', 'CarGurus', 'Google Vehicle Ads', 'stock feed', 'FCA', 'representative APR', 'part-exchange'],
            ],
            'b2b-marketing' => [
                'aud' => 'B2B Companies',
                'title' => 'B2B Marketing Agency UK | Lead Generation & ABM',
                'desc' => 'B2B marketing and lead generation for UK companies: qualified pipeline from LinkedIn, search and account-based marketing, connected to your CRM and sales team.',
                'lead' => 'GTech Digital is a B2B marketing agency that helps UK companies build qualified sales pipeline with B2B lead generation, LinkedIn, search, account-based marketing and content, all connected to your CRM so sales can see which campaigns create revenue.',
                'overview' => [
                    'heading' => 'What Is B2B Marketing?',
                    'paras' => [
                        'B2B marketing is marketing to other businesses. Buying decisions involve several people, take weeks or months, and depend on trust, so the job is to reach the right companies, help every person on the buying team understand your value, and hand sales warm, qualified conversations.',
                        'A B2B marketing agency combines demand generation (creating interest), lead generation (capturing it) and account-based marketing (focusing on named target accounts). GTech Digital measures it in pipeline and closed revenue, not form fills.',
                    ],
                    'bullets' => ['Professional services firms', 'Manufacturers and distributors', 'Technology and IT companies', 'Logistics, energy and B2B services'],
                ],
                'media' => [
                    'growth' => ['Qualified Leads and Pipeline Your Sales Team Wants', ['Not every lead is worth a sales call. We agree with your sales team what a marketing qualified lead (MQL) and sales qualified lead (SQL) look like, then optimise campaigns for those, so marketing is judged on meetings and revenue.'], ['Agreed MQL and SQL definitions', 'Lead scoring and routing', 'Cost per opportunity reporting', 'Monthly pipeline reviews with sales']],
                    'channels' => ['Demand Generation, B2B Lead Generation and ABM', ['LinkedIn reaches decision-makers by job title and company, search captures buyers who already have a need, and account-based marketing focuses budget on the accounts you most want to win. We combine them in one plan.'], ['LinkedIn Ads and Lead Gen Forms', 'Google Ads for high-intent searches', 'Account-based marketing for named accounts', 'Intent data to spot active buyers']],
                    'journey' => ['Content and Nurture for Long Buying Cycles', ['Most B2B buyers are not ready to talk on their first visit. Guides, case studies, comparison pages and webinars answer their questions at each stage, while email nurture keeps you front of mind until they are.'], ['Buyer guides and case studies', 'Comparison and pricing pages', 'Webinars and events', 'Email nurture sequences']],
                    'trust' => ['CRM Integration and Closed-Loop Reporting', ['We connect campaigns to HubSpot, Salesforce or Pipedrive so every lead keeps its source, from first click to closed deal. You see which channels and campaigns actually create revenue, and budgets move to what works.'], ['HubSpot, Salesforce and Pipedrive integration', 'Offline conversions sent back to ad platforms', 'Attribution by campaign and channel', 'Revenue dashboards for leadership']],
                ],
                'who' => ['Who We Work With in B2B', 'Lead generation shaped around your sales cycle.', [
                    ['lucide:briefcase', 'Professional services', 'Law, accountancy and consulting firms.'],
                    ['lucide:factory', 'Manufacturers', 'Distributors, trade buyers and specifiers.'],
                    ['lucide:cpu', 'IT and technology', 'Managed services, software and hardware.'],
                    ['lucide:truck', 'Logistics', 'Freight, warehousing and supply chain.'],
                    ['lucide:hard-hat', 'Construction', 'Contractors, suppliers and building products.'],
                    ['lucide:graduation-cap', 'Training and education', 'Corporate training and courses.'],
                ]],
                'cost' => ['How Much Does a B2B Marketing Agency Cost?', 'Most UK B2B companies invest £1,500 to £6,000 a month in agency fees, plus media spend; LinkedIn typically needs at least £1,500 a month in ad budget to learn. Your quote depends on:', [
                    ['lucide:target', 'Target market', 'How many accounts and decision-makers.'],
                    ['lucide:megaphone', 'Channels', 'LinkedIn, search, ABM and email.'],
                    ['lucide:file-text', 'Content', 'Guides, case studies and webinars.'],
                    ['lucide:database', 'CRM work', 'Integration, scoring and reporting.'],
                ]],
                'compare' => ['Inbound vs Outbound B2B Lead Generation', ['', 'Inbound (content, SEO, ads)', 'Outbound (ABM, outreach)'], [
                    ['How it works', 'Buyers find you when they have a need', 'You reach named accounts first'],
                    ['Best for', 'Broad markets, many potential buyers', 'Few high-value accounts'],
                    ['Time to results', 'Weeks to months, then compounds', 'Faster first meetings'],
                    ['Main channels', 'Google, LinkedIn content, guides', 'LinkedIn ABM, email, events'],
                ], 'Most B2B companies use both: inbound builds demand, outbound focuses sales on the best-fit accounts.'],
                'faqs' => [
                    ['What does a B2B marketing agency do?', 'A B2B marketing agency plans and runs the campaigns that create sales pipeline for business-to-business companies: lead generation, LinkedIn and search ads, account-based marketing, content and CRM reporting. GTech Digital works alongside your sales team and reports on meetings and revenue.'],
                    ['How much does a B2B marketing agency cost in the UK?', 'Most UK B2B agencies charge £1,500 to £6,000 a month in fees, plus ad spend. Fixed-scope projects such as a website or a lead generation campaign launch are quoted separately. GTech Digital gives a fixed quote after a free audit.'],
                    ['What is a B2B lead generation agency?', 'A B2B lead generation agency focuses on finding and qualifying potential customers for your sales team, through ads, content, outreach and events. The best ones measure success by qualified opportunities and revenue rather than the number of leads.'],
                ],
                'sec' => ['b2b lead generation agency', 'b2b digital marketing agency', 'account-based marketing', 'demand generation'],
                'ent' => ['LinkedIn Ads', 'account-based marketing', 'HubSpot', 'Salesforce', 'MQL', 'SQL', 'intent data', 'sales pipeline'],
            ],
            'technology-saas' => [
                'aud' => 'SaaS and Technology Companies',
                'title' => 'SaaS Marketing Agency UK | SaaS SEO & Growth',
                'desc' => 'SaaS marketing for UK software companies: SaaS SEO, paid acquisition, comparison pages and trial-to-paid conversion that lower CAC and grow recurring revenue.',
                'lead' => 'GTech Digital is a SaaS marketing agency that helps UK software and technology companies grow monthly recurring revenue with SaaS SEO, paid acquisition, review-site visibility and onboarding that turns free trials into paying customers.',
                'overview' => [
                    'heading' => 'What Is SaaS Marketing?',
                    'paras' => [
                        'SaaS marketing is how a software-as-a-service company attracts users, turns trials or demos into subscriptions and keeps customers paying. Because revenue is recurring, the numbers that matter are customer acquisition cost (CAC), lifetime value (LTV), monthly recurring revenue (MRR) and churn.',
                        'We work across the whole funnel: SaaS SEO and comparison pages that capture buyers researching tools, paid campaigns that bring sign-ups at a profitable CAC, and onboarding emails and in-app prompts that help new users reach value quickly.',
                    ],
                    'bullets' => ['B2B SaaS', 'B2C and prosumer apps', 'Vertical SaaS for one industry', 'Technology and IT service companies'],
                ],
                'media' => [
                    'growth' => ['Grow Recurring Revenue With a Lower CAC', ['Growth only helps if each customer pays back their acquisition cost. We track CAC payback and LTV by channel, cut campaigns that bring low-value sign-ups, and invest in the sources that bring customers who stay.'], ['CAC and payback by channel', 'LTV:CAC reporting', 'Trial and demo conversion tracking', 'Churn and expansion insight']],
                    'channels' => ['SaaS SEO, Comparison Pages and AI Search', ['Software buyers compare options on Google, G2 and Capterra, and increasingly ask ChatGPT for recommendations. We build alternative and comparison pages, integration pages and use-case content that rank, earn reviews and get your product mentioned in AI answers.'], ['SaaS SEO and content strategy', '"Alternative to" and comparison pages', 'G2 and Capterra review programmes', 'AEO and GEO for AI recommendations']],
                    'journey' => ['Turn Free Trials Into Paying Customers', ['Most trials fail because users never reach the moment the product proves its value. We map the activation steps, then use onboarding emails, in-app prompts and well-timed sales touches to get more users there.'], ['Activation and onboarding mapping', 'Lifecycle email sequences', 'Pricing page and checkout tests', 'Product-qualified lead alerts for sales']],
                    'trust' => ['Product-Led Growth Analytics and Experiments', ['Product-led growth depends on knowing what users do in the product. We connect analytics such as GA4, Mixpanel or PostHog to your CRM, run experiments on pricing and onboarding, and share what we learn every month.'], ['Product analytics setup', 'A/B tests on pricing and onboarding', 'Feature adoption reporting', 'Experiment roadmap']],
                ],
                'who' => ['Who We Work With in Software', 'Marketing for every stage of a SaaS company.', [
                    ['lucide:rocket', 'Early-stage start-ups', 'First customers and product-market fit signals.'],
                    ['lucide:trending-up', 'Scale-ups', 'Repeatable acquisition and lower CAC.'],
                    ['lucide:building-2', 'Enterprise SaaS', 'ABM, demos and long sales cycles.'],
                    ['lucide:layers', 'Vertical SaaS', 'Software for one industry, marketed in its language.'],
                    ['lucide:smartphone', 'Apps and prosumer tools', 'Sign-ups, trials and app store visibility.'],
                    ['lucide:server', 'IT and managed services', 'Qualified B2B technology leads.'],
                ]],
                'cost' => ['How Much Does a SaaS Marketing Agency Cost?', 'Most UK SaaS companies invest £2,000 to £7,500 a month in agency fees, plus ad spend; early-stage companies often start with SEO and one paid channel. Your quote depends on:', [
                    ['lucide:target', 'Growth stage', 'Start-up, scale-up or enterprise.'],
                    ['lucide:megaphone', 'Channels', 'SEO, paid, review sites and lifecycle.'],
                    ['lucide:file-text', 'Content volume', 'Comparison, use-case and integration pages.'],
                    ['lucide:chart-column', 'Analytics', 'Product and CRM data set-up.'],
                ]],
                'compare' => ['Sales-Led vs Product-Led Growth', ['', 'Sales-led', 'Product-led'], [
                    ['How people buy', 'Demo, proposal, contract', 'Free trial or freemium, then upgrade'],
                    ['Best for', 'High-value, complex products', 'Self-serve products with quick value'],
                    ['Key metrics', 'Pipeline, win rate, ACV', 'Activation, conversion to paid, churn'],
                    ['Marketing focus', 'ABM, demos, case studies', 'SEO, onboarding, in-app prompts'],
                ], 'Many SaaS companies run a hybrid: self-serve for small teams, sales for larger accounts.'],
                'faqs' => [
                    ['Which digital marketing agency is best for SaaS companies?', 'Choose an agency that reports on CAC, trial-to-paid conversion and MRR, not just traffic, and that can show SaaS case studies. GTech Digital combines SaaS SEO, paid acquisition and lifecycle marketing, and can also build product features when growth needs them.'],
                    ['What is SaaS SEO?', 'SaaS SEO is search optimisation for software companies: ranking for problem, comparison and "alternative to" searches, integration and use-case pages, and earning reviews and links. It brings a steady flow of sign-ups at a lower cost per customer than paid ads over time.'],
                    ['How much should a SaaS company spend on marketing?', 'Growing SaaS companies often spend 20 to 50 per cent of revenue on sales and marketing. The right number depends on how quickly a new customer pays back their acquisition cost; we aim for a CAC payback period of under 12 months.'],
                ],
                'sec' => ['saas seo agency', 'saas digital marketing agency', 'b2b saas marketing', 'product-led growth'],
                'ent' => ['CAC', 'LTV', 'MRR', 'churn', 'G2', 'Capterra', 'free trial', 'product-led growth'],
            ],
            'travel' => [
                'aud' => 'Travel Companies',
                'title' => 'Travel Marketing Agency UK | Travel SEO & Bookings',
                'desc' => 'Travel marketing for UK tour operators, travel agents and attractions: travel SEO, peak-season campaigns and booking journeys that turn inspiration into sales.',
                'lead' => 'GTech Digital is a travel marketing agency that helps UK tour operators, travel agents, attractions and tourism businesses turn holiday inspiration into bookings with travel SEO, paid search, social video and booking websites built for every season.',
                'overview' => [
                    'heading' => 'What Is Travel Marketing?',
                    'paras' => [
                        'Travel marketing is how a tour operator, travel agent or attraction inspires people to travel, earns their trust and wins the booking. Travellers dream on Instagram, TikTok and YouTube, research on Google, TripAdvisor and comparison sites, and book weeks or months later, often in the January peak.',
                        'We plan around that long journey: inspiring content and destination guides that rank in travel SEO, campaigns timed for peak booking periods, and a booking website that shows live prices and the protection travellers look for, such as ATOL and ABTA.',
                    ],
                    'bullets' => ['Tour operators and specialist holiday companies', 'Independent travel agents', 'Attractions, experiences and activity providers', 'Destination and tourism organisations'],
                ],
                'media' => [
                    'growth' => ['More Online Bookings at a Profitable Cost', ['We track every campaign to the booking and its value, so budget follows the trips and destinations that make money. Return on ad spend (ROAS) and cost per booking are reported each month, by season and destination.'], ['Booking and revenue tracking', 'ROAS by destination and season', 'Peak and late-deal campaigns', 'Abandoned enquiry follow-up']],
                    'channels' => ['Inspiration on Social, Intent on Search', ['Short videos on Instagram, TikTok and YouTube create the dream; search captures it when people are ready to compare. We connect both with retargeting, so travellers who watched your content see your offers when they start planning.'], ['Instagram, TikTok and YouTube content', 'Google Ads for destination searches', 'Retargeting across Meta and Google', 'Email for past and future travellers']],
                    'journey' => ['A Booking Journey With Live Prices and Availability', ['Travellers abandon sites that hide prices or make them call. We build trip and destination pages with clear itineraries, live pricing and availability where your booking system allows it, and enquiry forms for tailor-made trips.'], ['Itinerary and destination pages', 'Booking system and live price integration', 'Tailor-made enquiry forms', 'Mobile-first, fast pages']],
                    'trust' => ['ATOL, ABTA and Reviews That Build Confidence', ['Big purchases need reassurance. We show your ATOL and ABTA protection correctly, bring reviews from Trustpilot, Feefo or TripAdvisor onto your pages, and answer common worries before travellers have to ask.'], ['ATOL and ABTA details shown correctly', 'Trustpilot, Feefo and TripAdvisor reviews', 'Clear booking terms and FAQs', 'Travel SEO for destination guides']],
                ],
                'who' => ['Who We Work With in Travel', 'Marketing for the way your travellers book.', [
                    ['lucide:plane', 'Tour operators', 'Package and specialist holidays.'],
                    ['lucide:map', 'Travel agents', 'Independent and home-based agents.'],
                    ['lucide:ticket', 'Attractions and experiences', 'Day trips, tours and activities.'],
                    ['lucide:ship', 'Cruise and river travel', 'High-value, long-planned bookings.'],
                    ['lucide:mountain', 'Adventure travel', 'Small groups and active holidays.'],
                    ['lucide:landmark', 'Destinations', 'Tourism boards and visitor attractions.'],
                ]],
                'cost' => ['How Much Does Travel Marketing Cost?', 'Most UK travel companies invest £1,000 to £5,000 a month in marketing management, plus ad spend that rises in peak booking periods such as January. Your quote depends on:', [
                    ['lucide:globe', 'Destinations', 'How many trips and markets to promote.'],
                    ['lucide:calendar', 'Seasonality', 'Peak and shoulder-season campaigns.'],
                    ['lucide:megaphone', 'Channels', 'Search, social video, email and SEO.'],
                    ['lucide:camera', 'Content', 'Destination guides, photos and video.'],
                ]],
                'compare' => ['Travel SEO vs Paid Search', ['', 'Travel SEO', 'Paid search (Google Ads)'], [
                    ['Speed', 'Builds over 3 to 9 months', 'Bookings within days of launch'],
                    ['Cost per booking', 'Falls over time', 'Rises with competition in peak'],
                    ['Best for', 'Destination guides and inspiration', 'Specific trips, offers and dates'],
                    ['Lasting value', 'Pages keep ranking', 'Stops when spend stops'],
                ], 'The best results come from both: SEO for year-round demand and paid search to win peak bookings.'],
                'faqs' => [
                    ['What is a travel marketing agency?', 'A travel marketing agency helps tour operators, travel agents and tourism businesses attract and convert travellers through SEO, paid search, social media, email and booking websites. GTech Digital measures success in bookings and revenue across each season.'],
                    ['How do I market my travel business?', 'Start with a clear niche and destination focus, then build destination guides for travel SEO, run Google Ads for high-intent trip searches, share short travel videos on social, and email past travellers before peak booking periods. Show ATOL or ABTA protection and reviews to build trust.'],
                    ['What is travel SEO?', 'Travel SEO is search optimisation for travel businesses: destination and itinerary pages, travel guides that answer planning questions, and technical work so large trip catalogues are indexed. It brings travellers who are researching and booking without paying for every click.'],
                ],
                'sec' => ['travel seo agency', 'tourism marketing agency', 'tour operator marketing', 'travel digital marketing'],
                'ent' => ['ATOL', 'ABTA', 'TripAdvisor', 'Trustpilot', 'Google Travel', 'booking engine', 'ROAS', 'peak booking season'],
            ],
            'e-commerce' => [
                'aud' => 'Online Stores',
                'title' => 'Ecommerce Marketing Agency UK | Shopify & WooCommerce',
                'desc' => 'Ecommerce marketing for UK online stores on Shopify and WooCommerce: Google Shopping, paid social, email and conversion work that grows profitable revenue.',
                'lead' => 'GTech Digital is an ecommerce marketing agency that helps UK online stores on Shopify, WooCommerce and Magento grow profitable revenue with Google Shopping, paid social, ecommerce SEO, email marketing and conversion rate optimisation.',
                'overview' => [
                    'heading' => 'What Is an Ecommerce Marketing Agency?',
                    'paras' => [
                        'An ecommerce marketing agency helps online stores attract shoppers, turn visits into orders and bring customers back. The work covers Google Shopping and Performance Max, Meta and TikTok ads, ecommerce SEO, email and SMS, and improvements to product pages and checkout.',
                        'The goal is profitable growth, so we report on revenue, return on ad spend (ROAS), average order value (AOV) and customer lifetime value, and we plan ahead for peaks such as Black Friday and Christmas.',
                    ],
                    'bullets' => ['Shopify and Shopify Plus stores', 'WooCommerce and Magento stores', 'D2C brands and retailers', 'B2B and trade ecommerce'],
                ],
                'media' => [
                    'growth' => ['Profitable Revenue Growth, Not Just Traffic', ['Revenue is only good if it is profitable. We set targets by product margin, report ROAS and new-customer revenue separately, and move budget to the products and channels that grow profit.'], ['ROAS and margin-based targets', 'New vs returning customer revenue', 'Average order value growth', 'Weekly trading reports in peak']],
                    'channels' => ['Google Shopping, Paid Social and Email Working Together', ['Shoppers discover products on Instagram and TikTok, compare on Google Shopping and buy when the price and timing are right. We run Performance Max, Meta and TikTok campaigns with one product feed, and use Klaviyo email and SMS to bring them back.'], ['Google Shopping and Performance Max', 'Meta and TikTok Shop ads', 'Klaviyo email and SMS flows', 'Ecommerce SEO for categories and products']],
                    'journey' => ['Product Pages and Checkout That Convert', ['Small changes to product pages and checkout often add more revenue than extra traffic. We test images, delivery and returns messaging, reviews and checkout steps to lift conversion rate and basket size.'], ['Product page and collection tests', 'Checkout and basket abandonment fixes', 'Reviews and trust messaging', 'Bundles, upsells and free delivery thresholds']],
                    'trust' => ['Feeds, Tracking and Peak-Season Readiness', ['Accurate data keeps campaigns profitable. We fix your Google Merchant Center feed, set up server-side tracking and GA4 ecommerce events, and plan stock, offers and budgets for Black Friday, Cyber Monday and Christmas months in advance.'], ['Google Merchant Center feed optimisation', 'GA4 and server-side conversion tracking', 'Black Friday and Christmas planning', 'Stock and margin-aware bidding']],
                ],
                'who' => ['Who We Work With in Ecommerce', 'Marketing for every kind of online store.', [
                    ['simple-icons:shopify', 'Shopify brands', 'Growth for Shopify and Shopify Plus stores.'],
                    ['simple-icons:woocommerce', 'WooCommerce stores', 'Marketing and development on WordPress.'],
                    ['lucide:shirt', 'Fashion and beauty', 'Visual products, social and influencers.'],
                    ['lucide:house', 'Home and garden', 'High-value baskets and seasonal peaks.'],
                    ['lucide:package', 'B2B and trade', 'Trade accounts and repeat ordering.'],
                    ['lucide:store', 'Retailers going online', 'From shop floor to online sales.'],
                ]],
                'cost' => ['How Much Does an Ecommerce Marketing Agency Cost?', 'Most UK online stores invest £1,000 to £5,000 a month in agency fees, plus ad spend; some agencies charge a percentage of ad spend instead. Your quote depends on:', [
                    ['lucide:package', 'Catalogue size', 'How many products and categories.'],
                    ['lucide:megaphone', 'Channels', 'Shopping, social, email and SEO.'],
                    ['lucide:wallet', 'Ad spend', 'Monthly budget under management.'],
                    ['lucide:gauge', 'CRO work', 'Testing and development each month.'],
                ]],
                'compare' => ['Marketplaces vs Your Own Online Store', ['', 'Amazon and marketplaces', 'Your own store (Shopify, WooCommerce)'], [
                    ['Fees', 'Referral and fulfilment fees per sale', 'Platform and payment fees only'],
                    ['Customer data', 'Kept by the marketplace', 'Yours, for email and loyalty'],
                    ['Brand control', 'Limited', 'Full'],
                    ['Best for', 'Reach and discovery', 'Margin, repeat customers and brand'],
                ], 'Many brands sell on both, using their own store and email to build profitable repeat sales.'],
                'faqs' => [
                    ['What is an ecommerce marketing agency?', 'An ecommerce marketing agency grows online store revenue through Google Shopping, paid social, SEO, email and conversion rate optimisation. GTech Digital works with Shopify, WooCommerce and Magento stores and reports on profitable revenue, ROAS and customer lifetime value.'],
                    ['Do you work with Shopify stores?', 'Yes. As a Shopify marketing agency we run campaigns for Shopify and Shopify Plus stores, including Google Shopping feeds, Meta and TikTok ads, Klaviyo email and conversion work, and our developers can build Shopify themes and apps when the store needs it.'],
                    ['What is the average fee for an ecommerce marketing agency?', 'UK ecommerce agencies usually charge £1,000 to £5,000 a month, or 10 to 20 per cent of ad spend for paid media management. GTech Digital gives a fixed monthly fee after a free audit, so your costs do not rise automatically when you scale spend.'],
                ],
                'sec' => ['shopify marketing agency'],
                'ent' => ['Shopify', 'WooCommerce', 'Google Merchant Center', 'Performance Max', 'Klaviyo', 'ROAS', 'average order value', 'Black Friday'],
            ],
        ];
    }
}
