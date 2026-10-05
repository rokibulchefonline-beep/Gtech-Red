// Tiny in-memory cache for data (never connections) that is read on every admin request, such as the
// signed-in user and the site settings. Entries live a few seconds, and are dropped immediately when
// this server instance writes the same record, so edits show up straight away.
const store = (globalThis as unknown as { _memo?: Map<string, { v: unknown; exp: number }> });
const mem = () => (store._memo ??= new Map());

export async function memo<T>(key: string, ttlMs: number, fn: () => Promise<T>): Promise<T> {
  const hit = mem().get(key);
  if (hit && hit.exp > Date.now()) return hit.v as T;
  const v = await fn();
  mem().set(key, { v, exp: Date.now() + ttlMs });
  return v;
}
export const forget = (prefix: string) => { for (const k of [...mem().keys()]) if (k.startsWith(prefix)) mem().delete(k); };
