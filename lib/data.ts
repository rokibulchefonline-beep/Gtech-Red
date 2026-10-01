import { slugify } from './util';

// Site structure. Edit here to change menus, pages and copy.
export const site = {
  name: 'GTech Digital',
  tagline: 'Digital marketing, web and software agency',
  email: 'hello@gtechred.com',
  phone: '+44 0000 000000',
};

export type ServiceGroup = {
  slug: string;
  title: string;
  intro: string;
  items: { name: string; slug: string; blurb: string }[];
};

const groups: Record<string, { intro: string; items: Record<string, string> }> = {
  'Digital Marketing': {
    intro: 'Get found, get clicks, get customers. Data-led search and paid campaigns.',
    items: {
      'Search Engine Optimization': 'Rank higher on Google with technical, on-page and content SEO.',
      'Google Ads': 'Paid search and display campaigns tuned for lead cost and ROAS.',
      'Reputation Management': 'Monitor, protect and improve your brand reviews and search results.',
      'Content Marketing': 'Articles, guides and assets that pull qualified traffic.',
      'SEO Backlinks': 'Quality link building that lifts domain authority.',
      'Digital Advertising': 'Multi-channel ad campaigns with clear tracking.',
      'Paid Media': 'Media buying and budget optimisation across platforms.',
    },
  },
  'Social Media Marketing': {
    intro: 'Grow audience and sales on the platforms your customers use.',
    items: {
      'Facebook Marketing': 'Page growth, ads and community management.',
      'Instagram Marketing': 'Reels, stories and ads that convert followers.',
      'LinkedIn Marketing': 'B2B lead generation and employer branding.',
      'TikTok Marketing': 'Short-video content and paid campaigns.',
      'Pinterest Marketing': 'Visual discovery campaigns that drive traffic.',
    },
  },
  'Web Design & Development': {
    intro: 'Fast, secure, conversion-focused websites and stores.',
    items: {
      'WordPress Development': 'Custom themes and plugins on WordPress.',
      'PHP Development': 'Custom PHP applications and APIs.',
      'CMS Development': 'Content platforms your team can edit without code.',
      'Laravel Development': 'Scalable Laravel web apps and back offices.',
      'Website Maintenance': 'Updates, backups, security and uptime monitoring.',
      'Ecommerce Development': 'Online stores with payments, catalog and checkout.',
      'Website Design': 'UX and UI design built around your goals.',
    },
  },
  'Custom Software Development': {
    intro: 'Software built around your process, not the other way round.',
    items: {
      'Web Application Development': 'Portals, dashboards and internal tools.',
      'Mobile App Development': 'iOS and Android apps.',
      'API & System Integration': 'Connect your tools, data and third-party services.',
      'CRM & ERP Development': 'Custom business management systems.',
      'SaaS Product Development': 'From idea to launched subscription product.',
      'MVP Development': 'Ship a first version fast and validate demand.',
    },
  },
  'Branding & Strategy': {
    intro: 'Clear brand, clear plan, better conversion.',
    items: {
      Branding: 'Identity, positioning and brand guidelines.',
      'Marketing Advisory': 'Senior guidance on channels, budget and growth plan.',
      'Conversion Rate Optimization': 'Test and improve pages to turn more visits into leads.',
    },
  },
};

export const services: ServiceGroup[] = Object.entries(groups).map(([title, g]) => ({
  slug: slugify(title),
  title,
  intro: g.intro,
  items: Object.entries(g.items).map(([name, blurb]) => ({ name, slug: slugify(name), blurb })),
}));

export const industries = [
  'E-commerce', 'Education', 'B2B Marketing', 'Automotive', 'Healthcare',
  'Hospitality & Hotels', 'Travel', 'Real Estate', 'Finance', 'Technology & SaaS',
].map((name) => ({ name, slug: slugify(name) }));

export const budgets = [
  'Under £500', '£500 - £1,000', '£1,000 - £2,500', '£2,500 - £5,000',
  '£5,000 - £10,000', '£10,000+', 'Not sure yet',
];

