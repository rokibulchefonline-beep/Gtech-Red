import { hashPassword, passwordProblem, roleList, type Role, type SessionUser } from '@/lib/auth';
import { bool, longStr, num, oneOf, slugOf, str, strs, url } from '@/lib/admin-api';
import { sanitizeHtml } from '@/lib/sanitize-html';
import { validateCustomSchema } from '@/lib/schema';
import { count, findOne, list, type Rec } from '@/lib/store';

type Ctx = { user: SessionUser; existing?: Rec | null; creating: boolean };
export type Resource = {
  coll: string;
  perm: 'content' | 'leads' | 'users';
  search: string[];
  sort: Record<string, 1 | -1>;
  /** Single-document keyed resources (page content, SEO): PUT creates the record. */
  keyed?: boolean;
  noCreate?: boolean;
  clean: (input: Rec, ctx: Ctx) => Promise<Rec | { error: string }> | Rec | { error: string };
  view?: (d: Rec) => Rec;
  canDelete?: (d: Rec, ctx: Ctx) => Promise<string | null> | string | null;
};

const status = ['draft', 'published', 'scheduled'] as const;
const metrics = (v: unknown) => (Array.isArray(v) ? v.slice(0, 4).map((m: Rec) => ({ value: str(m?.value, 24), label: str(m?.label, 40) })).filter((m) => m.value && m.label) : []);

async function uniqueSlug(coll: string, slug: string, id?: string) {
  const hit = await findOne(coll, { slug });
  return !hit || hit._id === id;
}

