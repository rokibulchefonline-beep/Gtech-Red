import { MongoClient, type Db } from 'mongodb';
import { demoCaseStudies } from './data';
import { findOne, list } from './store';

const globalForMongo = globalThis as unknown as { _mongo?: Promise<MongoClient> };

export async function getDb(): Promise<Db> {
  const uri = process.env.MONGODB_URI;
  if (!uri) throw new Error('MONGODB_URI is not set');
  globalForMongo._mongo ??= new MongoClient(uri).connect();
  return (await globalForMongo._mongo).db(process.env.MONGODB_DB || 'gtech_red');
}

export type Metric = { value: string; label: string };
export type Doc = {
  slug: string; title: string; excerpt?: string; body?: string; image?: string; imageAlt?: string; logo?: string; services?: string[];
  client?: string; industry?: string; duration?: string; website?: string; metrics?: Metric[];
  challenge?: string; solution?: string; results?: string[]; quote?: { text: string; name: string; role: string };
  metaTitle?: string; metaDescription?: string;
};

// Case studies live in the `case_studies` collection (managed in Admin > Case studies).
// Demo studies are shown until the first published one is added.
const toDoc = (r: Record<string, any>): Doc => ({ // eslint-disable-line @typescript-eslint/no-explicit-any
  slug: r.slug, title: r.title, excerpt: r.excerpt, body: r.body, image: r.image, imageAlt: r.imageAlt, logo: r.logo, services: r.services,
  client: r.client, industry: r.industry, duration: r.duration, website: r.website, metrics: r.metrics, challenge: r.challenge, solution: r.solution,
  results: r.results, quote: r.quote?.text ? r.quote : undefined, metaTitle: r.metaTitle, metaDescription: r.metaDescription,
});

/** Fetch published docs; demo studies if none exist or the database is unavailable. */
export async function listDocs(collection: 'posts' | 'case_studies', limit = 50): Promise<Doc[]> {
  try {
    const rows = await list(collection, { filter: { status: 'published' }, sort: { order: 1, createdAt: -1 }, limit });
    const docs = rows.map(toDoc);
    return docs.length || collection !== 'case_studies' ? docs : demoCaseStudies.slice(0, limit);
  } catch {
    return collection === 'case_studies' ? demoCaseStudies.slice(0, limit) : [];
  }
}

export async function getDoc(collection: 'posts' | 'case_studies', slug: string): Promise<Doc | null> {
  try {
    const r = await findOne(collection, { slug, status: 'published' });
    if (r) return toDoc(r);
  } catch {
    // fall through to demo data
  }
  return collection === 'case_studies' ? demoCaseStudies.find((c) => c.slug === slug) ?? null : null;
}

/** Case studies tagged with a service slug (field `services`). */
export async function caseStudiesFor(service: string, limit = 6): Promise<Doc[]> {
  const all = await listDocs('case_studies', 50);
  return all.filter((d) => d.services?.includes(service)).slice(0, limit);
}
