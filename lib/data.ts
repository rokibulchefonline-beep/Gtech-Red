import { slugify } from './util';

// Site structure. Edit here to change menus, pages and copy.
export const site = {
  name: 'GTech Digital',
  tagline: 'Digital marketing, web and software agency',
  email: 'hello@gtechred.com',
  phone: '+44 0000 000000',
  // Company social profiles (placeholders: replace with the real URLs).
  socials: [
    { name: 'LinkedIn', icon: 'simple-icons:linkedin', url: 'https://www.linkedin.com/company/gtechdigital' },
    { name: 'Facebook', icon: 'simple-icons:facebook', url: 'https://www.facebook.com/gtechdigital' },
    { name: 'Instagram', icon: 'simple-icons:instagram', url: 'https://www.instagram.com/gtechdigital' },
    { name: 'X', icon: 'simple-icons:x', url: 'https://x.com/gtechdigital' },
    { name: 'YouTube', icon: 'simple-icons:youtube', url: 'https://www.youtube.com/@gtechdigital' },
  ],
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
      'Local SEO': 'Google Business Profile, map pack and local rankings.',
      'Ecommerce SEO': 'Category and product page SEO that grows online sales.',
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

// Demo results for each placeholder case study (numbers are illustrative: replace with real client data).
const demoDetail: Record<string, Record<string, unknown>> = {
  chefonline: { client: 'ChefOnline', industry: 'Hospitality and Ecommerce', duration: '18 months',
    metrics: [{ value: '+212%', label: 'organic traffic' }, { value: '£1.4M', label: 'online revenue' }, { value: '4.8x', label: 'ROAS' }, { value: '3.1x', label: 'more orders' }],
    challenge: 'ChefOnline relied on third-party marketplaces and paid heavily in commission. Direct ordering was small, search visibility was weak and the ordering platform was slow on mobile.',
    solution: 'We rebuilt the ordering experience for speed, added restaurant and cuisine landing pages for local SEO, ran Google Ads and Meta campaigns tuned to profit, and connected ordering data to reporting so every channel was judged on revenue.',
    results: ['Organic traffic up 212% in 12 months', 'Online revenue grew to £1.4M a year', 'Blended ROAS improved to 4.8x', 'Direct orders tripled, cutting marketplace commission'],
    quote: { text: 'GTech understood our platform and our margins. Direct orders are now our biggest channel.', name: 'Founder', role: 'ChefOnline' } },
  'salik-and-co': { client: 'Salik & Co', industry: 'Professional Services', duration: '12 months',
    metrics: [{ value: '+340%', label: 'qualified leads' }, { value: '-46%', label: 'cost per lead' }, { value: '#1', label: 'local pack rank' }, { value: '£780k', label: 'pipeline value' }],
    challenge: 'A respected firm with almost no online presence. Enquiries came from referrals only and competitors dominated local search.',
    solution: 'We launched a fast WordPress website, built service and location pages, fixed the Google Business Profile, earned reviews and links, and ran LinkedIn campaigns to reach business owners.',
    results: ['Qualified leads up 340%', 'Cost per lead down 46%', 'Top local pack position for 14 priority searches', 'A CRM that tracks every enquiry to a signed client'],
    quote: { text: 'We went from referral-only to a steady stream of enquiries we can plan around.', name: 'Managing Partner', role: 'Salik & Co' } },
  arta: { client: 'Arta', industry: 'Hospitality and Awards', duration: '9 months',
    metrics: [{ value: '2.4M', label: 'social reach' }, { value: '+185%', label: 'engagement' }, { value: '+62k', label: 'new followers' }, { value: '38%', label: 'ticket sales from social' }],
    challenge: 'Arta needed to build a recognisable brand fast and fill award events, with a small team and no consistent social presence.',
    solution: 'We created the brand identity, a content system for Instagram, TikTok and Facebook, creator partnerships and paid social that retargeted engaged viewers into ticket buyers.',
    results: ['2.4M people reached across social platforms', 'Engagement up 185%', '62,000 new followers in nine months', '38% of ticket sales came from social'],
    quote: { text: 'The brand and the content finally feel like one thing, and sales show it.', name: 'Events Director', role: 'Arta' } },
  'table-booking': { client: 'Table Booking', industry: 'Hospitality Technology', duration: '14 months',
    metrics: [{ value: '+128%', label: 'bookings' }, { value: '£1.9M', label: 'booking value' }, { value: '5.2x', label: 'ROAS' }, { value: '-38%', label: 'cost per booking' }],
    challenge: 'A booking platform with strong product but costly acquisition and a slow, hard to update website.',
    solution: 'We rebuilt the platform front end in Laravel, set up conversion tracking end to end and ran Google Ads and Facebook campaigns focused on restaurants ready to switch.',
    results: ['Bookings up 128%', '£1.9M in booking value tracked to ads', 'ROAS of 5.2x on search campaigns', 'Cost per booking down 38%'],
    quote: { text: 'For the first time we know exactly which campaign brings each booking.', name: 'Head of Growth', role: 'Table Booking' } },
  'demo-brand-five': { client: 'Demo Brand', industry: 'Placeholder', duration: '6 months',
    metrics: [{ value: '+150%', label: 'organic traffic' }, { value: '+90%', label: 'leads' }, { value: '£320k', label: 'revenue influenced' }],
    challenge: 'Placeholder challenge. Replace with the real client problem.', solution: 'Placeholder solution. Describe what you built or ran.',
    results: ['Organic traffic up 150%', 'Leads up 90%', '£320k revenue influenced'], quote: { text: 'Placeholder testimonial.', name: 'Client', role: 'Demo Brand' } },
  'demo-brand-six': { client: 'Sample Client', industry: 'Placeholder', duration: '6 months',
    metrics: [{ value: '+75%', label: 'sales' }, { value: '3.6x', label: 'ROAS' }, { value: '-30%', label: 'cost per sale' }],
    challenge: 'Placeholder challenge. Replace with the real client problem.', solution: 'Placeholder solution. Describe what you built or ran.',
    results: ['Sales up 75%', 'ROAS of 3.6x', 'Cost per sale down 30%'], quote: { text: 'Placeholder testimonial.', name: 'Client', role: 'Sample Client' } },
};
export const demoCaseStudies = [
  { slug: 'chefonline', services: ['search-engine-optimization', 'google-ads', 'paid-media', 'reputation-management', 'social-media-marketing', 'facebook-marketing', 'instagram-marketing', 'local-seo', 'ecommerce-development', 'ecommerce-seo', 'website-design', 'custom-software-development', 'web-application-development', 'mobile-app-development', 'api-system-integration', 'saas-product-development', 'conversion-rate-optimization', 'marketing-advisory', 'digital-marketing', 'hospitality-hotels', 'e-commerce', 'technology-saas'], image: '/case/chefonline.webp', title: 'ChefOnline', excerpt: 'Online ordering growth for a restaurant platform.' },
  { slug: 'salik-and-co', services: ['search-engine-optimization', 'seo-backlinks', 'reputation-management', 'linkedin-marketing', 'local-seo', 'web-design-development', 'wordpress-development', 'website-design', 'website-maintenance', 'crm-erp-development', 'web-application-development', 'branding-strategy', 'marketing-advisory', 'conversion-rate-optimization', 'digital-marketing', 'finance', 'b2b-marketing', 'real-estate'], image: '/case/salik-and-co.webp', title: 'Salik & Co', excerpt: 'Lead generation and local SEO for a professional services firm.' },
  { slug: 'arta', services: ['social-media-marketing', 'reputation-management', 'content-marketing', 'digital-advertising', 'facebook-marketing', 'instagram-marketing', 'tiktok-marketing', 'web-design-development', 'website-design', 'cms-development', 'wordpress-development', 'mobile-app-development', 'mvp-development', 'branding-strategy', 'branding', 'hospitality-hotels', 'travel', 'education'], image: '/case/arta.webp', title: 'Arta', excerpt: 'Brand and social growth for a hospitality awards brand.' },
  { slug: 'table-booking', services: ['web-design-development', 'google-ads', 'digital-advertising', 'paid-media', 'facebook-marketing', 'pinterest-marketing', 'laravel-development', 'php-development', 'website-maintenance', 'ecommerce-development', 'cms-development', 'custom-software-development', 'saas-product-development', 'api-system-integration', 'mobile-app-development', 'web-application-development', 'conversion-rate-optimization', 'branding-strategy', 'digital-marketing', 'hospitality-hotels', 'technology-saas', 'travel'], image: '/case/table-booking.webp', title: 'Table Booking', excerpt: 'Web platform and paid media for restaurant bookings.' },
  { slug: 'demo-brand-five', services: ['search-engine-optimization', 'content-marketing', 'seo-backlinks', 'linkedin-marketing', 'pinterest-marketing', 'ecommerce-seo', 'local-seo', 'laravel-development', 'php-development', 'wordpress-development', 'crm-erp-development', 'api-system-integration', 'mvp-development', 'custom-software-development', 'marketing-advisory', 'branding', 'digital-marketing', 'healthcare', 'automotive', 'education', 'b2b-marketing'], image: '/case/demo-brand-five.webp', title: 'Demo Brand', excerpt: 'Placeholder case study. Replace with a real client.' },
  { slug: 'demo-brand-six', services: ['branding', 'digital-advertising', 'paid-media', 'tiktok-marketing', 'pinterest-marketing', 'social-media-marketing', 'ecommerce-development', 'ecommerce-seo', 'website-design', 'cms-development', 'website-maintenance', 'laravel-development', 'php-development', 'saas-product-development', 'mvp-development', 'crm-erp-development', 'branding-strategy', 'conversion-rate-optimization', 'marketing-advisory', 'e-commerce', 'real-estate', 'finance', 'healthcare', 'automotive'], image: '/case/demo-brand-six.webp', title: 'Sample Client', excerpt: 'Placeholder case study. Replace with a real client.' },
].map((c) => ({
  ...c,
  ...demoDetail[c.slug],
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
