import { industryContent } from '@/content/industries';
import { seoMap } from '@/content/seo-map';
import { serviceContent } from '@/content/services';
import type { ServiceContent } from '@/content/types';
import { withOverrides } from '@/lib/content';
import { buildGraph, linkStats, type PageLinks } from '@/lib/link-graph';
import { findGroup, findItem, industries } from '@/lib/data';
import { getSettings } from '@/lib/settings';
import { list } from '@/lib/store';

// Per-page audit for SEO (classic ranking signals), AEO (answer engines: featured snippets, voice,
// AI answers) and GEO (generative engines: entity clarity, evidence, freshness). Pure analysis of the
// content that is rendered, so the score reflects the page the visitor and the crawler actually get.

export type Level = 'pass' | 'warn' | 'fail';
export type AuditCheck = { id: string; group: 'SEO' | 'AEO' | 'GEO'; level: Level; label: string; detail?: string; fix?: string; weight: number };
export type PageAudit = {
  path: string; name: string; kind: 'service' | 'industry'; keyword: string; core: string;
  scores: { seo: number; aeo: number; geo: number; total: number };
  stats: { words: number; faqs: number; h2: number; linksIn: number; linksOut: number; titleLen: number; descLen: number };
  checks: AuditCheck[];
};

const words = (s: string) => s.match(/[A-Za-z0-9£%'’-]+/g) ?? [];
const norm = (t: string) => t.toLowerCase().replace(/&/g, 'and');
const has = (hay: string, needle: string) => norm(hay).includes(norm(needle));
export const coreOf = (kw: string) => kw.replace(/\b(services?|company|agency|uk|consultancy|management)\b/gi, '').replace(/\s+/g, ' ').trim();

function bodyOf(c: ServiceContent) {
  const parts: string[] = [c.hero.lead, ...c.hero.points];
  const h2: string[] = [];
  for (const s of c.sections) {
    const x = s as unknown as Record<string, unknown>;
    if (typeof x.heading === 'string') { h2.push(x.heading); parts.push(x.heading); }
    for (const k of ['paras', 'bullets']) if (Array.isArray(x[k])) parts.push(...(x[k] as string[]));
    if (typeof x.text === 'string') parts.push(x.text);
    if (typeof x.intro === 'string') parts.push(x.intro);
    for (const k of ['cards', 'steps', 'metrics', 'stats']) if (Array.isArray(x[k])) for (const i of x[k] as Record<string, string>[]) parts.push(Object.values(i).join(' '));
    if (Array.isArray(x.rows)) for (const r of x.rows as string[][]) parts.push(r.join(' '));
    if (Array.isArray(x.items)) for (const i of x.items as Record<string, string>[]) if (i.text) parts.push(i.text);
    if (Array.isArray(x.reviews)) for (const r of x.reviews as Record<string, string>[]) parts.push(r.text);
  }
  for (const f of c.faqs) parts.push(f.q, f.a);
  return { text: parts.join(' \n'), h2 };
}

export function auditPage(kind: 'service' | 'industry', c: ServiceContent, links: PageLinks | undefined, ctx: { socials: number; schemaOff: boolean; focus?: string }): PageAudit {
  const slug = c.slug;
  const e = seoMap[slug];
  const path = `/${kind === 'service' ? 'services' : 'industries'}/${slug}`;
  const name = c.short ?? findItem(slug)?.item.name ?? findGroup(slug)?.title ?? slug;
  const keyword = ctx.focus || e?.kw || name;
  const core = coreOf(keyword);
  const h1 = `${c.hero.keyword ?? name} Services of GTech Digital`;
  const { text, h2 } = bodyOf(c);
  const w = words(text);
  const lead = c.hero.lead;
  const leadWords = words(lead).length;
  const checks: AuditCheck[] = [];
  const add = (id: string, group: AuditCheck['group'], level: Level, label: string, weight: number, detail?: string, fix?: string) => checks.push({ id, group, level, label, weight, detail, fix });
  const tl = c.metaTitle.length, dl = c.metaDescription.length;

  // ---- SEO ----
  add('title-len', 'SEO', tl >= 30 && tl <= 60 ? 'pass' : tl > 70 || tl < 20 ? 'fail' : 'warn', `SEO title length (${tl})`, 2, 'Aim for 30–60 characters so Google does not truncate it.', 'Shorten or expand the SEO title.');
  add('title-kw', 'SEO', has(c.metaTitle, core) ? 'pass' : 'fail', 'Primary keyword in the SEO title', 3, `Target: “${keyword}” (core: “${core}”).`, `Add “${core}” near the start of the title.`);
  add('desc-len', 'SEO', dl >= 120 && dl <= 160 ? 'pass' : dl > 175 || dl < 90 ? 'fail' : 'warn', `Meta description length (${dl})`, 2, 'Aim for 120–160 characters.', 'Rewrite the meta description to 120–160 characters.');
  add('desc-kw', 'SEO', has(c.metaDescription, core) ? 'pass' : 'fail', 'Primary keyword in the meta description', 2, undefined, `Mention “${core}” in the description.`);
  add('h1-kw', 'SEO', has(h1, core) ? 'pass' : has(h1, core.split(' ')[0]) ? 'warn' : 'fail', 'Primary keyword in the H1', 3, `H1: “${h1}”.`, `Set the hero keyword so the H1 contains “${core}”.`);
  add('lead-kw', 'SEO', has(lead, core) ? 'pass' : 'fail', 'Primary keyword in the opening description', 2, undefined, `Use “${core}” in the first sentence.`);
  const sec = e?.sec ?? [];
  const found = sec.filter((t) => has(text, t.replace(/^(a|an|the) /, '')));
  const cov = sec.length ? found.length / sec.length : 1;
  add('sec-kw', 'SEO', cov >= 0.6 ? 'pass' : cov >= 0.35 ? 'warn' : 'fail', `Related keywords used (${found.length}/${sec.length})`, 3, sec.filter((t) => !found.includes(t)).length ? `Missing: ${sec.filter((t) => !found.includes(t)).join(', ')}.` : undefined, 'Work the missing phrases into headings, bullets or paragraphs.');
  const kwHeads = h2.filter((h) => has(h, core) || sec.some((t) => has(h, t)));
  add('h2-kw', 'SEO', kwHeads.length >= 3 ? 'pass' : kwHeads.length >= 1 ? 'warn' : 'fail', `Subheadings with keywords (${kwHeads.length}/${h2.length})`, 2, undefined, 'Include the primary or a related keyword in more H2s.');
  add('h2-count', 'SEO', h2.length >= 8 ? 'pass' : h2.length >= 5 ? 'warn' : 'fail', `Page structure (${h2.length} sections)`, 1);
  add('words', 'SEO', w.length >= 800 ? 'pass' : w.length >= 600 ? 'warn' : 'fail', `Content depth (${w.length} words)`, 2, 'Aim for 800+ words of useful, non-repetitive copy.', 'Add detail, examples or FAQs.');
  const dens = w.length ? ((text.toLowerCase().split(core.toLowerCase()).length - 1) / w.length) * 100 : 0;
  add('density', 'SEO', dens >= 0.3 && dens <= 2.5 ? 'pass' : dens > 3.5 ? 'fail' : 'warn', `Keyword density ${dens.toFixed(2)}%`, 1, 'Keep between about 0.3% and 2.5%.');
  const lo = links?.contextualOut ?? 0, li = links?.contextualIn ?? 0;
  add('links-out', 'SEO', lo >= 6 ? 'pass' : lo >= 3 ? 'warn' : 'fail', `Contextual internal links out (${lo})`, 2, undefined, 'Add semantic links in Admin or content/seo-map.ts.');
  add('links-in', 'SEO', li >= 4 ? 'pass' : li >= 1 ? 'warn' : 'fail', `Contextual internal links in (${li})`, 2, li === 0 ? 'Orphan page: nothing in the body links here.' : undefined, 'Link to this page from related service and industry pages.');

  // ---- AEO ----
  const snippet = leadWords >= 25 && leadWords <= 60 && has(lead, 'GTech Digital') && has(lead, core);
  add('lead-snippet', 'AEO', snippet ? 'pass' : leadWords >= 20 && leadWords <= 70 && has(lead, 'GTech Digital') ? 'warn' : 'fail', `Snippet-ready opening (${leadWords} words)`, 3, 'A 25–60 word direct answer that names GTech Digital and the keyword is what featured snippets and AI answers quote.', 'Rewrite the lead as one clear definition sentence.');
  add('faq-count', 'AEO', c.faqs.length >= 6 ? 'pass' : c.faqs.length >= 4 ? 'warn' : 'fail', `FAQ questions (${c.faqs.length})`, 2, undefined, 'Add questions people actually ask (People Also Ask style).');
  const good = c.faqs.filter((f) => { const n = words(f.a).length; return n >= 25 && n <= 85; });
  add('faq-len', 'AEO', c.faqs.length && good.length / c.faqs.length >= 0.8 ? 'pass' : c.faqs.length && good.length / c.faqs.length >= 0.5 ? 'warn' : 'fail', `FAQ answers 25–85 words (${good.length}/${c.faqs.length})`, 3, 'Answers in this range are the length AI assistants and snippets prefer.', 'Expand one-line answers or trim long ones.');
  const qOk = c.faqs.filter((f) => /^(what|how|why|when|who|which|can|do|does|is|are|should|will|where)\b/i.test(f.q.trim()));
  add('faq-form', 'AEO', c.faqs.length && qOk.length === c.faqs.length ? 'pass' : qOk.length / Math.max(1, c.faqs.length) >= 0.7 ? 'warn' : 'fail', `Questions written as real questions (${qOk.length}/${c.faqs.length})`, 1);
  add('definition', 'AEO', h2.some((h) => /^what (is|are)\b/i.test(h)) ? 'pass' : 'warn', 'A “What is …” definition section', 2, undefined, 'Add a heading that defines the service in plain words.');
  add('structured-answers', 'AEO', c.sections.some((s) => s.type === 'table' || s.type === 'steps') ? 'pass' : 'fail', 'Process steps or comparison table (extractable answers)', 2, undefined, 'Add a numbered process or a comparison table.');
  add('faq-schema', 'AEO', ctx.schemaOff ? 'fail' : 'pass', 'FAQPage schema emitted', 1, ctx.schemaOff ? 'Automatic schema is switched off for this page.' : undefined);

  // ---- GEO ----
  const brand = (text.match(/GTech Digital/g) ?? []).length + (c.metaTitle.includes('GTech') ? 1 : 0) + 1; // +1: the “Reviewed by GTech Digital specialists” line every page template shows
  add('brand', 'GEO', brand >= 3 ? 'pass' : brand >= 1 ? 'warn' : 'fail', `Brand entity mentioned (${brand}×)`, 2, 'Generative engines build an entity from repeated, consistent naming.', 'Mention GTech Digital in the lead, a section and an FAQ answer.');
  const ent = e?.ent ?? [];
  const entFound = ent.filter((t) => has(text, t));
  add('entities', 'GEO', !ent.length || entFound.length / ent.length >= 0.6 ? 'pass' : entFound.length / ent.length >= 0.35 ? 'warn' : 'fail', `Topic entities covered (${entFound.length}/${ent.length})`, 3, ent.filter((t) => !entFound.includes(t)).length ? `Missing: ${ent.filter((t) => !entFound.includes(t)).join(', ')}.` : undefined, 'Name the platforms, standards and concepts people associate with this service.');
  const nums = (text.match(/(£\s?\d[\d,.]*[kKmM]?|\d[\d,.]*\s?(%|x\b|months?|weeks?|days?|hours?|years?|\+))/g) ?? []).length;
  add('evidence', 'GEO', nums >= 8 ? 'pass' : nums >= 4 ? 'warn' : 'fail', `Specific numbers and evidence (${nums})`, 2, 'Concrete figures make a page citable.', 'Add timeframes, ranges, prices and results.');
  add('proof', 'GEO', c.sections.some((s) => s.type === 'reviews') && c.sections.some((s) => s.type === 'cases') ? 'pass' : 'warn', 'Reviews and case studies on the page', 2);
  const uk = (text.match(/\bUK\b|United Kingdom|British/g) ?? []).length;
  add('geo-ref', 'GEO', uk >= 2 ? 'pass' : uk >= 1 ? 'warn' : 'fail', `UK location signals (${uk})`, 1);
  add('sameas', 'GEO', ctx.socials >= 3 ? 'pass' : 'warn', `Organization schema links to profiles (${ctx.socials} sameAs)`, 1, undefined, 'Add social profile URLs in Admin > Settings.');
  add('fresh', 'GEO', ctx.schemaOff ? 'warn' : 'pass', 'Freshness: dateModified in schema', 1);

  const score = (g?: AuditCheck['group']) => {
    const cs = checks.filter((x) => !g || x.group === g);
    const t = cs.reduce((n, x) => n + x.weight, 0);
    return Math.round((cs.reduce((n, x) => n + x.weight * (x.level === 'pass' ? 1 : x.level === 'warn' ? 0.5 : 0), 0) / t) * 100);
  };
  return {
    path, name, kind, keyword, core, checks,
    scores: { seo: score('SEO'), aeo: score('AEO'), geo: score('GEO'), total: score() },
    stats: { words: w.length, faqs: c.faqs.length, h2: h2.length, linksIn: li, linksOut: lo, titleLen: tl, descLen: dl },
  };
}

export async function auditSite() {
  const [settings, seoDocs] = await Promise.all([getSettings(), list('seo', { limit: 500 }).catch(() => [])]);
  const seoBy = new Map(seoDocs.map((d) => [d._id as string, d]));
  const graph = buildGraph();
  const stats = linkStats(graph);
  const pages: PageAudit[] = [];
  for (const [kind, map] of [['service', serviceContent], ['industry', industryContent]] as const) {
    for (const [slug, base] of Object.entries(map)) {
      const c = await withOverrides(kind, base);
      const path = `/${kind === 'service' ? 'services' : 'industries'}/${slug}`;
      const o = seoBy.get(`${kind === 'service' ? 'services' : 'industries'}~${slug}`);
      pages.push(auditPage(kind, c, stats[path], { socials: settings.socials.filter((s) => s.url).length, schemaOff: !!o?.schemaOff, focus: o?.focusKeyword }));
    }
  }
  const avg = (k: keyof PageAudit['scores']) => Math.round(pages.reduce((n, p) => n + p.scores[k], 0) / Math.max(1, pages.length));
  // cannibalisation: pages competing for the same core keyword
  const byCore = new Map<string, string[]>();
  for (const p of pages) byCore.set(p.core.toLowerCase(), [...(byCore.get(p.core.toLowerCase()) ?? []), p.path]);
  const conflicts = [...byCore.entries()].filter(([, v]) => v.length > 1).map(([kw, paths]) => ({ keyword: kw, paths }));
  // broken semantic targets
  const known = new Set(graph.nodes.map((n) => n.id));
  const broken = graph.edges.filter((e) => e.type === 'semantic' && !known.has(e.to)).map((e) => `${e.from} → ${e.to}`);
  const orphans = graph.nodes.filter((n) => (stats[n.id].contextualIn === 0) && (n.kind === 'service' || n.kind === 'industry' || n.kind === 'category')).map((n) => n.id);
  return {
    summary: { pages: pages.length, seo: avg('seo'), aeo: avg('aeo'), geo: avg('geo'), total: avg('total'), conflicts: conflicts.length, orphans: orphans.length, broken: broken.length, sectors: industries.length },
    pages, conflicts, orphans, broken, graph: { nodes: graph.nodes, edges: graph.edges, stats: Object.fromEntries(Object.entries(stats).map(([k, v]) => [k, { in: v.contextualIn, out: v.contextualOut }])) },
  };
}