export const findGroup = (slug: string) => services.find((g) => g.slug === slug);

export function findItem(slug: string) {
  for (const group of services) {
    const item = group.items.find((i) => i.slug === slug);
    if (item) return { group, item };
  }
  return undefined;
}


// Certified partner logos. Hotlinked for now: download the SVGs into /public/partners/ and change `logo`
// to '/partners/<file>.svg' so the site does not depend on another domain.
const p = 'https://www.unitedseo.ae/front_asset/images/slider-logos';
export const partners = [
  { name: 'Google Partner', logo: `${p}/work-icon-1.svg` },
  { name: 'Meta Business Partner', logo: `${p}/work-icon-2.svg` },
  { name: 'HubSpot', logo: `${p}/work-icon-3.svg` },
  { name: 'Semrush', logo: `${p}/work-icon-4.svg` },
];

// Demo client logos (hotlinked placeholders). Replace with your own in /public/clients/.
const g = 'https://growmemarketing.ca/wp-content/uploads';
export const brandLogos = [
  { name: 'Barbecues Galore', logo: `${g}/2023/11/barbecues-galore-logo.webp` },
  { name: 'Edwards Injury Law', logo: `${g}/2025/10/logo-edwards-injury-law-fit.svg` },
  { name: 'Stephanie Edwards', logo: `${g}/2019/08/edwards-injury-law_logo-02.png` },
  { name: 'Supreme Security', logo: `${g}/2023/11/image_home-client-logo-supreme-security.webp` },
  { name: 'Ultimate Homes & Renovations', logo: `${g}/2025/06/logo-ultimate-renovations-colored.png` },
  { name: 'Tiptop Plumbing & Heating', logo: `${g}/2025/03/TiptopPlumbingHeatingLogo.png` },
  { name: 'Vive Rejuvenation', logo: `${g}/2024/12/vive-med-spa.svg` },
  { name: 'Digital Agency Network', logo: `${g}/2025/12/digital-agency-network-logo.svg` },
  { name: 'Silverhorn', logo: `${g}/2024/02/Silverhorn-Logo.png` },
];

// Six core services shown as stacked cards on the home page. `slug` links to /services/{slug}.
// `image` is optional: set e.g. '/services/seo.jpg' (file in /public/services/) to show a photo
// instead of the icon panel.
export const coreServices: {
  slug: string; title: string; line: string; points: string[]; image?: string;
}[] = [
  {
    slug: 'search-engine-optimization', title: 'Search Engine Optimization', image: '/services/seo.webp',
    line: 'Rank higher, earn qualified traffic and turn searches into customers.',
    points: ['Technical SEO audits and fixes', 'Keyword and content strategy', 'Local and ecommerce SEO', 'Link building and monthly reporting'],
  },
  {
    slug: 'google-ads', title: 'Google Ads & Paid Media', image: '/services/paid.webp',
    line: 'Paid campaigns built around cost per lead and return on ad spend.',
    points: ['Search, Shopping and Performance Max', 'Meta, TikTok and LinkedIn ads', 'Landing pages and conversion tracking', 'Weekly optimisation and clear reporting'],
  },
  {
    slug: 'social-media-marketing', title: 'Social Media Marketing', image: '/services/social.webp',
    line: 'Grow your audience and sales on the platforms your customers use.',
    points: ['Content calendars and creative', 'Community management', 'Influencer and UGC campaigns', 'Paid social and analytics'],
  },
  {
    slug: 'web-design-development', title: 'Web Design & Development', image: '/services/web.webp',
    line: 'Fast, secure, conversion-focused websites and online stores.',
    points: ['UX and UI design', 'WordPress, Laravel and Next.js builds', 'Ecommerce and payments', 'Speed, security and maintenance'],
  },
  {
    slug: 'custom-software-development', title: 'Custom Software Development', image: '/services/software.webp',
    line: 'Software built around your process, not the other way round.',
    points: ['Web apps and internal tools', 'CRM, ERP and integrations', 'Mobile apps', 'SaaS products and MVPs'],
  },
  {
    slug: 'branding', title: 'Branding & Strategy', image: '/services/branding.webp',
    line: 'A clear brand and a clear plan to grow it.',
    points: ['Identity and brand guidelines', 'Positioning and messaging', 'Marketing advisory', 'Conversion rate optimisation'],
  },
];

