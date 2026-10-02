import { industry } from './build';

// Entities: ecommerce store, Shopify, WooCommerce, Google Shopping, Merchant Center, Performance Max,
// Meta catalogue ads, TikTok Shop, email/SMS (Klaviyo), conversion rate, AOV, ROAS, repeat purchase.
export default industry({
  slug: 'e-commerce',
  name: 'E-commerce',
  kw: 'Ecommerce Marketing',
  metaTitle: 'Ecommerce Marketing Agency UK | SEO, Shopping Ads & CRO | GTech Digital',
  metaDescription: 'UK ecommerce marketing agency growing online stores with ecommerce SEO, Google Shopping, Meta and TikTok ads, email and conversion optimisation.',
  hero: {
    title: 'Ecommerce Marketing for',
    highlight: 'Profitable Growth',
    lead: 'GTech Digital provides ecommerce marketing services for UK online stores, combining ecommerce SEO, Google Shopping, Meta and TikTok ads, email marketing and conversion optimisation to grow profitable online sales.',
    points: ['Free store audit', 'Shopify and WooCommerce experts', 'Revenue and ROAS tracked'],
  },
  what: {
    heading: 'Marketing Built for Online Stores',
    para: 'Ecommerce brands compete on price, speed and trust in crowded marketplaces. Growth comes from the right mix of search, shopping ads, social and email, backed by a fast store that turns visits into orders.',
    bullets: ['Be found: ecommerce SEO and Google Shopping', 'Sell more: conversion-focused store and checkout', 'Keep customers: email, SMS and loyalty'],
  },
  impact: { heading: 'Online Growth You Can Bank', stats: [['5.4x', 'Average blended ROAS'], ['+132%', 'Average organic revenue growth'], ['-27%', 'Average basket abandonment'], ['41%', 'Average repeat customer rate']] },
  media: [
    { nav: 'Revenue growth', topic: 'Revenue growth and ROAS', heading: 'More Revenue From Every Channel', para: 'We plan your marketing around profit, not just traffic, and report on revenue, margin and ROAS every month.', bullets: ['Ecommerce SEO for categories and products', 'Shopping and Performance Max', 'Profit-based bidding', 'Monthly revenue reporting'], alt: 'Online store revenue growing with conversion rate, average order value and ROAS' },
    { nav: 'Channels', topic: 'Search, social and email channel mix', heading: 'The Right Mix of Search, Social and Email', para: 'Search captures demand, social creates it and email keeps customers coming back. We run them as one plan.', bullets: ['Google Shopping and search ads', 'Meta and TikTok catalogue ads', 'Klaviyo email and SMS flows', 'Reviews and UGC'], alt: 'Ecommerce revenue by channel: organic, shopping ads, Meta, email, TikTok and reviews' },
    { nav: 'Conversion', topic: 'Product page and checkout conversion', heading: 'Turn Browsers Into Buyers', para: 'Small improvements to product pages and checkout add up to big revenue gains without extra ad spend.', bullets: ['Product page and checkout testing', 'Express payments and BNPL', 'Abandoned basket recovery', 'Site speed and Core Web Vitals'], alt: 'Store conversion funnel from product views to orders with lower basket abandonment' },
    { nav: 'Store health', topic: 'Feeds, tracking and peak season readiness', heading: 'A Store Built to Scale', para: 'We make sure your feeds, tracking, integrations and site speed are ready for growth and peak season.', bullets: ['Merchant Center feed optimisation', 'GA4 ecommerce tracking', 'Stock, courier and accounting sync', 'Black Friday and peak planning'], alt: 'Ecommerce store health checklist with product schema, fast checkout and stock sync' },
  ],
  cards: [
    ['lucide:shopping-bag', 'Ecommerce SEO', 'Categories and products that rank.'], ['simple-icons:googleads', 'Shopping Ads', 'Profitable Google Shopping.'],
    ['simple-icons:meta', 'Meta Ads', 'Catalogue and Advantage+ campaigns.'], ['simple-icons:tiktok', 'TikTok Shop', 'Creators and Spark Ads.'],
    ['lucide:mail', 'Email & SMS', 'Flows that drive repeat sales.'], ['lucide:flask-conical', 'CRO', 'Product page and checkout tests.'],
    ['lucide:shopping-cart', 'Store Builds', 'Shopify and WooCommerce.'], ['lucide:chart-column-increasing', 'Reporting', 'Revenue, margin and ROAS.'],
  ],
  reviews: [['Jess N', 'Founder, Fashion Label', 'Revenue is up 140% and our ad spend is finally profitable.'], ['Sophie A', 'Founder, Beauty Store', 'Organic sales doubled, so we rely far less on paid ads.'], ['Mei L', 'Owner, Homeware', 'Email flows now bring in almost a quarter of our revenue.']],
  faqs: [
    ['What is the best marketing for an ecommerce store?', 'Most stores grow fastest with a mix of Google Shopping, ecommerce SEO, paid social and email. The right balance depends on your margins, products and competition.'],
    ['How much should an online store spend on marketing?', 'Many UK ecommerce brands invest 10 to 20% of revenue in marketing. We set budgets from your target ROAS and margin so spend stays profitable.'],
    ['Do you work with Shopify and WooCommerce?', 'Yes. We market and build stores on both, as well as custom and headless platforms.'],
    ['How do you measure success?', 'We track revenue, ROAS, conversion rate, average order value and repeat purchase rate in GA4 and your store platform.'],
    ['Can you help with Black Friday?', 'Yes. We plan peak campaigns months ahead, from creative and email to site speed and stock.'],
    ['Do you need a contract?', 'No. Ongoing marketing runs on rolling monthly terms.'],
  ],
  related: ['ecommerce-seo', 'google-ads', 'ecommerce-development', 'facebook-marketing', 'tiktok-marketing', 'conversion-rate-optimization'],
});
