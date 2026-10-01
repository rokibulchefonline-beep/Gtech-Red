// Shape of a long-form service page. One file per service in content/services/.
export type Card = { icon: string; title: string; text: string };
export type Section =
  | { type: 'text'; id: string; nav?: string; eyebrow?: string; heading: string; paras: string[]; bullets?: string[] }
  | { type: 'media'; id: string; nav?: string; eyebrow?: string; heading: string; paras: string[]; bullets?: string[]; image: string; alt: string; flip?: boolean; tone?: 'white' | 'grey' }
  | { type: 'cards'; id: string; nav?: string; eyebrow?: string; heading: string; intro?: string; cards: Card[] }
  | { type: 'steps'; id: string; nav?: string; eyebrow?: string; heading: string; intro?: string; steps: { title: string; text: string }[] }
  | { type: 'table'; id: string; nav?: string; eyebrow?: string; heading: string; intro?: string; columns: string[]; rows: string[][]; note?: string }
  | { type: 'impact'; id: string; nav?: string; eyebrow?: string; heading: string; text: string; stats: { value: string; label: string }[] }
  | { type: 'logos'; id: string }
  | { type: 'cases'; id: string; nav?: string; eyebrow?: string; heading: string; intro?: string }
  | { type: 'reviews'; id: string; nav?: string; eyebrow?: string; heading: string; intro?: string; reviews: { name: string; role: string; text: string }[] }
  | { type: 'industries'; id: string; nav?: string; eyebrow?: string; heading: string; intro?: string; items: { slug: string; text: string }[] }
  | { type: 'features'; id: string; nav?: string; eyebrow?: string; heading: string; intro?: string; paras: string[]; cards: Card[] }
  | { type: 'metrics'; id: string; nav?: string; eyebrow?: string; heading: string; intro?: string; metrics: { label: string; value: string; text: string }[] };

export type ServiceContent = {
  slug: string;
  short?: string; // short name used in headings, e.g. 'SEO'
  metaTitle: string;
  metaDescription: string;
  hero: { eyebrow: string; title: string; highlight: string; lead: string; motion: string; points: string[] };
  sections: Section[];
  faqs: { q: string; a: string }[];
  related: string[];
};
