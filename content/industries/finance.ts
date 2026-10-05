import { industry } from './build';

// Entities: financial advisers, mortgage brokers, accountants, wealth managers, lenders, insurance,
// FCA financial promotions, Consumer Duty, risk warnings, YMYL content, Trustpilot, calculators.
export default industry({
  slug: 'finance',
  name: 'Finance',
  kw: 'Financial Services Marketing',
  h1: 'Financial Marketing',
  metaTitle: 'Financial Marketing Agency UK | Compliant Marketing',
  metaDescription: 'UK financial marketing agency for advisers, brokers, accountants and lenders: compliant SEO, Google Ads, LinkedIn and secure websites that win clients.',
  hero: {
    title: 'Financial Marketing That',
    highlight: 'Wins Clients',
    lead: 'GTech Digital provides financial marketing for UK advisers, mortgage brokers, accountants and lenders, combining financial SEO, Google and LinkedIn ads and FCA-compliant content to win qualified client enquiries.',
    points: ['Free marketing audit', 'FCA-aware campaigns', 'Compliance sign-off built in'],
  },
  what: {
    heading: 'Marketing in a Regulated Industry',
    para: 'Financial decisions are high-stakes, so clients research carefully and look for expertise and trust. Marketing must be clear, fair and not misleading, and it needs a reliable compliance process.',
    bullets: ['Expert content that ranks for financial questions', 'Compliant ads with the right risk warnings', 'Secure websites, calculators and portals'],
  },
  impact: { heading: 'Growth Without Compliance Risk', stats: [['+68%', 'Average growth in new clients'], ['£52', 'Average cost per enquiry'], ['47%', 'Average consultation to client'], ['100%', 'Campaigns with compliance sign-off']] },
  media: [
    { nav: 'Enquiries', topic: 'Client growth and qualified enquiries', heading: 'More Qualified Enquiries', para: 'We target people actively looking for advice, mortgages or accounting help, and filter out poor-fit leads.', bullets: ['High-intent search campaigns', 'Callback and booking forms', 'Lead qualification', 'Cost per client reporting'], alt: 'Qualified financial enquiries growing with new clients, cost per enquiry and compliance sign-off' },
    { nav: 'Channels', topic: 'Search, LinkedIn and review channels', heading: 'Expertise That Shows Up in Search', para: 'Helpful guides and calculators earn rankings and trust, while Google and LinkedIn reach people ready to act.', bullets: ['Financial SEO and guides', 'Google Ads for advice searches', 'LinkedIn for B2B finance', 'Trustpilot and Google reviews'], alt: 'How clients find you: SEO, Google Ads, LinkedIn, guides, reviews and email' },
    { nav: 'Journey', topic: 'Client journey from research to consultation', heading: 'From First Question to New Client', para: 'Clear explanations, calculators and fast callbacks guide people from research to a first consultation.', bullets: ['Calculators and tools', 'Consultation booking', 'Callbacks within the hour', 'Email nurture'], alt: 'Financial client journey from guide visits to enquiries, consultations and new clients' },
    { nav: 'Compliance', topic: 'FCA financial promotions and Consumer Duty compliance', heading: 'Marketing That Passes Compliance', para: 'We build FCA financial promotion rules and Consumer Duty into every page and campaign, with a clear sign-off workflow.', bullets: ['FCA financial promotions rules', 'Consumer Duty-friendly content', 'Risk warnings and disclaimers', 'Secure forms and portals'], alt: 'Compliant financial marketing checklist with FCA rules, Consumer Duty, risk warnings and sign-off' },
  ],
  cards: [
    ['lucide:search', 'Financial SEO', 'Expert content that ranks.'], ['simple-icons:googleads', 'Google Ads', 'Compliant lead campaigns.'],
    ['simple-icons:linkedin', 'LinkedIn', 'B2B finance marketing.'], ['lucide:calculator', 'Calculators', 'Tools that generate leads.'],
    ['lucide:star', 'Reviews', 'Trustpilot and Google.'], ['lucide:shield-check', 'Compliance Workflow', 'Sign-off built in.'],
    ['lucide:monitor', 'Secure Websites', 'Fast and trustworthy.'], ['lucide:app-window', 'Client Portals', 'Documents and onboarding.'],
  ],
  reviews: [['Mark S', 'CEO, Financial Advisers', 'Enquiries are up and compliance signs off campaigns without rework.'], ['Helen G', 'Director, Mortgage Broker', 'Our guides rank on page one and bring in steady, quality leads.'], ['Tom A', 'Partner, Accountancy Firm', 'New client enquiries grew by two thirds in a year.']],
  faqs: [
    ['How do financial advisers get more clients online?', 'Publish expert content that answers common questions, rank in local and national search, collect reviews and run compliant Google Ads, with fast follow-up on every enquiry.'],
    ['Are financial promotions regulated?', 'Yes. Financial promotions must be clear, fair and not misleading under FCA rules, and Consumer Duty applies to communications with retail clients. We build campaigns to meet these standards.'],
    ['Do you work with accountants?', 'Yes. Our accountant marketing and financial adviser marketing work covers accountants, financial advisers, mortgage and insurance brokers, wealth managers and lenders, with content written for each audience.'],
    ['Can our compliance team approve content?', 'Yes. We build a sign-off step into every campaign and keep an audit trail of approvals, so your compliance team reviews each ad, page and email before it goes live.'],
    ['Do you build client portals?', 'Yes. We build secure portals for documents, onboarding and messaging, so clients can upload information, sign forms and message your team safely, which reduces email attachments and speeds up onboarding.'],
    ['Do you need a contract?', 'No. GTech Digital runs financial marketing on rolling monthly terms. Compliance reviews and trust building take time, so we recommend six months, but you can stop with a month\'s notice.'],
    ['What is FCA compliant marketing?', 'FCA compliant marketing means every financial promotion is clear, fair and not misleading, includes the right risk warnings and meets Consumer Duty. Our mortgage broker marketing and adviser campaigns follow these rules and include a documented approval step.'],
  ],
  related: ['search-engine-optimization', 'google-ads', 'linkedin-marketing', 'content-marketing', 'website-design', 'web-application-development'],
});
