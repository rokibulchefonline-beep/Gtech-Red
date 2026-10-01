// Shape of a long-form service page. One file per service in content/services/.
export type Card = { icon: string; title: string; text: string };
export type Section =
  | { type: 'text'; id: string; nav?: string; eyebrow?: string; heading: string; paras: string[]; bullets?: string[] }
  | { type: 'media'; id: string; nav?: string; eyebrow?: string; heading: string; paras: string[]; bullets?: string[]; image: string; alt: string; flip?: boolean }
  | { type: 'cards'; id: string; nav?: string; eyebrow?: string; heading: string; intro?: string; cards: Card[] }
  | { type: 'steps'; id: string; nav?: string; eyebrow?: string; heading: string; intro?: string; steps: { title: string; text: string }[] }
  | { type: 'table'; id: string; nav?: string; eyebrow?: string; heading: string; intro?: string; columns: string[]; rows: string[][]; note?: string }
  | { type: 'metrics'; id: string; nav?: string; eyebrow?: string; heading: string; intro?: string; metrics: { label: string; value: string; text: string }[] };

export type ServiceContent = {
  slug: string;
  metaTitle: string;
  metaDescription: string;
  hero: { eyebrow: string; title: string; highlight: string; lead: string; motion: string; points: string[] };
  sections: Section[];
  faqs: { q: string; a: string }[];
  related: string[];
  industries: string[];
};
