import { fail, guard, json, str, route } from '@/lib/admin-api';
import { hashPassword, passwordProblem, verifyPassword } from '@/lib/auth';
import { findOne, update } from '@/lib/store';

export const dynamic = 'force-dynamic';

// Account settings for the signed-in user (any role): name and password.
async function PUT_(req: Request) {
  const g = await guard(req, null);
  if ('res' in g) return g.res;
  const b = await req.json().catch(() => ({}));
  const patch: Record<string, unknown> = {};
  if (b.name !== undefined) { const n = str(b.name, 80); if (!n) return fail('Enter your name.'); patch.name = n; }
  if (b.newPassword) {
    const me = await findOne('users', { _id: g.user.id });
    if (!me || !(await verifyPassword(String(b.currentPassword ?? ''), me.passwordHash).catch(() => false))) return fail('Your current password is incorrect.');
    const problem = passwordProblem(String(b.newPassword));
    if (problem) return fail(problem);
    patch.passwordHash = await hashPassword(String(b.newPassword));
  }
  await update('users', g.user.id, patch);
  return json({ ok: true });
}

export const PUT = route(PUT_);
