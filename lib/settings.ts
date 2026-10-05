import { forget, memo } from '@/lib/cache';
import { findOne, upsert } from '@/lib/store';
import { site } from '@/lib/data';

// Site-wide settings (one document, id "site"). The SMTP password is encrypted at rest with a key
// derived from AUTH_SECRET and is never sent back to the browser.

export type Settings = {
  general: { siteName: string; tagline: string; siteUrl: string };
  contact: { email: string; phone: string; address: string; hours: string };
  socials: { name: string; url: string }[];
  tracking: { gtmId: string; ga4Id: string; metaPixelId: string };
  seo: { titleSuffix: string; defaultDescription: string; ogImage: string };
  smtp: { host: string; port: number; secure: boolean; user: string; pass: string; fromName: string; fromEmail: string; notifyTo: string; autoReply: boolean };
  publish: { deployHook: string };
};

export const defaults: Settings = {
  general: { siteName: site.name, tagline: site.tagline, siteUrl: 'https://www.gtechdigital.co.uk' },
  contact: { email: site.email, phone: site.phone, address: '', hours: 'Mon to Fri, 9am to 6pm' },
  socials: site.socials.map((s) => ({ name: s.name, url: s.url })),
  tracking: { gtmId: '', ga4Id: '', metaPixelId: '' },
  seo: { titleSuffix: ' | GTech Digital', defaultDescription: '', ogImage: '' },
  smtp: { host: '', port: 587, secure: false, user: '', pass: '', fromName: site.name, fromEmail: '', notifyTo: '', autoReply: true },
  publish: { deployHook: '' },
};

const keyPromise = () => {
  const s = process.env.AUTH_SECRET || 'dev-only-secret-change-me-dev-only-secret';
  return crypto.subtle.digest('SHA-256', new TextEncoder().encode('smtp:' + s)).then((k) => crypto.subtle.importKey('raw', k, 'AES-GCM', false, ['encrypt', 'decrypt']));
};
const b64 = (u: Uint8Array) => btoa(String.fromCharCode(...u));
const unb64 = (s: string) => Uint8Array.from(atob(s), (c) => c.charCodeAt(0));

export async function encrypt(text: string) {
  if (!text) return '';
  const iv = crypto.getRandomValues(new Uint8Array(12));
  const ct = new Uint8Array(await crypto.subtle.encrypt({ name: 'AES-GCM', iv }, await keyPromise(), new TextEncoder().encode(text)));
  return `enc:${b64(iv)}:${b64(ct)}`;
}
export async function decrypt(v: string) {
  if (!v?.startsWith('enc:')) return v ?? '';
  const [, iv, ct] = v.split(':');
  try { return new TextDecoder().decode(await crypto.subtle.decrypt({ name: 'AES-GCM', iv: unb64(iv) as BufferSource }, await keyPromise(), unb64(ct) as BufferSource)); } catch { return ''; }
}

function merge<T>(base: T, over: unknown): T {
  if (Array.isArray(base) || typeof base !== 'object' || base === null) return (over ?? base) as T;
  const o = (over ?? {}) as Record<string, unknown>;
  return Object.fromEntries(Object.entries(base as Record<string, unknown>).map(([k, v]) => [k, k in o ? merge(v, o[k]) : v])) as T;
}

/** Full settings, including the encrypted SMTP password (server use only). */
export async function getSettings(): Promise<Settings> {
  try {
    const doc = await memo('settings', 30_000, () => findOne('settings', { _id: 'site' }));
    return merge(defaults, doc);
  } catch { return defaults; }
}

/** Safe copy for public pages and the admin UI: no SMTP password, no deploy hook value. */
export function redact(s: Settings) {
  return { ...s, smtp: { ...s.smtp, pass: '', hasPass: !!s.smtp.pass }, publish: { deployHook: '', hasHook: !!s.publish.deployHook } };
}
export const getPublicSettings = async () => redact(await getSettings());

export async function saveSettings(input: Partial<Settings> & { smtp?: Partial<Settings['smtp']> & { hasPass?: boolean }; publish?: { deployHook?: string; hasHook?: boolean } }) {
  const cur = await getSettings();
  const next = merge(cur, input);
  // blank password / hook in the form means "keep the stored one"
  next.smtp.pass = input.smtp?.pass ? await encrypt(input.smtp.pass) : cur.smtp.pass;
  next.publish.deployHook = input.publish?.deployHook ? input.publish.deployHook : cur.publish.deployHook;
  delete (next.smtp as Record<string, unknown>).hasPass;
  delete (next.publish as Record<string, unknown>).hasHook;
  await upsert('settings', 'site', next);
  forget('settings');
}
