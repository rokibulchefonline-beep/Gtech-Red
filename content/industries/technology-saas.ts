import { industry } from './build';

// Entities: SaaS, B2B software, free trials, product-led growth, demo requests, MRR/ARR, CAC,
// churn, activation, comparison and alternatives pages, G2/Capterra, AI search, onboarding.
export default industry({
  slug: 'technology-saas',
  name: 'Technology & SaaS',
  kw: 'SaaS Marketing',
  metaTitle: 'SaaS Marketing Agency UK | Tech & Software Growth',
  metaDescription: 'UK SaaS marketing agency growing trials, demos and MRR with SEO, AI search, paid acquisition, LinkedIn and product-led onboarding.',
  hero: {
    title: 'SaaS Marketing That',
    highlight: 'Grows MRR',
    lead: 'GTech Digital provides SaaS marketing services for UK software and technology companies, combining SaaS SEO, AI search visibility, Google and LinkedIn ads and onboarding optimisation to grow trials, demos and recurring revenue.',
    points: ['Free growth audit', 'Trials and MRR tracked', 'SEO, paid and product-led'],
  },
  what: {
    heading: 'Growth for Software Companies',
    para: 'SaaS buyers compare tools, read reviews and ask AI assistants before signing up. Growth comes from ranking for comparisons and problems, efficient paid acquisition and onboarding that turns trials into customers.',
    bullets: ['SEO and AI search for problem and comparison queries', 'Paid acquisition with clear CAC targets', 'Onboarding and lifecycle that lift activation'],
  },
  impact: { heading: 'Lower CAC, Higher MRR', stats: [['+236%', 'Average growth in sign-ups from search'], ['11%', 'Average trial to paid'], ['116%', 'Average net revenue retention'], ['-34%', 'Average reduction in CAC']] },
  media: [
    { nav: 'Revenue', topic: 'Recurring revenue and CAC payback', heading: 'Marketing Measured in MRR', para: 'We connect marketing to trials, activation and paid conversions so every channel is judged on revenue.', bullets: ['Trial and demo tracking', 'CAC and payback reporting', 'Pricing page testing', 'MRR attribution'], alt: 'SaaS monthly recurring revenue growing with trial sign-ups, trial to paid and CAC' },
    { nav: 'Channels', topic: 'Growth channels, comparison pages and AI search', heading: 'Be the Tool Buyers Shortlist', para: 'We rank you for the problems your product solves, the tools you compete with and the questions buyers ask AI.', bullets: ['Comparison and alternatives pages', 'AI search and answer engines', 'Google and LinkedIn ads', 'Review site profiles'], alt: 'SaaS growth channels: SEO, Google Ads, LinkedIn, AI answers, comparison pages and onboarding emails' },
    { nav: 'Activation', topic: 'Trial to paid conversion and activation', heading: 'Turn Trials Into Customers', para: 'Most churn starts in the first week. We improve onboarding so users reach value fast and upgrade.', bullets: ['Onboarding email sequences', 'In-app guidance', 'Activation milestones', 'Upgrade prompts'], alt: 'Trial to paid funnel from visitors to sign-ups, activated users and paying customers' },
    { nav: 'Product-led', topic: 'Product-led growth analytics and testing', heading: 'Built for Product-Led Growth', para: 'We set up the analytics and experiments you need to grow efficiently and prove what works.', bullets: ['Product analytics and events', 'A/B testing roadmap', 'Lifecycle and churn reporting', 'G2 and Capterra reviews'], alt: 'SaaS checklist with product analytics, comparison pages, pricing tests and AI search visibility' },
  ],
  cards: [
    ['lucide:search', 'SaaS SEO', 'Problem and comparison content.'], ['lucide:sparkles', 'AI Search', 'Get recommended by AI.'],
    ['simple-icons:googleads', 'Google Ads', 'Trials at target CAC.'], ['simple-icons:linkedin', 'LinkedIn', 'Demos from decision-makers.'],
    ['lucide:mail', 'Lifecycle Email', 'Onboarding and upgrades.'], ['lucide:flask-conical', 'CRO', 'Pricing and sign-up tests.'],
    ['lucide:cloud', 'Product Development', 'Build and scale your SaaS.'], ['lucide:chart-column-increasing', 'Reporting', 'MRR, CAC and churn.'],
  ],
  reviews: [['Elliot W', 'Founder, PropTech SaaS', 'Search is now our biggest source of trials, at a fraction of our ad CAC.'], ['Hannah V', 'CEO, HR Software', 'Their onboarding work doubled our trial-to-paid rate.'], ['Simran K', 'Head of Growth, B2B SaaS', 'We now show up when buyers ask ChatGPT for tools like ours.']],
  faqs: [
    ['What is the best marketing for SaaS?', 'Usually a mix of SEO for problem and comparison searches, AI search visibility, paid search and LinkedIn for demand capture, and lifecycle email to convert trials. The right mix depends on your price point and sales model.'],
    ['How do you reduce customer acquisition cost?', 'By growing organic and AI search, improving sign-up and trial conversion, and cutting paid spend that does not lead to paying customers. We report CAC payback by channel, so budget moves to what pays back fastest.'],
    ['Do you work with B2B and B2C software?', 'Yes. We work with both self-serve and sales-led SaaS and technology companies, adapting tactics to price point, buyer and sales cycle. B2B SaaS marketing usually leans on content and LinkedIn, B2C on search and social.'],
    ['Can you help us appear in ChatGPT and AI answers?', 'Yes. Our AEO and GEO work improves how AI assistants such as ChatGPT and Perplexity understand and recommend your product, using clear comparison content, schema and consistent mentions on trusted sites.'],
    ['Can you also build our product?', 'Yes. Our development team builds SaaS products, MVPs and integrations, so you can get product and marketing from one partner. This also means your marketing site, onboarding and analytics are connected from day one.'],
    ['Do you need a contract?', 'No. GTech Digital runs SaaS marketing on rolling monthly terms. We recommend six months to build content and demand, but you are never locked in.'],
    ['What is product-led growth?', 'Product-led growth means the product itself drives sign-ups, upgrades and referrals, usually through a free trial or freemium plan. We support it with onboarding emails, in-app prompts, activation tracking and experiments, so more trial users become paying customers.'],
  ],
  related: ['search-engine-optimization', 'saas-product-development', 'linkedin-marketing', 'google-ads', 'conversion-rate-optimization', 'content-marketing'],
});
