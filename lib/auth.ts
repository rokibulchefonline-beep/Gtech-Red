import { SignJWT, jwtVerify } from 'jose';
import { cookies } from 'next/headers';
import { findOne } from '@/lib/store';

// Session auth for the admin. Passwords: PBKDF2-SHA256 (Web Crypto, works on Cloudflare Workers).
// Sessions: signed JWT in an httpOnly cookie. The user record is re-read on every request, so
// role changes, deactivation and deletion take effect immediately.

export const COOKIE = 'gt_session';
const MAX_AGE = 60 * 60 * 24 * 7;

export { can, roleInfo, roleList } from '@/lib/auth-shared';
export type { Perm, Role, SessionUser } from '@/lib/auth-shared';
import type { Role, SessionUser } from '@/lib/auth-shared';

const secret = () => {
  const s = process.env.AUTH_SECRET;
  if (!s && process.env.NODE_ENV === 'production' && process.env.ALLOW_MEMORY_DB !== '1') throw new Error('AUTH_SECRET is not set');
  return new TextEncoder().encode(s || 'dev-only-secret-change-me-dev-only-secret');
};

// ---- passwords -------------------------------------------------------------------------------
const b64 = (u: Uint8Array) => btoa(String.fromCharCode(...u));
const unb64 = (s: string) => Uint8Array.from(atob(s), (c) => c.charCodeAt(0));
async function pbkdf2(pw: string, salt: Uint8Array, iter: number) {
  const key = await crypto.subtle.importKey('raw', new TextEncoder().encode(pw), 'PBKDF2', false, ['deriveBits']);
  return new Uint8Array(await crypto.subtle.deriveBits({ name: 'PBKDF2', hash: 'SHA-256', salt: salt as BufferSource, iterations: iter }, key, 256));
}
export async function hashPassword(pw: string) {
  const salt = crypto.getRandomValues(new Uint8Array(16));
  return `pbkdf2$100000$${b64(salt)}$${b64(await pbkdf2(pw, salt, 100000))}`;
}
export async function verifyPassword(pw: string, stored: string) {
  const [, iter, salt, hash] = stored.split('$');
  const got = await pbkdf2(pw, unb64(salt), Number(iter));
  const want = unb64(hash);
  let diff = got.length ^ want.length;
  for (let i = 0; i < got.length; i++) diff |= got[i] ^ (want[i] ?? 0);
  return diff === 0;
}
export const passwordProblem = (pw: string) => (pw.length < 10 ? 'Use at least 10 characters.' : !/[a-z]/i.test(pw) || !/\d/.test(pw) ? 'Include letters and numbers.' : '');

// ---- sessions --------------------------------------------------------------------------------
export async function createSession(uid: string) {
  const token = await new SignJWT({ uid }).setProtectedHeader({ alg: 'HS256' }).setIssuedAt().setExpirationTime(`${MAX_AGE}s`).sign(secret());
  (await cookies()).set(COOKIE, token, { httpOnly: true, secure: process.env.NODE_ENV === 'production', sameSite: 'lax', path: '/', maxAge: MAX_AGE });
}
export async function destroySession() { (await cookies()).delete(COOKIE); }

export async function currentUser(): Promise<SessionUser | null> {
  const token = (await cookies()).get(COOKIE)?.value;
  if (!token) return null;
  try {
    const { payload } = await jwtVerify(token, secret());
    const u = await findOne('users', { _id: String(payload.uid) });
    if (!u || u.active === false) return null;
    return { id: u._id, name: u.name, email: u.email, role: u.role };
  } catch { return null; }
}
