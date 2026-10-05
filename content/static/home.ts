import type { ServiceContent } from '@/content/types';

// Editable copy of the Home page (Admin > Pages > Home). The hero heading uses | for a line break and [[ ]] to colour words red.
export const homeContent: ServiceContent = {
  slug: 'home',
  short: 'Home',
  metaTitle: 'GTech Digital | Digital Marketing Agency UK',
  metaDescription: 'GTech Digital is a UK digital marketing agency growing businesses with SEO, Google Ads, social media, web design and custom software.',
  hero: { h1: 'Digital Marketing|Agency for Scalable|Growth', lead: 'GTech Digital helps UK businesses grow with smart, conversion-focused marketing.', motion: '', points: [] },
  sections: [
    { type: 'text', id: 'who', nav: 'Who we are', heading: 'A [[Digital Marketing Agency]] Built for Growth',
      paras: ['GTech Digital is a full-service digital marketing agency specialising in search marketing, advertising, branding, and high-performing websites and software for growth-focused businesses. We turn strategy into measurable revenue.'],
      bullets: ['Strategy, design and engineering under one roof', 'Every campaign tracked to leads and revenue', 'Plain-English reporting, no jargon'] },
    { type: 'impact', id: 'services', nav: 'Services', heading: 'Digital Marketing, Web and Software Services', text: 'Everything you need to grow online, from one team. Strategy, creative and engineering that work together and are measured on real business results.', stats: [] },
    { type: 'impact', id: 'brands', nav: 'Brands strip', heading: 'Experience Working with Industry [[Leading Brands.]]', text: '', stats: [] },
    { type: 'text', id: 'inquiry', nav: 'Free proposal block (also on About)', heading: 'Request a Free Proposal',
      paras: ['Now you know about us, we would love to get to know you better. Why not drop us a message today and introduce yourself? It could be the beginning of a beautiful relationship.'] },
  ],
  faqs: [],
  related: [],
};
