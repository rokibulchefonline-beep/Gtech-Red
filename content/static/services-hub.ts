import type { ServiceContent } from '@/content/types';
import { services } from '@/lib/data';

// Editable copy of the Services hub (Admin > Pages > Services hub). Images, icons and card grids stay in code.
export const groupInfo: Record<string, { image: string; title: string; h2: string; line: string; points: string[]; cards: string[] }> = {
  'digital-marketing': { image: '/services/dm.webp', title: 'Digital Marketing', h2: 'Digital Marketing Services: SEO, Google Ads and Content', line: 'Be found on Google and in AI answers, and turn searches into customers.', points: ['SEO, local SEO and AI search', 'Google Ads and paid media', 'Content, links and reviews'],
    cards: ['search-engine-optimization', 'local-seo', 'ecommerce-seo', 'google-ads', 'content-marketing', 'reputation-management'] },
  'social-media-marketing': { image: '/services/social.webp', title: 'Social Media Marketing', h2: 'Social Media Marketing Services for Facebook, Instagram, LinkedIn and TikTok', line: 'Content, ads and creators that grow your audience and your sales.', points: ['Content and community management', 'Paid social campaigns', 'Creators and social commerce'],
    cards: ['facebook-marketing', 'instagram-marketing', 'linkedin-marketing', 'tiktok-marketing', 'pinterest-marketing', 'paid-media'] },
  'web-design-development': { image: '/services/web.webp', title: 'Web Design & Development', h2: 'Web Design and Development Services: WordPress, Ecommerce and Laravel', line: 'Fast, secure websites and stores designed to convert.', points: ['UX and UI design', 'WordPress, Laravel and ecommerce', 'Speed, SEO and maintenance'],
    cards: ['website-design', 'ecommerce-development', 'wordpress-development', 'laravel-development', 'cms-development', 'website-maintenance'] },
  'custom-software-development': { image: '/services/software.webp', title: 'Custom Software', h2: 'Custom Software Development Services: Apps, CRM and SaaS', line: 'Apps and systems built around how your business works.', points: ['Web and mobile apps', 'CRM, ERP and integrations', 'SaaS products and MVPs'],
    cards: ['web-application-development', 'mobile-app-development', 'api-system-integration', 'crm-erp-development', 'saas-product-development', 'mvp-development'] },
  'branding-strategy': { image: '/services/branding.webp', title: 'Branding & Strategy', h2: 'Branding and Strategy Services: Identity, Advisory and CRO', line: 'A clear brand and a clear plan to grow it.', points: ['Brand identity and guidelines', 'Marketing advisory', 'Conversion rate optimisation'],
    cards: ['branding', 'marketing-advisory', 'conversion-rate-optimization', 'website-design', 'content-marketing', 'reputation-management'] },
};

const total = services.reduce((n, g) => n + g.items.length, 0);

export const servicesHubContent: ServiceContent = {
  slug: 'services-hub',
  short: 'Services hub',
  metaTitle: 'GTech Digital Services | Marketing, Web & Software Solutions',
  metaDescription: 'Explore every GTech Digital service: SEO, Google Ads, social media, web design, custom software and branding, delivered by one UK team.',
  hero: {
    h1: 'Marketing, Web and Software Services of [[GTech Digital]]',
    lead: `GTech Digital is a UK digital agency offering ${total}+ services across digital marketing, social media marketing, web design and development, custom software development and branding, all delivered by one joined-up team.`,
    motion: '',
    points: [`${total}+ specialist services`, `${services.length} disciplines`, 'One joined-up team'],
  },
  sections: [
    ...Object.entries(groupInfo).map(([slug, g]) => ({ type: 'text' as const, id: `group-${slug}`, nav: g.title, heading: g.h2, paras: [g.line], bullets: g.points })),
    { type: 'cards', id: 'why', nav: 'Why choose us', heading: 'Why Businesses Choose Our Digital Agency', cards: [
  { icon: 'lucide:users', title: 'One Joined-Up Team', text: 'Marketing, web and software specialists working from one plan.' },
  { icon: 'lucide:user-check', title: 'Senior-Led', text: 'A dedicated lead who knows your business.' },
  { icon: 'lucide:eye', title: 'Transparent', text: 'Fixed prices, plain-English reports, no lock-in.' },
  { icon: 'lucide:trending-up', title: 'Results Tracked', text: 'Every channel measured against leads and revenue.' },
    ] },
    { type: 'steps', id: 'process', nav: 'Process', heading: 'How Our Services Work, Step by Step', steps: [
          { title: 'Discover', text: 'Your goals, market and customers.' },
          { title: 'Audit', text: 'A free review of what works today.' },
          { title: 'Recommend', text: 'The right services and budget.' },
          { title: 'Deliver', text: 'Specialists get to work.' },
          { title: 'Report', text: 'Clear monthly results.' },
          { title: 'Grow', text: 'Scale what works.' },
    ] },
  ],
  faqs: [
  { q: 'What services does GTech Digital offer?', a: 'We offer digital marketing (SEO, local and ecommerce SEO, Google Ads, paid media, content and reputation), social media marketing, web design and development, custom software and apps, and branding and strategy.' },
  { q: 'Can I use more than one service?', a: 'Yes. Most clients combine services, for example a new website with SEO and Google Ads, all managed by one team and one plan.' },
  { q: 'How do I know which service I need?', a: 'Book a free audit. We review your website, marketing and goals, then recommend the services that will make the biggest difference.' },
  { q: 'Do you work with businesses across the UK?', a: 'Yes. We work with businesses throughout the UK, meeting in person or online.' },
  { q: 'Are your services on contracts?', a: 'Ongoing services run on rolling monthly terms. Projects such as websites and software have a fixed, agreed price.' },
  ],
  related: [],
};
