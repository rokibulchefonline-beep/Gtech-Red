import { industry } from './build';

// Entities: B2B lead generation, sales pipeline, ICP, buying committee, ABM, LinkedIn Ads, thought
// leadership, HubSpot/Salesforce, lead scoring, MQL/SQL, sales cycle, closed-loop reporting.
export default industry({
  slug: 'b2b-marketing',
  name: 'B2B',
  kw: 'B2B Marketing',
  metaTitle: 'B2B Marketing Agency UK | Lead Generation & ABM | GTech Digital',
  metaDescription: 'UK B2B marketing agency generating qualified leads and sales pipeline with SEO, LinkedIn, Google Ads, account-based marketing and CRM-connected reporting.',
  hero: {
    title: 'B2B Marketing That Builds',
    highlight: 'Pipeline',
    lead: 'GTech Digital provides B2B marketing services for UK companies, combining B2B SEO, LinkedIn Ads, account-based marketing and CRM-connected reporting to generate qualified leads and measurable sales pipeline.',
    points: ['Free pipeline audit', 'CRM-connected tracking', 'Reporting on pipeline, not clicks'],
  },
  what: {
    heading: 'Marketing for Long, Complex Sales',
    para: 'B2B buyers research for weeks, involve several decision-makers and rarely convert on the first visit. Winning them takes useful content, precise targeting and steady nurturing, all tracked through your CRM.',
    bullets: ['Demand: SEO, content and thought leadership', 'Leads: LinkedIn, Google Ads and ABM', 'Pipeline: nurturing, scoring and CRM reporting'],
  },
  impact: { heading: 'Pipeline, Not Vanity Metrics', stats: [['£3.2M', 'Average pipeline created a year'], ['+44%', 'Average win rate improvement'], ['£72', 'Average cost per qualified lead'], ['-21 days', 'Average sales cycle reduction']] },
  media: [
    { nav: 'Pipeline', topic: 'Pipeline growth and qualified leads', heading: 'Marketing Sales Teams Value', para: 'We agree lead definitions with your sales team and measure success on meetings, opportunities and revenue.', bullets: ['Ideal customer profile', 'Lead scoring and routing', 'Sales and marketing alignment', 'Pipeline reporting'], alt: 'B2B sales pipeline created with qualified leads, sales meetings and cost per lead' },
    { nav: 'Channels', topic: 'Demand generation channels and ABM', heading: 'Reach the Whole Buying Committee', para: 'We combine search for in-market buyers with LinkedIn and ABM to reach the accounts you most want to win.', bullets: ['SEO and content for research', 'LinkedIn Ads and Lead Gen Forms', 'Account-based marketing', 'Email nurture sequences'], alt: 'B2B demand channels: SEO, LinkedIn, Google Ads, content, email and ABM' },
    { nav: 'Journey', topic: 'Buyer journey content and nurture', heading: 'From First Touch to Signed Contract', para: 'Buyers need proof at every stage. We map content and campaigns to each step of their decision.', bullets: ['Guides, case studies and webinars', 'Retargeting by stage', 'Demo and consultation pages', 'Sales enablement content'], alt: 'B2B pipeline from target accounts reached to meetings booked with win rate and sales cycle' },
    { nav: 'CRM', topic: 'CRM integration and closed-loop reporting', heading: 'Closed-Loop Reporting You Can Trust', para: 'We connect your website, ads and CRM so you see which channels create revenue, not just leads.', bullets: ['HubSpot and Salesforce integration', 'Offline conversion tracking', 'Attribution reporting', 'Data quality checks'], alt: 'B2B growth engine checklist with CRM tracking, lead scoring, ABM and closed-loop reporting' },
  ],
  cards: [
    ['lucide:search', 'B2B SEO', 'Rank for high-intent searches.'], ['simple-icons:linkedin', 'LinkedIn Ads', 'Reach the right job titles.'],
    ['simple-icons:googleads', 'Google Ads', 'Capture in-market buyers.'], ['lucide:building-2', 'ABM', 'Target named accounts.'],
    ['lucide:file-text', 'Thought Leadership', 'Content that builds trust.'], ['lucide:mail', 'Nurture', 'Email sequences to sales-ready.'],
    ['lucide:database', 'CRM Integration', 'HubSpot and Salesforce.'], ['lucide:chart-column-increasing', 'Reporting', 'Pipeline and revenue.'],
  ],
  reviews: [['David K', 'MD, IT Services', 'We now get around 15 qualified sales meetings a month, all tracked in HubSpot.'], ['Joanne E', 'Sales Director, Services', 'Marketing and sales finally agree on what a good lead looks like.'], ['Tariq A', 'Head of Growth, SaaS', 'Their ABM campaigns opened doors we had been knocking on for years.']],
  faqs: [
    ['What is B2B marketing?', 'B2B marketing promotes products and services to other businesses. It focuses on reaching decision-makers, building trust over longer sales cycles and generating qualified leads for sales teams.'],
    ['Which channels work best for B2B?', 'Usually SEO and content for research-stage buyers, LinkedIn for precise targeting by job title and company, and Google Ads for in-market searches.'],
    ['What is account-based marketing?', 'ABM focuses marketing on a list of named target accounts, with tailored ads and content for the people involved in the buying decision.'],
    ['How do you measure B2B marketing?', 'We track qualified leads, meetings, opportunities and closed revenue through your CRM, not just clicks and form fills.'],
    ['Do you work with our sales team?', 'Yes. We agree lead definitions, follow-up processes and reporting with sales from the start.'],
    ['Do you need a contract?', 'No. We work on rolling monthly terms.'],
  ],
  related: ['linkedin-marketing', 'search-engine-optimization', 'content-marketing', 'google-ads', 'crm-erp-development', 'marketing-advisory'],
});
