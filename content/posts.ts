// Demo blog posts, shown until posts are published in MongoDB (collection `posts`).
// Body format: simple Markdown. "## " = section heading (added to the table of contents),
// "### " = sub-heading, "- " = bullet, **bold** for emphasis, blank line = new paragraph.

export type DemoPost = {
  slug: string;
  title: string;
  excerpt: string;
  category: string;
  date: string; // ISO date
  image: string;
  featured?: boolean;
  body: string;
};

export const demoPosts: DemoPost[] = [
  {
    slug: 'aeo-geo-guide',
    title: 'AEO and GEO Explained: How to Get Your Business Into AI Answers',
    excerpt: 'Search is moving into AI Overviews, ChatGPT and Perplexity. Here is how answer engine and generative engine optimisation work, and what to do first.',
    category: 'SEO',
    date: '2026-09-24',
    image: '/posts/aeo-geo-guide.webp',
    featured: true,
    body: `More people now get answers without clicking a single link. Google shows AI Overviews at the top of many searches, and tools like ChatGPT, Perplexity and Gemini answer questions directly. If your business is not mentioned in those answers, you are invisible to a growing share of buyers.

The good news is that the foundations are familiar. Answer engine optimisation (AEO) and generative engine optimisation (GEO) build on good SEO, with a stronger focus on clear answers, trusted sources and consistent facts about your brand.

## What Is AEO?

Answer engine optimisation means structuring your content so search engines can lift a clear, direct answer from it. It is how you win featured snippets, People Also Ask boxes and voice answers.

- Answer one question per section, in the first one or two sentences
- Use the question as a heading, written the way people ask it
- Add FAQ and HowTo schema where it genuinely fits
- Keep answers short, factual and free of fluff

## What Is GEO?

Generative engine optimisation is about being cited or recommended inside AI-generated answers. Large language models build their answers from sources they trust, so GEO focuses on authority and consistency across the web, not just your own site.

- Be mentioned on trusted third-party sites, directories and press
- Keep your name, services and locations consistent everywhere
- Publish original data, expert opinion and clear comparisons
- Make your About, service and pricing pages easy to understand

## How AI Chooses Which Brands to Mention

AI tools tend to recommend brands that appear repeatedly in reliable sources, with clear and consistent descriptions. Reviews, case studies, expert quotes and structured data all help models understand who you are and when you are relevant.

**In short:** if humans and search engines already trust you, AI tools are far more likely to mention you.

## A Simple AEO and GEO Checklist

- Audit the questions your customers ask and answer each one clearly
- Add Organization, Service and FAQ schema to key pages
- Earn mentions on industry sites, local directories and news outlets
- Collect reviews on Google and trusted review platforms
- Track brand mentions in AI tools every month

## How Long Does It Take?

Structured answers can be picked up within weeks. Building the authority that earns AI recommendations usually takes three to six months of steady work, much like traditional SEO.

## Where to Start

Start with your most valuable service pages. Make sure each one clearly explains what you do, who it is for, what it costs and why you are trusted. That single step improves SEO, AEO and GEO at the same time.`,
  },
  {
    slug: 'local-seo-checklist',
    title: 'Local SEO Checklist: 12 Steps to Rank in the Google Map Pack',
    excerpt: 'The three businesses on the map win most local calls. Use this practical checklist to improve your Google Business Profile, reviews and local signals.',
    category: 'Local SEO',
    date: '2026-09-17',
    image: '/posts/local-seo-checklist.webp',
    body: `When someone searches for a plumber, dentist or restaurant near them, Google shows a map with three businesses. Those three listings win most of the calls, clicks and direction requests. This checklist covers the steps that matter most.

## Optimise Your Google Business Profile

Your profile is the single biggest local ranking factor you control.

- Choose the most accurate primary category, then add relevant secondary ones
- Add every service you offer, with short descriptions
- Set accurate opening hours, including holidays
- Upload real photos of your team, premises and work every month
- Post updates and offers weekly
- Answer common questions in the Q&A section

## Keep Your Details Consistent

Google cross-checks your name, address and phone number (NAP) across the web. Inconsistent details reduce trust.

- Use exactly the same business name, address and phone everywhere
- Fix old listings on directories such as Yell, Bing Places and Apple Business Connect
- Update details immediately if you move or change number

## Earn and Answer Reviews

Reviews influence both rankings and whether people choose you.

- Ask every happy customer for a review, with a direct link
- Reply to every review, positive or negative, within a few days
- Never buy reviews or offer incentives, which breaks Google's rules

## Build Local Relevance on Your Website

- Create a page for each main service and each area you serve
- Add your address and LocalBusiness schema to your contact page
- Embed a map and include local landmarks or areas served
- Earn links from local news, sponsorships and business associations

## Track the Right Results

Rankings vary street by street, so track map positions from several postcodes. More importantly, track calls, direction requests and enquiries from your profile.

## How Long Does Local SEO Take?

Many businesses see map pack improvements within four to eight weeks. In busy areas, expect three to six months of consistent work.`,
  },
  {
    slug: 'website-cost-uk',
    title: 'How Much Does a Website Cost in the UK in 2026?',
    excerpt: 'From simple brochure sites to custom ecommerce, here are realistic UK website prices, what drives the cost, and how to avoid paying for features you do not need.',
    category: 'Web Design',
    date: '2026-09-10',
    image: '/posts/website-cost-uk.webp',
    body: `Website prices in the UK range from a few hundred pounds to well over £50,000. The right budget depends on what the website needs to do for your business. Here is a realistic breakdown.

## Typical UK Website Prices

- **Starter website (5 to 10 pages):** £1,500 to £4,000
- **Custom business website:** £4,000 to £12,000
- **Ecommerce store:** £5,000 to £40,000
- **Web application or portal:** £12,000 to £80,000+

## What Affects the Cost?

### Design

A template-based design is cheaper, while a fully custom design built around your brand and customers takes more time but usually converts better.

### Number of Page Templates

Cost is driven more by unique layouts than total pages. Ten blog posts share one template, while a homepage, service page and landing page each need their own.

### Features and Integrations

Booking systems, member areas, CRM integrations and custom calculators all add development time.

### Content

Copywriting, photography and moving content from an old website are often underestimated. Plan for them early.

## Ongoing Costs to Budget For

- Domain name: around £10 to £30 per year
- Hosting: £10 to £100+ per month
- Maintenance and security: £50 to £500 per month
- SEO and marketing to bring visitors

## How to Get Better Value

- Be clear on the main goal of the website, such as enquiries, sales or bookings
- Start with the essential features, then add more once you see results
- Choose a platform your team can update without a developer
- Make sure the website is fast, mobile-friendly and SEO-ready from launch

## The Bottom Line

The cheapest website is rarely the best value. A website that loads fast, ranks well and turns visitors into customers pays for itself, while a cheap site that nobody finds costs more in lost business.`,
  },
  {
    slug: 'seo-vs-google-ads',
    title: 'SEO vs Google Ads: Which Is Better for UK Small Businesses?',
    excerpt: 'Should you invest in SEO, Google Ads or both? We compare cost, speed and long-term value so you can choose the right mix for your budget.',
    category: 'Paid Ads',
    date: '2026-09-03',
    image: '/posts/seo-vs-google-ads.webp',
    body: `SEO and Google Ads both put your business in front of people searching on Google. They work very differently, though, and most businesses get the best results by using them together.

## How Google Ads Works

With Google Ads you pay each time someone clicks your advert. Ads can appear within hours of launching, which makes them ideal for quick leads, new offers and testing which keywords convert.

- Results from day one
- Precise control over budget, locations and timing
- Traffic stops as soon as you stop paying

## How SEO Works

SEO improves your website so it ranks in the free, organic results. It takes longer to build, but clicks are free and results keep working long after the work is done.

- Usually three to six months to see strong results
- No cost per click
- Builds trust, as many people prefer organic results

## Cost Compared

Google Ads costs depend on competition. In many UK service industries, clicks cost between £1 and £10, sometimes far more. SEO usually costs a fixed monthly fee, and the cost per lead tends to fall over time as rankings grow.

## When to Choose Google Ads

- You need leads quickly
- You are launching a new product or offer
- You want to test which services and keywords convert

## When to Choose SEO

- You want a steady flow of leads without paying per click
- You plan to grow over the next one to three years
- You want to appear in AI Overviews and answer engines too

## Why Most Businesses Use Both

Ads bring leads while SEO builds. Ad data shows which keywords convert, which guides your SEO priorities. As organic rankings grow, you can reduce ad spend on those terms and lower your overall cost per lead.`,
  },
  {
    slug: 'social-media-strategy',
    title: 'How to Build a Social Media Strategy That Actually Drives Sales',
    excerpt: 'Posting more is not a strategy. Here is a simple framework to choose the right platforms, plan content and connect social media to real revenue.',
    category: 'Social Media',
    date: '2026-08-27',
    image: '/posts/social-media-strategy.webp',
    body: `Many businesses post on social media every week but cannot say what it brings in. A good strategy connects every post, ad and reply to a business goal.

## Start With One Clear Goal

Choose the main job social media should do for you: brand awareness, leads, online sales or customer loyalty. Your goal decides which platforms, content and metrics matter.

## Choose the Right Platforms

You do not need to be everywhere.

- **Facebook:** local audiences and adults over 30
- **Instagram:** visual products, lifestyle and hospitality
- **LinkedIn:** B2B services and recruitment
- **TikTok:** younger audiences and products that are easy to demonstrate
- **Pinterest:** home, fashion, food and planned purchases

## Plan Content Pillars

Content pillars are three to five themes you post about regularly. For example: tips and education, behind the scenes, customer stories, offers and community.

## Mix Organic and Paid

Organic posts build trust with your followers. Paid social reaches new people and drives sales. Most growing brands boost their best organic posts as ads, which keeps costs low and results authentic.

## Measure What Matters

- Reach and engagement show whether content is working
- Clicks, leads and sales show whether it is profitable
- Use tracking links, the Meta Pixel and GA4 to connect social to revenue

## Review and Improve Monthly

Each month, look at your best and worst posts, double down on what works and drop what does not. Small, consistent improvements beat sudden bursts of activity.`,
  },
  {
    slug: 'custom-software-vs-off-the-shelf',
    title: 'Custom Software vs Off-the-Shelf: How to Decide',
    excerpt: 'Spreadsheets and workarounds slowing you down? Compare costs, flexibility and risk to decide whether to buy software or build your own.',
    category: 'Software',
    date: '2026-08-20',
    image: '/posts/custom-software-vs-off-the-shelf.webp',
    body: `As businesses grow, they often outgrow spreadsheets and basic tools. The question is whether to buy an off-the-shelf product or build custom software. Both can be the right answer.

## When Off-the-Shelf Software Makes Sense

- Your process is similar to most businesses in your sector
- You need something working within days
- The monthly licence cost is reasonable for your team size

Tools such as Xero, HubSpot and Shopify are excellent when they fit how you work.

## When Custom Software Makes Sense

- Your team relies on workarounds and manual copying between systems
- Licence fees grow with every new user
- Your process gives you a competitive advantage
- You need several systems to share data reliably

## Comparing the Costs

Off-the-shelf tools have a low starting cost but ongoing per-user fees. Custom software has an upfront build cost, then lower running costs and no per-user fees. Over three to five years, custom software can be cheaper for growing teams.

## Reducing the Risk of a Custom Build

- Start with a discovery phase to define the scope
- Build a focused first version, then add features
- Insist on automated testing and documentation
- Make sure you own the code and data

## The Hybrid Option

Often the best answer is both: keep proven tools like Xero or Shopify, and build custom software or integrations around them to automate the gaps.`,
  },
];
