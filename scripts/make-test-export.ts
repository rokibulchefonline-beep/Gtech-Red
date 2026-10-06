// Test only: builds a fake MongoDB export (same format as mongoexport --jsonArray) from the demo content, to try the
// Laravel importer without touching the live database:  npx tsx scripts/make-test-export.ts ./test-export
import { mkdirSync, writeFileSync } from 'fs';
import { webcrypto } from 'crypto';
import { seedAll } from '@/lib/seed';
import { insert, list, upsert } from '@/lib/store';

const out = process.argv[2];
const ej = (v: unknown): unknown => v instanceof Date ? { $date: v.toISOString() } : Array.isArray(v) ? v.map(ej) : v && typeof v === 'object' ? Object.fromEntries(Object.entries(v).map(([k, x]) => [k, ej(x)])) : v;
const b64 = (u: Uint8Array) => Buffer.from(u).toString('base64');

async function main() {
  await seedAll();
  // a user with the old PBKDF2 hash (password: OldPassword123)
  const salt = webcrypto.getRandomValues(new Uint8Array(16));
  const key = await webcrypto.subtle.importKey('raw', new TextEncoder().encode('OldPassword123'), 'PBKDF2', false, ['deriveBits']);
  const bits = new Uint8Array(await webcrypto.subtle.deriveBits({ name: 'PBKDF2', hash: 'SHA-256', salt, iterations: 100000 }, key, 256));
  await insert('users', { name: 'Rokibul', email: 'owner@example.com', role: 'super_admin', active: true, passwordHash: `pbkdf2$100000$${b64(salt)}$${b64(bits)}` });
  await insert('leads', { name: 'Jane Lead', business: 'Acme', email: 'jane@example.com', phone: '+44 7000', service: 'Local SEO', budget: '£1k', message: 'Hi', source: 'contact', status: 'qualified', notes: 'Call back', assignee: 'Sam' });
  await insert('subscribers', { email: 'news@example.com', source: 'blog' });
  await upsert('page_content', 'service~local-seo', { kind: 'service', slug: 'local-seo', metaTitle: '', metaDescription: '', focusKeyword: 'local seo',
    hero: { h1: 'Local SEO [[That Fills Your Diary]]', lead: 'Edited <b>lead</b>' }, sections: { }, faqs: [] });
  await upsert('seo', 'about', { path: '/about', title: 'About us | GTech', description: 'About', noindex: false, schemaOff: false, schemaCustom: '' });
  const png = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';
  await insert('media', { _id: '66f0aa11bb22cc33dd44ee55', name: 'dot.png', type: 'image/png', size: 68, data: png, by: 'owner@example.com' });
  // SMTP password encrypted exactly like lib/settings.ts (AES-GCM, key = sha256("smtp:" + AUTH_SECRET))
  const k = await webcrypto.subtle.importKey('raw', await webcrypto.subtle.digest('SHA-256', new TextEncoder().encode('smtp:old-secret-123')), 'AES-GCM', false, ['encrypt']);
  const iv = webcrypto.getRandomValues(new Uint8Array(12));
  const ct = new Uint8Array(await webcrypto.subtle.encrypt({ name: 'AES-GCM', iv }, k, new TextEncoder().encode('smtp-pass-OK')));
  await upsert('settings', 'site', { general: { siteName: 'GTech Digital', tagline: 't', siteUrl: 'https://x' }, contact: { email: 'hello@x.com', phone: '+44 1', address: '', hours: '9-5' },
    socials: [{ name: 'LinkedIn', url: 'https://linkedin.com/x' }], smtp: { host: 'smtp.x.com', port: 587, secure: false, user: 'u', pass: `enc:${b64(iv)}:${b64(ct)}`, fromName: 'G', fromEmail: 'g@x.com', notifyTo: 'n@x.com', autoReply: true },
    publish: { deployHook: 'https://hook' } });
  mkdirSync(out, { recursive: true });
  for (const c of ['users', 'categories', 'posts', 'case_studies', 'partners', 'clients', 'page_content', 'seo', 'leads', 'subscribers', 'media', 'settings']) {
    const rows = await list(c, { limit: 1000 });
    writeFileSync(`${out}/${c}.json`, JSON.stringify(ej(rows.map((r) => ({ ...r, _id: /^[a-f0-9]{24}$/.test(String(r._id)) ? { $oid: r._id } : r._id })))));
    console.log(c, rows.length);
  }
}
main();
