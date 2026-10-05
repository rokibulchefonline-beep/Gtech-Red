import { fail, json, str } from '@/lib/admin-api';
import { createSession, hashPassword, passwordProblem } from '@/lib/auth';
import { count, insert } from '@/lib/store';

export const dynamic = 'force-dynamic';

// One-time creation of the first super admin. Disabled as soon as any user exists.
// If SETUP_KEY is set, the form must supply it (recommended on a public site).
export async function POST(req: Request) {
  if ((await count('users')) > 0) return fail('Setup is already complete.', 403);
  const b = await req.json().catch(() => ({}));
  if (process.env.SETUP_KEY && b.setupKey !== process.env.SETUP_KEY) return fail('Incorrect setup key.', 403);
  const email = str(b.email, 160).toLowerCase();
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return fail('Enter a valid email address.');
  const problem = passwordProblem(String(b.password ?? ''));
  if (problem) return fail(problem);
  const u = await insert('users', { name: str(b.name, 80) || 'Super Admin', email, role: 'super_admin', active: true, passwordHash: await hashPassword(String(b.password)) });
  await createSession(u._id);
  return json({ ok: true });
}
