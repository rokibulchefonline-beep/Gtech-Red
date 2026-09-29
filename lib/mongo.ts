import { MongoClient, type Db } from 'mongodb';

const globalForMongo = globalThis as unknown as { _mongo?: Promise<MongoClient> };

export async function getDb(): Promise<Db> {
  const uri = process.env.MONGODB_URI;
  if (!uri) throw new Error('MONGODB_URI is not set');
  globalForMongo._mongo ??= new MongoClient(uri).connect();
  return (await globalForMongo._mongo).db(process.env.MONGODB_DB || 'gtech_red');
}

export type Doc = { slug: string; title: string; excerpt?: string; body?: string };

/** Fetch published docs; empty list if DB is unavailable. */
export async function listDocs(collection: 'posts' | 'case_studies', limit = 50): Promise<Doc[]> {
  try {
    const db = await getDb();
    const rows = await db.collection(collection).find({}).sort({ created_at: -1 }).limit(limit).toArray();
    return rows.map((r) => ({ slug: r.slug, title: r.title, excerpt: r.excerpt, body: r.body }));
  } catch {
    return [];
  }
}

export async function getDoc(collection: 'posts' | 'case_studies', slug: string): Promise<Doc | null> {
  try {
    const db = await getDb();
    const r = await db.collection(collection).findOne({ slug });
    return r ? { slug: r.slug, title: r.title, excerpt: r.excerpt, body: r.body } : null;
  } catch {
    return null;
  }
}
