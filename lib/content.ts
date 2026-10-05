import type { ServiceContent } from '@/content/types';
import { brandLogos } from '@/lib/data';
import { findOne, list } from '@/lib/store';

// Public-site getters for content managed in the admin. Each falls back to the built-in defaults
// when the database is empty or unreachable, so the site always renders.
type Logo = { name: string; logo: string; url?: string };

async function logos(coll: 'partners' | 'clients', fallback: Logo[]): Promise<Logo[]> {
  try {
    const rows = await list(coll, { sort: { order: 1 }, limit: 100 });
    const shown = rows.filter((r) => r.visible !== false && r.logo).map((r) => ({ name: r.name, logo: r.logo, url: r.url }));
    return rows.length ? shown : fallback;
  } catch { return fallback; }
}
const builtInPartners: Logo[] = [
  ['Google Partner', 'Google-Partner.png'], ['Meta Business Partner', 'Meta-1.png'], ['LinkedIn', 'LinkedIn.png'], ['TikTok Marketing Partner', 'TikTok-Partners.png'],
  ['Brevo Partner', 'Brevo.png'], ['Shopify Partner', 'Shopify.png'], ['Klaviyo Partner', 'Klaviyo.png'], ['Google Analytics', 'Google-Analytics.png'],
].map(([name, f]) => ({ name, logo: `/partners/${f}` }));
export const getPartners = () => logos('partners', builtInPartners);
export const getClients = () => logos('clients', brandLogos);

/** Apply admin edits (Admin > Pages) on top of a service or industry page definition. */
export async function withOverrides(kind: 'service' | 'industry', c: ServiceContent): Promise<ServiceContent> {
  let o;
  try { o = await findOne('page_content', { _id: `${kind}~${c.slug}` }); } catch { return c; }
  if (!o) return c;
  return {
    ...c,
    metaTitle: o.metaTitle || c.metaTitle,
    metaDescription: o.metaDescription || c.metaDescription,
    hero: {
      ...c.hero,
      ...(o.hero?.keyword && { keyword: o.hero.keyword }),
      ...(o.hero?.lead && { lead: o.hero.lead }),
      ...(o.hero?.points?.length && { points: o.hero.points }),
    },
    sections: c.sections.map((s) => {
      const e = o.sections?.[s.id];
      return e ? ({ ...s, ...(e.heading && { heading: e.heading }), ...(e.paras?.length && 'paras' in s && { paras: e.paras }), ...(e.bullets?.length && 'bullets' in s && { bullets: e.bullets }), ...(e.text && 'text' in s && { text: e.text }) } as typeof s) : s;
    }),
    faqs: o.faqs?.length ? o.faqs : c.faqs,
  };
}
