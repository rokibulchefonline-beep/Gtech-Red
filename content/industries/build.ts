import type { Section, ServiceContent } from '../types';

// Builds an industry page in the approved long-form layout: intro, numbers band, four Z-pattern
// media sections (growth, channels, journey, trust), services grid, case studies and reviews.
// Section headings are generated from the industry keyword (`kw`) plus the section topic.
type Media = { nav: string; topic: string; heading: string; para: string; bullets: string[]; alt: string };
type Input = {
  slug: string; name: string; kw: string; h1?: string; metaTitle: string; metaDescription: string;
  hero: { title: string; highlight: string; lead: string; points: string[] };
  what: { heading: string; para: string; bullets: string[] };
  impact: { heading: string; stats: [string, string][] };
  media: [Media, Media, Media, Media];
  cards: [string, string, string][];
  reviews: [string, string, string][];
  faqs: [string, string][];
  related: string[];
};

const images = ['growth', 'channels', 'journey', 'trust'];
const title = (s: string) => s.replace(/(^|\s)([a-z])/g, (_, a, b) => a + b.toUpperCase()).replace(/ And /g, ' and ').replace(/ To /g, ' to ').replace(/ Of /g, ' of ').replace(/ From /g, ' from ').replace(/ For /g, ' for ');

export function industry(i: Input): ServiceContent {
  const sections: Section[] = [
    { type: 'logos', id: 'clients' },
    { type: 'text', id: 'overview', nav: 'Overview', heading: `What Makes ${i.kw} Different and Why It Matters`, paras: [i.what.para], bullets: i.what.bullets },
    { type: 'impact', id: 'impact', heading: `${i.kw} Results and Key Statistics`, text: `The numbers behind the work we do for UK ${i.name.toLowerCase()} businesses.`, stats: i.impact.stats.map(([value, label]) => ({ value, label })) },
    ...i.media.map((m, n): Section => ({
      type: 'media', id: images[n], nav: m.nav, heading: `${i.kw}: ${title(m.topic)}`, image: `/pages/industries/${i.slug}/${images[n]}.webp`, alt: m.alt,
      paras: [m.para], bullets: m.bullets, ...(n % 2 ? { flip: true, tone: 'grey' as const } : {}),
    })),
    { type: 'cards', id: 'services', nav: 'What we do', heading: `Our ${i.kw} Services and Solutions`, cards: i.cards.map(([icon, t, text]) => ({ icon, title: t, text })) },
    { type: 'cases', id: 'case-studies', nav: 'Case studies', heading: `${i.kw} Case Studies and Results` },
    { type: 'reviews', id: 'reviews', nav: 'Reviews', heading: `${i.kw} Client Reviews and Testimonials`, reviews: i.reviews.map(([name, role, text]) => ({ name, role, text })) },
  ];
  return {
    slug: i.slug, short: i.kw, metaTitle: i.metaTitle, metaDescription: i.metaDescription,
    hero: { keyword: i.h1 ?? i.kw, ...i.hero, motion: `/services/ind-${i.slug}.webp` },
    sections, faqs: i.faqs.map(([q, a]) => ({ q, a })), related: i.related,
  };
}
