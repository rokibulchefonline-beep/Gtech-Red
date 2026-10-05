import { fail, json, str } from '@/lib/admin-api';
import { createSession, verifyPassword } from '@/lib/auth';
import { findOne, update } from '@/lib/store';

export const dynamic = 'force-dynamic';
const tries = (globalThis as unknown as { _tries?: Map<string, { n: number; t: number }> });

export async function POST(req: Request) {
  const body = await req.json().catch(() => ({}));
  const email = str(body.email, 160).toLowerCase();
  const key = `${req.headers.get('cf-connecting-ip') ?? req.headers.get('x-forwarded-for') ?? 'ip'}|${email}`;
  const map = (tries._tries ??= new Map());
  const t = map.get(key);
  if (t && Date.now() - t.t < 15 * 60_000 && t.n >= 8) return fail('Too many attempts. Try again in 15 minutes.', 429);
  const u = await findOne('users', { email });
  // verify against a dummy hash when the user is missing so timing does not reveal valid emails
  const hash = u?.passwordHash ?? 'pbkdf2$100000$AAAAAAAAAAAAAAAAAAAAAA==$AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=';
  const pwOk = await verifyPassword(String(body.password ?? ''), hash).catch(() => false);
  const ok = !!u && u.active !== false && pwOk;
  if (!ok) {
    map.set(key, { n: (t && Date.now() - t.t < 15 * 60_000 ? t.n : 0) + 1, t: Date.now() });
    return fail('Incorrect email or password.', 401);
  }
  map.delete(key);
  await createSession(u._id);
  await update('users', u._id, { lastLogin: new Date() });
  return json({ ok: true });
}
