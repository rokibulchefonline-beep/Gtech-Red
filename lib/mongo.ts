import { AsyncLocalStorage } from 'node:async_hooks';
import { MongoClient, type Db } from 'mongodb';
import { demoCaseStudies } from './data';
import { findOne, list } from './store';

const globalForMongo = globalThis as unknown as { _mongo?: Promise<MongoClient> };

export async function getDb(): Promise<Db> {
  const uri = process.env.MONGODB_URI;
  if (!uri) throw new Error('MONGODB_URI is not set');
  // A failed connection must not be cached, or every later request would fail instantly.
  globalForMongo._mongo ??= new MongoClient(uri, { serverSelectionTimeoutMS: 8000, connectTimeoutMS: 8000 }).connect().catch((e) => { globalForMongo._mongo = undefined; throw e; });
  return (await globalForMongo._mongo).db(process.env.MONGODB_DB || 'gtech_red');
}

// Cloudflare Workers cannot reuse an I/O object (such as a database socket) created during an earlier
// request: the next request crashes with error 1101. On Workers we therefore connect per request,
// share that connection between the queries that run together, and close it when they are done.
// On Node (builds, local development) one cached client is used as before.
const onWorkers = () => typeof navigator !== 'undefined' && navigator.userAgent === 'Cloudflare-Workers';
type Scope = { c?: Promise<MongoClient>; n: number };
const scopes = new AsyncLocalStorage<Scope>();
let batch: Scope | undefined; // fallback: queries started in the same tick share one connection

const connect = (uri: string) => new MongoClient(uri, { serverSelectionTimeoutMS: 8000, connectTimeoutMS: 8000, maxPoolSize: 4, minPoolSize: 0, serverMonitoringMode: 'poll' }).connect();
const closeLater = (s: Scope) => { const c = s.c; s.c = undefined; c?.then((x) => x.close()).catch(() => {}); };

/** Runs a request handler with ONE database connection shared by everything it queries (Workers only). */
export function scoped<T>(fn: () => Promise<T>): Promise<T> {
  if (!onWorkers() || scopes.getStore()) return fn();
  const s: Scope = { n: 0 };
  return scopes.run(s, async () => { try { return await fn(); } finally { closeLater(s); } });
}

export async function withDb<T>(fn: (db: Db) => Promise<T>): Promise<T> {
  if (!onWorkers()) return fn(await getDb());
  const uri = process.env.MONGODB_URI;
  if (!uri) throw new Error('MONGODB_URI is not set');
  const name = process.env.MONGODB_DB || 'gtech_red';
  const own = scopes.getStore();
  if (own) { own.c ??= connect(uri); return fn((await own.c).db(name)); }
  // No request scope: only calls made in the same synchronous tick (same request) may share a connection.
  if (!batch) { const mine: Scope = { n: 0 }; batch = mine; queueMicrotask(() => { if (batch === mine) batch = undefined; }); }
  const scope = batch;
  scope.n++;
  scope.c ??= connect(uri);
  try { return await fn((await scope.c).db(name)); } finally { if (--scope.n <= 0) closeLater(scope); }
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
