import { MongoClient, type Db } from 'mongodb';
import { demoCaseStudies, services } from './data';

const globalForMongo = globalThis as unknown as { _mongo?: Promise<MongoClient> };

export async function getDb(): Promise<Db> {
  const uri = process.env.MONGODB_URI;
  if (!uri) throw new Error('MONGODB_URI is not set');
  globalForMongo._mongo ??= new MongoClient(uri).connect();
  return (await globalForMongo._mongo).db(process.env.MONGODB_DB || 'gtech_red');
}

export type Doc = { slug: string; title: string; excerpt?: string; body?: string; image?: string; logo?: string; services?: string[]; tags?: string[] };

// Three short attributes for the card hover: explicit `tags`, else the first matching service names.
const names = new Map(services.flatMap((g) => g.items.map((i) => [i.slug, i.name] as const)));
export const tagsFrom = (slugs?: string[]) => (slugs ?? []).map((s) => names.get(s)).filter((n): n is string => !!n).slice(0, 3);

const toDoc = (r: Record<string, any>): Doc => ({ // eslint-disable-line @typescript-eslint/no-explicit-any
  slug: r.slug, title: r.title, excerpt: r.excerpt, body: r.body, image: r.image, logo: r.logo, services: r.services, tags: r.tags ?? tagsFrom(r.services),
});

const withTags = <T extends Doc>(d: T): T => ({ ...d, tags: d.tags ?? tagsFrom(d.services) });

/** Fetch published docs; empty list if DB is unavailable. */
export async function listDocs(collection: 'posts' | 'case_studies', limit = 50): Promise<Doc[]> {
  try {
    const db = await getDb();
    const rows = await db.collection(collection).find({}).sort({ created_at: -1 }).limit(limit).toArray();
    const docs = rows.map(toDoc);
    return docs.length || collection !== 'case_studies' ? docs : demoCaseStudies.slice(0, limit).map(withTags);
  } catch {
    return collection === 'case_studies' ? demoCaseStudies.slice(0, limit).map(withTags) : [];
  }
}

export async function getDoc(collection: 'posts' | 'case_studies', slug: string): Promise<Doc | null> {
  try {
    const db = await getDb();
    const r = await db.collection(collection).findOne({ slug });
    if (r) return toDoc(r);
  } catch {
    // fall through to demo data
  }
  const demo = collection === 'case_studies' ? demoCaseStudies.find((c) => c.slug === slug) : undefined;
  return demo ? withTags(demo) : null;
}

/** Case studies tagged with a service slug (field `services` in MongoDB). */
export async function caseStudiesFor(service: string, limit = 6): Promise<Doc[]> {
  const all = await listDocs('case_studies', 50);
  return all.filter((d) => d.services?.includes(service)).slice(0, limit);
}
