import { NextResponse } from 'next/server';
import { can, currentUser, type Perm, type SessionUser } from '@/lib/auth';

export const json = (data: unknown, status = 200) => NextResponse.json(data, { status, headers: { 'Cache-Control': 'no-store' } });
export const fail = (error: string, status = 400) => json({ ok: false, error }, status);

/** Auth + permission + same-origin check for mutating requests (CSRF defence on top of SameSite). */
export async function guard(req: Request, perm: Perm | null): Promise<{ user: SessionUser } | { res: NextResponse }> {
  if (req.method !== 'GET' && req.method !== 'HEAD') {
    const origin = req.headers.get('origin');
    if (origin && new URL(origin).host !== req.headers.get('host')) return { res: fail('Cross-site request blocked.', 403) };
  }
  const user = await currentUser();
  if (!user) return { res: fail('Please sign in.', 401) };
  if (perm && !can(user.role, perm)) return { res: fail('You do not have permission to do that.', 403) };
  return { user };
}

/** Runs a route body and turns unexpected errors (usually the database) into a readable JSON message. */
export async function safe(fn: () => Promise<NextResponse>): Promise<NextResponse> {
  try { return await fn(); } catch (e) {
    const msg = (e instanceof Error ? e.message : String(e)).replace(/mongodb(\+srv)?:\/\/[^\s]+/gi, 'mongodb://…').slice(0, 220);
    console.error('admin api error:', e);
    return fail(`Server error: ${msg}`, 500);
  }
}

// ---- input helpers ---------------------------------------------------------------------------
export const str = (v: unknown, max = 500) => String(v ?? '').trim().slice(0, max);
export const longStr = (v: unknown, max = 100000) => String(v ?? '').replace(/\r/g, '').slice(0, max);
export const strs = (v: unknown, max = 100, each = 200) => (Array.isArray(v) ? v.map((x) => str(x, each)).filter(Boolean).slice(0, max) : []);
export const bool = (v: unknown) => v === true || v === 'true';
export const num = (v: unknown, d = 0) => (Number.isFinite(Number(v)) ? Number(v) : d);
export const slugOf = (s: string) => s.toLowerCase().replace(/&/g, ' and ').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '').slice(0, 80);
export const url = (v: unknown) => { const s = str(v, 600); return s === '' || /^(https?:\/\/|\/)/i.test(s) ? s : ''; };
export const oneOf = <T extends string>(v: unknown, list: readonly T[], d: T): T => (list.includes(v as T) ? (v as T) : d);
