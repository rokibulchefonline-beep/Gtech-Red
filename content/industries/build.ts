import type { Section, ServiceContent } from '../types';

// Builds an industry page in the approved long-form layout: intro, numbers band, four Z-pattern
// media sections (growth, channels, journey, trust), services grid, case studies and reviews.
type Media = { nav: string; eyebrow: string; heading: string; para: string; bullets: string[]; alt: string };
type Input = {
  slug: string; name: string; metaTitle: string; metaDescription: string;
  hero: { eyebrow: string; title: string; highlight: string; lead: string; points: string[] };
  what: { heading: string; para: string; bullets: string[] };
  impact: { heading: string; stats: [string, string][] };
  media: [Media, Media, Media, Media];
  cards: [string, string, string][];
  reviews: [string, string, string][];
  faqs: [string, string][];
  related: string[];
};

const images = ['growth', 'channels', 'journey', 'trust'];

export function industry(i: Input): ServiceContent {
  const sections: Section[] = [
    { type: 'logos', id: 'clients' },
    { type: 'text', id: 'overview', nav: 'Overview', eyebrow: `${i.name} marketing`, heading: i.what.heading, paras: [i.what.para], bullets: i.what.bullets },
    { type: 'impact', id: 'impact', eyebrow: 'Results in numbers', heading: i.impact.heading, text: `The numbers behind the work we do for UK ${i.name.toLowerCase()} businesses.`, stats: i.impact.stats.map(([value, label]) => ({ value, label })) },
    ...i.media.map((m, n): Section => ({
      type: 'media', id: images[n], nav: m.nav, eyebrow: m.eyebrow, heading: m.heading, image: `/pages/industries/${i.slug}/${images[n]}.webp`, alt: m.alt,
      paras: [m.para], bullets: m.bullets, ...(n % 2 ? { flip: true, tone: 'grey' as const } : {}),
    })),
    { type: 'cards', id: 'services', nav: 'What we do', eyebrow: 'How we help', heading: `Everything ${i.name} Businesses Need to Grow`, cards: i.cards.map(([icon, title, text]) => ({ icon, title, text })) },
    { type: 'cases', id: 'case-studies', nav: 'Case studies', eyebrow: 'Case studies', heading: `${i.name} Results We Have Delivered` },
    { type: 'reviews', id: 'reviews', nav: 'Reviews', eyebrow: 'Client reviews', heading: `What Our ${i.name} Clients Say`, reviews: i.reviews.map(([name, role, text]) => ({ name, role, text })) },
  ];
  return {
    slug: i.slug, metaTitle: i.metaTitle, metaDescription: i.metaDescription,
    hero: { ...i.hero, motion: `/services/ind-${i.slug}.webp` },
    sections, faqs: i.faqs.map(([q, a]) => ({ q, a })), related: i.related,
  };
}
