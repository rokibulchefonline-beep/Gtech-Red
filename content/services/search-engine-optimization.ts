import type { ServiceContent } from '../types';

// Pilot long-form page. Entity map used for this page:
// SEO -> Google Search (crawling, indexing, ranking), search intent, keywords, technical SEO
// (crawlability, indexation, site architecture, Core Web Vitals: LCP, INP, CLS), on-page SEO
// (title tags, meta descriptions, headings, internal links, structured data), content
// (topical authority, E-E-A-T, helpful content), off-page (backlinks, digital PR, citations),
// local SEO (Google Business Profile, NAP, reviews, local pack), ecommerce SEO, AI Overviews,
// measurement (Google Search Console, GA4, conversions), pricing and timelines in the UK.

const content: ServiceContent = {
  slug: 'search-engine-optimization',
  metaTitle: 'SEO Agency UK | Search Engine Optimisation Services | GTech Digital',
  metaDescription:
    'UK SEO agency delivering technical SEO, content, local SEO and link building that turn Google rankings into leads and sales. Get a free SEO audit from GTech Digital.',
  hero: {
    eyebrow: 'SEO Services UK',
    title: 'SEO Agency That Turns Rankings Into',
    highlight: 'Revenue',
    lead:
      'We help UK businesses earn more qualified traffic from Google with technical SEO, search-led content, local SEO and white-hat link building, all measured against the leads and sales they produce.',
    motion: '/services/seo.webp',
    points: ['Free SEO audit', 'No long lock-in contracts', 'Monthly plain-English reports'],
  },
  sections: [
    {
      type: 'text',
      id: 'what-is-seo',
      nav: 'What is SEO',
      eyebrow: 'The basics',
      heading: 'What Is Search Engine Optimisation?',
      paras: [
        'Search engine optimisation (SEO) is the practice of improving a website so that it appears higher in the unpaid, organic results of search engines such as Google and Bing. In the UK, Google handles more than nine in every ten searches, so for most businesses SEO means understanding how Google crawls, indexes and ranks pages, and then making your site the most useful answer for the searches your customers make.',
        'Unlike paid search, you do not pay per click for organic traffic. Instead, you invest in the quality of your website, your content and your reputation across the web. That investment compounds: a page that ranks well for a valuable search term can bring in enquiries every day for years, long after the work that earned the ranking is done.',
        'Google ranks pages using hundreds of signals, but they fall into three broad groups. Technical signals show whether Google can find, render and understand your pages. Relevance signals show whether a page actually answers the search, which depends on your content, headings and structured data. Authority and trust signals, mostly links and mentions from other reputable websites, show whether others vouch for you. Good SEO works on all three at once.',
      ],
    },
    {
      type: 'media',
      id: 'why-seo',
      nav: 'Why it matters',
      eyebrow: 'Why invest',
      heading: 'Why SEO Is Still the Best Long-Term Growth Channel',
      image: '/pages/seo/why.webp',
      alt: 'Organic traffic and leads growing over twelve months',
      paras: [
        'People who search on Google have already told you what they want. Someone typing "accountant near me" or "best restaurant ordering system" is closer to buying than someone scrolling social media, which is why organic search traffic tends to convert well and why it remains the largest source of website visits for most UK businesses.',
        'SEO also lowers your cost of acquiring customers over time. Paid ads stop the moment you stop paying; rankings do not. As your organic visibility grows, you can rely less on paid media, or use your ad budget more selectively on the terms where you do not yet rank.',
      ],
      bullets: [
        'High-intent traffic from people already looking for your service',
        'Lower cost per lead as rankings mature',
        'Visibility that keeps working outside office hours',
        'Credibility: buyers trust businesses that rank organically',
        'A durable asset that supports your ads, social and email',
      ],
    },
    {
      type: 'cards',
      id: 'services',
      nav: "What's included",
      eyebrow: 'Our SEO services',
      heading: 'Everything Included in Our SEO Service',
      intro:
        'Every campaign is built from the same proven building blocks. We weight them differently depending on your website, your competitors and your goals.',
      cards: [
        { icon: 'lucide:settings-2', title: 'Technical SEO', text: 'Crawlability, indexation, site architecture, redirects, canonical tags, XML sitemaps, robots.txt and page speed, so Google can find and understand every page that matters.' },
        { icon: 'lucide:search', title: 'Keyword & Intent Research', text: 'We map the searches your customers make, group them by intent (informational, commercial and transactional) and match each group to the right page.' },
        { icon: 'lucide:file-text', title: 'On-Page Optimisation', text: 'Title tags, meta descriptions, headings, internal links, image alt text and schema markup, rewritten so each page clearly answers the search it targets.' },
        { icon: 'lucide:pen-line', title: 'Content Strategy & Writing', text: 'Service pages, guides and articles planned around topic clusters that build topical authority and demonstrate experience, expertise and trust (E-E-A-T).' },
        { icon: 'lucide:link', title: 'Link Building & Digital PR', text: 'White-hat outreach, digital PR and partnerships that earn relevant backlinks from real UK and industry websites. No link farms, no shortcuts.' },
        { icon: 'lucide:map-pin', title: 'Local SEO', text: 'Google Business Profile optimisation, consistent NAP citations, review generation and location pages to win the local map pack in your towns and cities.' },
        { icon: 'lucide:shopping-cart', title: 'Ecommerce SEO', text: 'Category and product page optimisation, faceted navigation control, product schema and internal linking for Shopify, WooCommerce and custom stores.' },
        { icon: 'lucide:chart-column-increasing', title: 'Reporting & Analytics', text: 'Google Search Console, GA4 and rank tracking combined into one monthly report that shows rankings, traffic, leads and revenue, in plain English.' },
      ],
    },
    {
      type: 'media',
      id: 'technical-seo',
      nav: 'Technical SEO',
      eyebrow: 'Foundations',
      heading: 'Technical SEO: Making Your Site Easy to Crawl, Fast and Trustworthy',
      image: '/pages/seo/technical.webp',
      alt: 'Technical SEO audit dashboard showing Core Web Vitals scores',
      flip: true,
      paras: [
        'Great content cannot rank if Google struggles to reach it. Our technical SEO audit crawls your whole site the way Googlebot does and checks every factor that affects how your pages are discovered, rendered and indexed. We then prioritise fixes by impact, so your developers, or ours, work on what moves rankings first.',
        'Page experience matters too. Google measures real-user performance through Core Web Vitals: Largest Contentful Paint (LCP) should be 2.5 seconds or less, Interaction to Next Paint (INP) 200 milliseconds or less, and Cumulative Layout Shift (CLS) 0.1 or less. Faster pages rank and convert better, so we treat speed as both an SEO and a sales issue.',
      ],
      bullets: [
        'Crawl errors, broken links and redirect chains',
        'Index coverage, duplicate content and canonical tags',
        'Site architecture and internal linking depth',
        'Core Web Vitals: LCP, INP and CLS',
        'Mobile usability and JavaScript rendering',
        'Structured data (schema.org) for rich results',
      ],
    },
    {
      type: 'text',
      id: 'content-and-authority',
      nav: 'Content & links',
      eyebrow: 'Relevance & authority',
      heading: 'Content and Links That Build Topical Authority',
      paras: [
        'Google rewards websites that cover a subject thoroughly and helpfully, not those that repeat a keyword the most. We build topic clusters: a strong pillar page for each core service, supported by in-depth articles that answer the specific questions your buyers ask before they enquire. Each article links back to the service page it supports, which helps Google understand your expertise and spreads authority through your site.',
        'Every piece of content is written for people first. That means real answers, original examples, clear structure and the experience of the people who actually do the work. This is what Google describes as E-E-A-T: experience, expertise, authoritativeness and trust. It also means content that is easy to quote, so you are more likely to be cited in Google AI Overviews and other AI-powered search results.',
        'Backlinks remain one of the strongest ranking signals, but quality matters far more than quantity. One link from a respected UK publication or trade association is worth more than hundreds of low-quality directory links, which can actively harm you. We earn links through digital PR campaigns, expert commentary, useful resources and genuine industry relationships, and we review your existing backlink profile for anything risky.',
      ],
    },
    {
      type: 'media',
      id: 'local-seo',
      nav: 'Local SEO',
      eyebrow: 'Win your area',
      heading: 'Local SEO for UK Businesses That Serve Customers Nearby',
      image: '/pages/seo/local.webp',
      alt: 'Local map pack results with a business pin and reviews',
      paras: [
        'If you serve customers in specific towns, cities or postcodes, the Google local pack, the map with three businesses under it, is often the most valuable spot on the page. Local rankings depend on relevance, distance and prominence, and you can influence two of the three.',
        'We fully optimise your Google Business Profile with the right primary and secondary categories, services, photos, posts and Q&A. We make sure your business name, address and phone number (NAP) match across UK directories and citation sites, build a steady flow of genuine reviews, and create location pages that are useful rather than copied.',
      ],
      bullets: [
        'Google Business Profile setup and optimisation',
        'NAP consistency and UK citation building',
        'Review strategy and response templates',
        'Location and service-area landing pages',
        'Local link building and community PR',
      ],
    },
    {
      type: 'steps',
      id: 'process',
      nav: 'Our process',
      eyebrow: 'How we work',
      heading: 'Our 6-Step SEO Process',
      intro: 'A clear, repeatable process means you always know what we are doing, why, and what happens next.',
      steps: [
        { title: 'Free SEO audit & discovery', text: 'We review your website, rankings, competitors and analytics, and learn about your customers, margins and goals so we target the searches that bring profitable work.' },
        { title: 'Keyword & competitor strategy', text: 'We build a keyword map grouped by intent and value, benchmark the sites outranking you, and agree measurable targets for traffic, leads and revenue.' },
        { title: 'Technical fixes', text: 'We resolve the crawl, indexation, speed and structure issues holding your site back, starting with the changes that have the biggest impact.' },
        { title: 'On-page & content', text: 'We optimise existing pages and publish new service pages and articles from a monthly content plan, each one written to answer a specific search.' },
        { title: 'Authority building', text: 'We earn relevant backlinks and local citations through outreach and digital PR, steadily increasing the trust Google places in your domain.' },
        { title: 'Measure, report & improve', text: 'Every month you get a clear report on rankings, traffic, leads and revenue, plus what we will do next. We double down on what works and cut what does not.' },
      ],
    },
    {
      type: 'table',
      id: 'seo-vs-ppc',
      nav: 'SEO vs PPC',
      eyebrow: 'Compare',
      heading: 'SEO vs PPC: Which Is Right for Your Business?',
      intro: 'SEO and pay-per-click (PPC) advertising both put you in front of people searching on Google, but they work very differently. Most growing businesses use both.',
      columns: ['Factor', 'SEO (organic)', 'PPC (Google Ads)'],
      rows: [
        ['Cost model', 'Ongoing investment in content, technical work and links', 'Pay for every click, often £1 to £10+ in competitive UK sectors'],
        ['Speed of results', 'Typically 3 to 6 months for meaningful movement', 'Traffic from the day ads go live'],
        ['Longevity', 'Rankings keep delivering after the work is done', 'Traffic stops when spend stops'],
        ['Trust', 'Organic results are often trusted more by searchers', 'Clearly labelled as sponsored'],
        ['Best for', 'Long-term growth and lowering cost per lead', 'Fast tests, launches, seasonal offers and gaps'],
      ],
      note: 'Our usual recommendation: use Google Ads for immediate leads while SEO builds, then shift budget as organic rankings take over.',
    },
    {
      type: 'cards',
      id: 'pricing',
      nav: 'Pricing',
      eyebrow: 'Investment',
      heading: 'How Much Does SEO Cost in the UK?',
      intro:
        'There is no one-size price because the work depends on how competitive your market is and how much needs fixing. As a guide, monthly SEO for UK small and medium businesses commonly ranges from around £500 for a focused local campaign to £5,000 or more for national or ecommerce campaigns. We quote a fixed monthly fee after your free audit, based on these factors:',
      cards: [
        { icon: 'lucide:target', title: 'Competition', text: 'Ranking for "plumber Leeds" takes less effort than "car insurance". The stronger the sites you are up against, the more content and authority you need.' },
        { icon: 'lucide:layout-template', title: 'Website size & health', text: 'A 10-page brochure site and a 5,000-product store need very different amounts of technical and on-page work.' },
        { icon: 'lucide:map-pin', title: 'Geographic scope', text: 'One town, several cities or the whole of the UK. Wider targeting means more pages, more links and more keywords to cover.' },
        { icon: 'lucide:pen-line', title: 'Content volume', text: 'How many new pages and articles you need each month to close the gap with competitors and build topical authority.' },
      ],
    },
    {
      type: 'metrics',
      id: 'results',
      nav: 'Results',
      eyebrow: 'What we measure',
      heading: 'How We Measure SEO Success',
      intro: 'Rankings are a means to an end. We report on the numbers that show SEO is growing your business, using Google Search Console, GA4 and your CRM.',
      metrics: [
        { label: 'Visibility', value: 'Rankings', text: 'Positions for your target keywords and how many sit on page one of Google.' },
        { label: 'Traffic', value: 'Organic visits', text: 'Non-branded organic sessions to your service and product pages, not just your blog.' },
        { label: 'Engagement', value: 'CTR', text: 'Click-through rate from Google results, improved with better titles and rich results.' },
        { label: 'Outcomes', value: 'Leads & sales', text: 'Calls, form fills, bookings and revenue attributed to organic search in GA4.' },
      ],
    },
    {
      type: 'text',
      id: 'why-gtech',
      nav: 'Why GTech',
      eyebrow: 'Why choose us',
      heading: 'Why Choose GTech Digital as Your SEO Agency',
      paras: [
        'We are a full-service digital agency, which means your SEO is planned alongside your website, paid media and content rather than in isolation. When a technical fix needs a developer, we have them in-house. When a page needs better copy or design to convert the traffic SEO brings, our writers and designers handle it.',
        'We only use white-hat techniques that follow Google Search Essentials, so the rankings we build are designed to last through algorithm updates. You own everything we create, from content to reports, and you can see exactly what we have done each month.',
      ],
      bullets: [
        'Senior specialists, not juniors learning on your account',
        'Strategy tied to leads and revenue, not vanity metrics',
        'Developers, writers and designers under one roof',
        'Transparent monthly reporting and a dedicated contact',
        'Flexible terms: we keep clients by results, not contracts',
      ],
    },
  ],
  faqs: [
    { q: 'How long does SEO take to work?', a: 'Most websites see early improvements within the first 1 to 3 months as technical fixes and on-page changes are picked up, with meaningful growth in traffic and leads typically after 3 to 6 months. Competitive national keywords can take 6 to 12 months or longer. SEO compounds, so results usually accelerate over time.' },
    { q: 'How much does SEO cost in the UK?', a: 'Monthly SEO retainers for UK small and medium businesses commonly range from around £500 for focused local SEO to £5,000 or more for national and ecommerce campaigns. The price depends on competition, website size, geographic scope and content volume. We provide a fixed quote after a free audit.' },
    { q: 'Can you guarantee first place on Google?', a: 'No honest agency can guarantee a specific ranking, because Google controls its algorithm and competitors are always changing. Be wary of anyone who does. What we commit to is a clear strategy, measurable targets and transparent monthly reporting on progress.' },
    { q: 'What is the difference between local SEO and national SEO?', a: 'Local SEO focuses on ranking in a specific area, especially in the Google map pack, using your Google Business Profile, citations, reviews and location pages. National SEO targets searches across the whole UK, which usually needs more content and stronger backlinks to compete.' },
    { q: 'Do I need to sign a long contract?', a: 'No. We work on rolling monthly agreements after an initial setup period, because we believe results should keep clients, not contracts. We do recommend committing at least six months to give SEO a fair chance to deliver.' },
    { q: 'Will SEO work with my existing website?', a: 'In most cases, yes. We work with WordPress, Shopify, WooCommerce, Webflow, Wix, Squarespace and custom-built sites. If your platform is seriously limiting your rankings, we will explain the trade-offs and options, including a rebuild by our web team if it makes commercial sense.' },
    { q: 'How do AI Overviews affect SEO?', a: 'Google AI Overviews summarise answers at the top of some results and cite the sources they draw from. Clear, well-structured, expert content with schema markup is more likely to be cited. The fundamentals of good SEO, helpful content, strong technical health and authority, are what earn those citations.' },
    { q: 'What do your monthly SEO reports include?', a: 'Each report covers keyword rankings, organic traffic, click-through rate, leads and conversions from organic search, the work completed that month and the plan for next month. We explain what the numbers mean for your business, not just what they are.' },
    { q: 'Is SEO better than Google Ads?', a: 'They do different jobs. Google Ads delivers traffic immediately but stops when you stop paying. SEO takes longer to build but keeps delivering and usually lowers your cost per lead over time. Most businesses get the best results by running both together.' },
  ],
  related: ['google-ads', 'content-marketing', 'seo-backlinks', 'website-design', 'conversion-rate-optimization', 'reputation-management'],
  industries: ['e-commerce', 'hospitality-hotels', 'healthcare', 'real-estate', 'finance', 'b2b-marketing'],
};

export default content;