export const resources: Record<string, Resource> = {
  posts: {
    coll: 'posts', perm: 'content', search: ['title', 'category', 'slug'], sort: { updatedAt: -1 },
    async clean(i, { existing }) {
      const title = str(i.title, 160);
      const slug = slugOf(str(i.slug) || title);
      if (!title) return { error: 'A title is required.' };
      if (!slug) return { error: 'A URL slug is required.' };
      if (!(await uniqueSlug('posts', slug, existing?._id))) return { error: 'Another post already uses that URL slug.' };
      const st = oneOf(i.status, status, 'draft');
      const cats = strs(i.categories, 10, 60);
      if (!cats.length && str(i.category, 60)) cats.push(str(i.category, 60));
      const fields = Array.isArray(i.customFields) ? i.customFields.slice(0, 30).map((f: Rec) => ({ name: str(f?.name, 80), value: str(f?.value, 1000) })).filter((f) => f.name) : [];
      return {
        title, slug, excerpt: str(i.excerpt, 400), format: 'html', body: sanitizeHtml(longStr(i.body, 400000)), category: cats[0] || 'Insights', categories: cats, tags: strs(i.tags, 12, 40),
        postFormat: oneOf(i.postFormat, ['standard', 'aside', 'image', 'video', 'quote', 'link', 'gallery', 'status', 'audio', 'chat'] as const, 'standard'),
        visibility: oneOf(i.visibility, ['public', 'private'] as const, 'public'), allowComments: i.allowComments !== false, allowPingbacks: i.allowPingbacks !== false, customFields: fields,
        image: url(i.image), imageAlt: str(i.imageAlt, 200), author: str(i.author, 80) || 'GTech Editorial Team', featured: bool(i.featured),
        status: st, date: str(i.date, 40) || (st === 'published' ? new Date().toISOString() : ''),
        metaTitle: str(i.metaTitle, 120), metaDescription: str(i.metaDescription, 300), focusKeyword: str(i.focusKeyword, 80), canonical: url(i.canonical), noindex: bool(i.noindex),
      };
    },
  },
  'case-studies': {
    coll: 'case_studies', perm: 'content', search: ['title', 'client', 'industry', 'slug'], sort: { order: 1, updatedAt: -1 },
    async clean(i, { existing }) {
      const title = str(i.title, 120);
      const slug = slugOf(str(i.slug) || title);
      if (!title || !slug) return { error: 'A title and URL slug are required.' };
      if (!(await uniqueSlug('case_studies', slug, existing?._id))) return { error: 'Another case study already uses that URL slug.' };
      const q = (i.quote ?? {}) as Rec;
      return {
        title, slug, client: str(i.client, 120) || title, industry: str(i.industry, 80), duration: str(i.duration, 60), website: url(i.website),
        excerpt: str(i.excerpt, 300), image: url(i.image), imageAlt: str(i.imageAlt, 200), logo: url(i.logo), services: strs(i.services, 20, 80),
        metrics: metrics(i.metrics), challenge: longStr(i.challenge, 5000), solution: longStr(i.solution, 5000), results: strs(i.results, 10, 200),
        quote: { text: str(q.text, 500), name: str(q.name, 80), role: str(q.role, 120) },
        status: oneOf(i.status, ['draft', 'published'] as const, 'draft'), order: num(i.order, 100),
        metaTitle: str(i.metaTitle, 120), metaDescription: str(i.metaDescription, 300), focusKeyword: str(i.focusKeyword, 80),
      };
    },
  },
  categories: {
    coll: 'categories', perm: 'content', search: ['name'], sort: { name: 1 },
    async clean(i, { existing }) {
      const name = str(i.name, 60), slug = slugOf(name);
      if (!name || !slug) return { error: 'Enter a category name.' };
      const hit = await findOne('categories', { slug });
      if (hit && hit._id !== existing?._id) return { error: 'That category already exists.' };
      return { name, slug };
    },
  },
  partners: {
    coll: 'partners', perm: 'content', search: ['name'], sort: { order: 1 },
    clean: (i) => (str(i.name, 80) ? { name: str(i.name, 80), logo: url(i.logo), url: url(i.url), order: num(i.order, 100), visible: i.visible !== false } : { error: 'A name is required.' }),
  },
  clients: {
    coll: 'clients', perm: 'content', search: ['name'], sort: { order: 1 },
    clean: (i) => (str(i.name, 80) ? { name: str(i.name, 80), logo: url(i.logo), url: url(i.url), order: num(i.order, 100), visible: i.visible !== false } : { error: 'A name is required.' }),
  },
  'page-content': {
    coll: 'page_content', perm: 'content', search: ['slug'], sort: { slug: 1 }, keyed: true,
    clean(i) {
      const sections: Rec = {};
      for (const [id, s] of Object.entries((i.sections ?? {}) as Rec).slice(0, 60)) {
        const v = s as Rec;
        sections[id.slice(0, 60)] = {
          ...(v.heading !== undefined && { heading: str(v.heading, 240) }),
          ...(v.paras !== undefined && { paras: strs(v.paras, 12, 6000).map((x) => sanitizeHtml(x)) }),
          ...(v.bullets !== undefined && { bullets: strs(v.bullets, 20, 800).map((x) => sanitizeHtml(x)) }),
          ...(v.text !== undefined && { text: sanitizeHtml(str(v.text, 1500)) }),
        };
      }
      const h = (i.hero ?? {}) as Rec;
      return {
        kind: oneOf(i.kind, ['service', 'industry'] as const, 'service'), slug: str(i.slug, 80),
        metaTitle: str(i.metaTitle, 120), metaDescription: str(i.metaDescription, 300), focusKeyword: str(i.focusKeyword, 80),
        hero: { keyword: str(h.keyword, 80), h1: str(h.h1, 200), lead: sanitizeHtml(str(h.lead, 1500)), points: strs(h.points, 5, 120) },
        sections,
        faqs: Array.isArray(i.faqs) ? i.faqs.slice(0, 20).map((f: Rec) => ({ q: str(f?.q, 250), a: sanitizeHtml(str(f?.a, 4000)) })).filter((f) => f.q && f.a) : [],
      };
    },
  },
  seo: {
    coll: 'seo', perm: 'content', search: ['path', 'title'], sort: { path: 1 }, keyed: true,
    clean(i) {
      const custom = longStr(i.schemaCustom, 20000).trim();
      const bad = validateCustomSchema(custom);
      if (bad) return { error: bad };
      return { path: str(i.path, 200), title: str(i.title, 120), description: str(i.description, 300), canonical: url(i.canonical), ogImage: url(i.ogImage), noindex: bool(i.noindex), focusKeyword: str(i.focusKeyword, 80), schemaOff: bool(i.schemaOff), schemaCustom: custom };
    },
  },
  leads: {
    coll: 'leads', perm: 'leads', search: ['name', 'business', 'email', 'phone', 'service'], sort: { createdAt: -1 }, noCreate: true,
    clean: (i, { existing }) => ({
      status: oneOf(i.status ?? existing?.status, ['new', 'contacted', 'qualified', 'won', 'lost'] as const, 'new'),
      notes: longStr(i.notes, 5000), assignee: str(i.assignee, 80), value: num(i.value, 0),
    }),
  },
  users: {
    coll: 'users', perm: 'users', search: ['name', 'email'], sort: { createdAt: 1 },
    view: (d) => { const { passwordHash, ...rest } = d; void passwordHash; return rest; },
    async clean(i, { existing, creating, user }) {
      const email = str(i.email, 160).toLowerCase();
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return { error: 'Enter a valid email address.' };
      const hit = await findOne('users', { email });
      if (hit && hit._id !== existing?._id) return { error: 'That email is already in use.' };
      const role = oneOf(i.role, roleList as readonly Role[], 'editor');
      const active = i.active !== false;
      if (existing && existing._id === user.id && (role !== existing.role || !active)) return { error: 'You cannot change your own role or deactivate yourself.' };
      if (existing?.role === 'super_admin' && (role !== 'super_admin' || !active) && (await count('users', { role: 'super_admin', active: { $ne: false } })) <= 1) return { error: 'There must be at least one active super admin.' };
      const out: Rec = { name: str(i.name, 80) || email.split('@')[0], email, role, active };
      const pw = String(i.password ?? '');
      if (creating || pw) {
        const problem = passwordProblem(pw);
        if (problem) return { error: problem };
        out.passwordHash = await hashPassword(pw);
      }
      return out;
    },
    async canDelete(d, { user }) {
      if (d._id === user.id) return 'You cannot delete your own account.';
      if (d.role === 'super_admin' && (await count('users', { role: 'super_admin', active: { $ne: false } })) <= 1) return 'There must be at least one super admin.';
      return null;
    },
  },
};

export async function listResource(r: Resource, q: { search?: string; filter?: Rec; page?: number; size?: number }) {
  const filter: Rec = { ...(q.filter ?? {}) };
  if (q.search) filter.$or = r.search.map((f) => ({ [f]: { $regex: q.search!.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), $options: 'i' } }));
  const size = Math.min(200, q.size ?? 50), page = Math.max(1, q.page ?? 1);
  const [rows, total] = await Promise.all([list(r.coll, { filter, sort: r.sort, limit: size, skip: (page - 1) * size }), count(r.coll, filter)]);
  return { rows: rows.map(r.view ?? ((d) => d)), total, page, size };
}