// Demo case studies, shown until real documents exist in the MongoDB `case_studies` collection.
// Covers are drawn in code (scripts/build-case-images.mjs, `npm run case-images`).
// Real documents can add `image` (cover photo URL) and `logo` (white logo URL) for the card.
export const demoCaseStudies = [
  { slug: 'chefonline', services: ['search-engine-optimization', 'google-ads'], image: '/case/chefonline.webp', title: 'ChefOnline', excerpt: 'Online ordering growth for a restaurant platform.' },
  { slug: 'salik-and-co', services: ['search-engine-optimization'], image: '/case/salik-and-co.webp', title: 'Salik & Co', excerpt: 'Lead generation and local SEO for a professional services firm.' },
  { slug: 'arta', services: ['social-media-marketing'], image: '/case/arta.webp', title: 'Arta', excerpt: 'Brand and social growth for a hospitality awards brand.' },
  { slug: 'table-booking', services: ['web-design-development', 'google-ads'], image: '/case/table-booking.webp', title: 'Table Booking', excerpt: 'Web platform and paid media for restaurant bookings.' },
  { slug: 'demo-brand-five', services: ['search-engine-optimization', 'content-marketing'], image: '/case/demo-brand-five.webp', title: 'Demo Brand', excerpt: 'Placeholder case study. Replace with a real client.' },
  { slug: 'demo-brand-six', services: ['branding'], image: '/case/demo-brand-six.webp', title: 'Sample Client', excerpt: 'Placeholder case study. Replace with a real client.' },
].map((c) => ({
  ...c,
  body: `${c.excerpt}\n\nThis is a placeholder case study. Add real documents to the MongoDB "case_studies" collection ` +
    `(fields: title, slug, excerpt, body, image, logo, created_at) and they replace these demos automatically.\n\n` +
    `Challenge\nDescribe the client's problem.\n\nSolution\nDescribe what you built or ran.\n\nResults\nAdd real numbers.`,
}));

// Company numbers under the hero. Placeholder figures: replace with your real ones.
export const stats = [
  { value: 10, suffix: '+', label: 'Years of experience' },
  { value: 500, suffix: '+', label: 'Projects delivered' },
  { value: 150, suffix: '+', label: 'Happy clients' },
  { value: 12, suffix: '', label: 'Industries served' },
  { value: 4.9, suffix: '/5', label: 'Average client rating', decimals: 1 },
];

// Demo testimonials. Replace with real client quotes.
export const testimonials = [
  { title: 'Reliable and Strategic SEO Partner', name: 'Derek L',
    text: "If you're looking for a reliable SEO agency that delivers tangible results, GTech Digital is the way to go. Their comprehensive SEO audits and tailored strategies have greatly enhanced our site's performance. We appreciate their proactive approach and continuous efforts to optimise our digital assets." },
  { title: 'Ads That Actually Pay Back', name: 'Sarah M',
    text: 'Our Google Ads spend used to feel like a gamble. GTech Digital rebuilt the account, fixed our tracking and cut our cost per lead within the first quarter. The monthly reports are clear and honest.' },
  { title: 'A Website That Converts', name: 'Imran K',
    text: 'The new site is faster, looks far better and brings in enquiries every week. The team handled design, development and SEO together, which saved us a lot of back and forth.' },
  { title: 'Software Built Around Our Process', name: 'Laura P',
    text: 'GTech Digital built a custom booking and CRM system that replaced three separate tools. It was delivered on time and the support since launch has been excellent.' },
  { title: 'A True Growth Partner', name: 'James T',
    text: 'They act like part of our own team. Strategy, creative and reporting all come from one place, and we can see exactly how marketing turns into revenue.' },
];
