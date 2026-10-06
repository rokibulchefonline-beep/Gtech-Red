import { forget } from '@/lib/cache';
import { withDb } from '@/lib/mongo';

// Tiny data-access layer used by the admin and the public site. Documents use string ids (`_id`).
// Sources, in order:
//   1. Laravel backend (LARAVEL_API_URL + LARAVEL_API_TOKEN): read-only; content is edited in the Laravel panel.
//   2. MongoDB (MONGODB_URI): the original setup, kept until the switch-over.
//   3. In-memory store for local development and tests (NODE_ENV not "production" or ALLOW_MEMORY_DB=1).

export type Rec = Record<string, any>; // eslint-disable-line @typescript-eslint/no-explicit-any
export type Query = {
  filter?: Rec; sort?: Record<string, 1 | -1>; limit?: number; skip?: number;
};

const mem = (globalThis as unknown as { _memdb?: Map<string, Rec[]> });
const memory = (): Map<string, Rec[]> => (mem._memdb ??= new Map<string, Rec[]>());
const useMemory = () => !process.env.MONGODB_URI && (process.env.NODE_ENV !== 'production' || process.env.ALLOW_MEMORY_DB === '1');

// --- Laravel backend (read only) -----------------------------------------------------------------
export const laravel = () => (process.env.LARAVEL_API_URL ? { url: process.env.LARAVEL_API_URL.replace(/\/$/, ''), token: process.env.LARAVEL_API_TOKEN ?? '' } : null);
async function remote(body: Rec): Promise<Rec> {
  const b = laravel()!;
  const res = await fetch(`${b.url}/api/v1/query`, { method: 'POST', headers: { 'content-type': 'application/json', accept: 'application/json', 'x-api-key': b.token }, body: JSON.stringify(body) });
  const json = await res.json().catch(() => ({}));
  if (!res.ok || !json.ok) throw new Error(`Laravel API: ${json.error ?? res.status}`);
  return json;
}
const readOnly = () => { throw new Error('This data is managed in the Laravel admin panel.'); };

export const newId = () => crypto.randomUUID().replace(/-/g, '').slice(0, 24);

// --- minimal Mongo-style matcher for the in-memory store -------------------------------------
const get = (o: Rec, path: string) => path.split('.').reduce<any>((v, k) => (v == null ? v : v[k]), o); // eslint-disable-line @typescript-eslint/no-explicit-any
function match(d: Rec, f: Rec): boolean {
  return Object.entries(f).every(([k, cond]) => {
    if (k === '$or') return (cond as Rec[]).some((c) => match(d, c));
    if (k === '$and') return (cond as Rec[]).every((c) => match(d, c));
    const v = get(d, k);
    if (cond && typeof cond === 'object' && !(cond instanceof Date) && !Array.isArray(cond)) {
      return Object.entries(cond as Rec).every(([op, x]) => {
        if (op === '$in') return (x as unknown[]).includes(v) || (Array.isArray(v) && v.some((i) => (x as unknown[]).includes(i)));
        if (op === '$ne') return v !== x;
        if (op === '$regex') return new RegExp(String(x), (cond as Rec).$options ?? '').test(String(v ?? ''));
        if (op === '$options') return true;
        if (op === '$lte') return v <= x;
        if (op === '$gte') return v >= x;
        if (op === '$exists') return (v !== undefined) === x;
        return false;
      });
    }
    return Array.isArray(v) ? v.includes(cond) : v === cond;
  });
}
const cmp = (a: unknown, b: unknown) => (a === b ? 0 : (a as number) > (b as number) ? 1 : -1);

export async function list(coll: string, q: Query = {}): Promise<Rec[]> {
  if (laravel()) return (await remote({ coll, filter: q.filter ?? {}, sort: q.sort ?? {}, limit: q.limit ?? 500, skip: q.skip ?? 0 })).rows as Rec[];
  if (useMemory()) {
    let rows = (memory().get(coll) ?? []).filter((d) => match(d, q.filter ?? {}));
    for (const [k, dir] of Object.entries(q.sort ?? {}).reverse()) rows = [...rows].sort((a, b) => dir * cmp(get(a, k), get(b, k)));
    return rows.slice(q.skip ?? 0, (q.skip ?? 0) + (q.limit ?? 1000)).map((r) => structuredClone(r));
  }
  return withDb((db) => db.collection(coll).find(q.filter ?? {}).sort(q.sort ?? {}).skip(q.skip ?? 0).limit(q.limit ?? 1000).toArray() as Promise<Rec[]>);
}

export async function count(coll: string, filter: Rec = {}): Promise<number> {
  if (laravel()) return (await remote({ coll, filter, count: true })).total as number;
  if (useMemory()) return (memory().get(coll) ?? []).filter((d) => match(d, filter)).length;
  return withDb((db) => db.collection(coll).countDocuments(filter));
}

export async function findOne(coll: string, filter: Rec): Promise<Rec | null> {
  return (await list(coll, { filter, limit: 1 }))[0] ?? null;
}

export async function insert(coll: string, doc: Rec): Promise<Rec> {
  if (laravel()) return readOnly();
  const d = { ...doc, _id: doc._id ?? newId(), createdAt: doc.createdAt ?? new Date(), updatedAt: new Date() };
  if (useMemory()) { const m = memory(); m.set(coll, [...(m.get(coll) ?? []), structuredClone(d)]); return d; }
  await withDb((db) => db.collection(coll).insertOne(d as never));
  return d;
}

export async function update(coll: string, id: string, patch: Rec): Promise<boolean> {
  if (laravel()) return readOnly();
  if (coll === 'users') forget(`user:${id}`);
  if (coll === 'settings') forget('settings');
  const set: Rec = { ...patch, updatedAt: new Date() };
  delete set._id;
  if (useMemory()) {
    const rows = memory().get(coll) ?? [];
    const i = rows.findIndex((r) => r._id === id);
    if (i < 0) return false;
    rows[i] = { ...rows[i], ...structuredClone(set) };
    return true;
  }
  const r = await withDb((db) => db.collection(coll).updateOne({ _id: id as never }, { $set: set }));
  return r.matchedCount > 0;
}

/** Insert or replace the document with this id (used for keyed singletons such as settings). */
export async function upsert(coll: string, id: string, doc: Rec): Promise<void> {
  if (!(await update(coll, id, doc))) await insert(coll, { ...doc, _id: id });
}

export async function remove(coll: string, id: string): Promise<boolean> {
  if (laravel()) return readOnly();
  if (coll === 'users') forget(`user:${id}`);
  if (useMemory()) {
    const rows = memory().get(coll) ?? [];
    const n = rows.length;
    memory().set(coll, rows.filter((r) => r._id !== id));
    return rows.length !== n;
  }
  return ((await withDb((db) => db.collection(coll).deleteOne({ _id: id as never }))).deletedCount ?? 0) > 0;
}
