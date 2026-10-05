import { industryContent } from '@/content/industries';
import { seoMap } from '@/content/seo-map';
import { serviceContent } from '@/content/services';
import { findGroup, findItem, industries, services } from '@/lib/data';

// The internal link graph, built from the same data the pages render from. Used for the contextual
// "Explore related topics" blocks, the audit, and the semantic network view in Admin > SEO audit.

export type NodeKind = 'home' | 'hub' | 'category' | 'service' | 'industry' | 'page';
export type GNode = { id: string; label: string; kind: NodeKind; cluster: string };
export type EdgeType = 'breadcrumb' | 'category-list' | 'related' | 'semantic' | 'industries' | 'sector-services' | 'sector-peer' | 'hub' | 'home';
export type GEdge = { from: string; to: string; type: EdgeType; anchor: string };

export const pathOf = (target: string) => (target.startsWith('/') ? target : target.startsWith('i:') ? `/industries/${target.slice(2)}` : `/services/${target}`);

export function buildGraph(): { nodes: GNode[]; edges: GEdge[] } {
  const nodes: GNode[] = [
    { id: '/', label: 'Home', kind: 'home', cluster: 'site' },
    { id: '/services', label: 'Services', kind: 'hub', cluster: 'site' }, { id: '/industries', label: 'Industries', kind: 'hub', cluster: 'site' },
    { id: '/case-studies', label: 'Case studies', kind: 'page', cluster: 'site' }, { id: '/blogs', label: 'Blog', kind: 'page', cluster: 'site' },
    { id: '/about', label: 'About', kind: 'page', cluster: 'site' }, { id: '/contact', label: 'Contact', kind: 'page', cluster: 'site' },
  ];
  const edges: GEdge[] = [];
  const add = (from: string, to: string, type: EdgeType, anchor: string) => { if (from !== to) edges.push({ from, to, type, anchor }); };

  for (const [to, label] of [['/services', 'Services'], ['/industries', 'Industries'], ['/case-studies', 'Case studies'], ['/blogs', 'Blog'], ['/about', 'About us'], ['/contact', 'Contact']]) add('/', to, 'home', label);

  for (const g of services) {
    nodes.push({ id: `/services/${g.slug}`, label: g.title, kind: 'category', cluster: g.slug });
    add('/', `/services/${g.slug}`, 'home', g.title); add('/services', `/services/${g.slug}`, 'hub', g.title);
    for (const it of g.items) {
      nodes.push({ id: `/services/${it.slug}`, label: it.name, kind: 'service', cluster: g.slug });
      add(`/services/${g.slug}`, `/services/${it.slug}`, 'category-list', it.name);
      add(`/services/${it.slug}`, `/services/${g.slug}`, 'breadcrumb', g.title);
      add('/services', `/services/${it.slug}`, 'hub', it.name);
    }
  }
  for (const i of industries) {
    nodes.push({ id: `/industries/${i.slug}`, label: i.name, kind: 'industry', cluster: 'industries' });
    add('/industries', `/industries/${i.slug}`, 'hub', i.name); add(`/industries/${i.slug}`, '/industries', 'breadcrumb', 'Industries');
    for (const o of industries) add(`/industries/${i.slug}`, `/industries/${o.slug}`, 'sector-peer', o.name);
  }
  for (const [slug, c] of Object.entries(serviceContent)) {
    const from = `/services/${slug}`;
    for (const r of c.related) { const f = findItem(r); if (f) add(from, `/services/${r}`, 'related', f.item.name); }
    for (const s of c.sections) if (s.type === 'industries') for (const it of s.items) { const ind = industries.find((x) => x.slug === it.slug); if (ind) add(from, `/industries/${ind.slug}`, 'industries', ind.name); }
  }
  for (const [slug, c] of Object.entries(industryContent)) for (const r of c.related) { const f = findItem(r); if (f) add(`/industries/${slug}`, `/services/${r}`, 'sector-services', f.item.name); }
  for (const [slug, e] of Object.entries(seoMap)) {
    const from = industries.some((i) => i.slug === slug) ? `/industries/${slug}` : `/services/${slug}`;
    for (const [t, anchor] of e.to) add(from, pathOf(t), 'semantic', anchor);
  }
  const ids = new Set(nodes.map((n) => n.id));
  return { nodes, edges: edges.filter((e) => ids.has(e.from) && ids.has(e.to)) };
}

export type PageLinks = { inbound: GEdge[]; outbound: GEdge[]; contextualIn: number; contextualOut: number };
/** Contextual = editorial links in the page body (everything except breadcrumbs, hub listings and the home page). */
const CONTEXTUAL: EdgeType[] = ['related', 'semantic', 'industries', 'sector-services', 'category-list'];

export function linkStats(g = buildGraph()): Record<string, PageLinks> {
  const out: Record<string, PageLinks> = {};
  for (const n of g.nodes) out[n.id] = { inbound: [], outbound: [], contextualIn: 0, contextualOut: 0 };
  for (const e of g.edges) {
    out[e.from].outbound.push(e); out[e.to].inbound.push(e);
    if (CONTEXTUAL.includes(e.type)) { out[e.from].contextualOut++; out[e.to].contextualIn++; }
  }
  return out;
}

/** The semantic links shown on a page, resolved to labels and paths. */
export function semanticLinksFor(slug: string) {
  const e = seoMap[slug];
  if (!e) return [];
  return e.to.map(([t, anchor, why]) => {
    const href = pathOf(t);
    return { href, anchor, why, group: t.startsWith('i:') ? 'industry' : t.startsWith('/') ? 'page' : (findGroup(t) ? 'category' : 'service') };
  });
}
