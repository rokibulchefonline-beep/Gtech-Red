'use client';

import { useEffect, useState } from 'react';
import Icon from '@/components/Icon';
import { api } from '@/components/admin/api';
import { Card, Field, PageTitle, useToast } from '@/components/admin/ui';
import type { Settings } from '@/lib/settings';

type S = Omit<Settings, 'smtp' | 'publish'> & { smtp: Settings['smtp'] & { hasPass?: boolean }; publish: { deployHook: string; hasHook?: boolean } };
const tabs = [['general', 'General'], ['contact', 'Contact and social'], ['tracking', 'Tracking'], ['seo', 'SEO defaults'], ['email', 'Email (SMTP)'], ['publish', 'Publishing']] as const;

export default function SettingsForm() {
  const toast = useToast();
  const [s, setS] = useState<S | null>(null);
  const [tab, setTab] = useState<(typeof tabs)[number][0]>('general');
  const [busy, setBusy] = useState(false);
  const [testTo, setTestTo] = useState('');
  const [testing, setTesting] = useState(false);

  useEffect(() => { api('/api/admin/settings').then((d) => setS(d.settings)).catch((e) => toast(e.message, true)); }, [toast]);
  if (!s) return <p className="ad-empty">Loading…</p>;
  const up = <K extends keyof S>(k: K, patch: Partial<S[K]>) => setS({ ...s, [k]: { ...(s[k] as object), ...(patch as object) } } as S);

  async function save() {
    setBusy(true);
    try { const d = await api('/api/admin/settings', { method: 'PUT', body: s }); setS(d.settings); toast('Settings saved'); } catch (e) { toast(e instanceof Error ? e.message : 'Save failed', true); }
    setBusy(false);
  }
  async function test() {
    setTesting(true);
    try { await save(); await api('/api/admin/smtp-test', { body: { to: testTo } }); toast('Test email sent. Check the inbox.'); } catch (e) { toast(e instanceof Error ? e.message : 'Could not send', true); }
    setTesting(false);
  }

  return (
    <>
      <PageTitle title="Settings" sub="Site details, tracking, email and publishing." actions={<button className="ad-btn" onClick={save} disabled={busy}>{busy ? 'Saving…' : 'Save settings'}</button>} />
      <div className="ed-tabs inline">{tabs.map(([k, l]) => <button key={k} className={tab === k ? 'on' : ''} onClick={() => setTab(k)}>{l}</button>)}</div>

      {tab === 'general' && <Card title="General"><div className="ad-form">
        <Field label="Site name"><input value={s.general.siteName} onChange={(e) => up('general', { siteName: e.target.value })} /></Field>
        <Field label="Site URL" hint="Used in canonical links and the sitemap."><input value={s.general.siteUrl} onChange={(e) => up('general', { siteUrl: e.target.value })} /></Field>
        <Field label="Tagline" wide><input value={s.general.tagline} onChange={(e) => up('general', { tagline: e.target.value })} /></Field>
      </div></Card>}

      {tab === 'contact' && <>
        <Card title="Contact details"><div className="ad-form">
          <Field label="Public email" hint="Shown in the footer and contact page. Also receives lead alerts unless set below."><input value={s.contact.email} onChange={(e) => up('contact', { email: e.target.value })} /></Field>
          <Field label="Phone"><input value={s.contact.phone} onChange={(e) => up('contact', { phone: e.target.value })} /></Field>
          <Field label="Office hours"><input value={s.contact.hours} onChange={(e) => up('contact', { hours: e.target.value })} /></Field>
          <Field label="Address"><input value={s.contact.address} onChange={(e) => up('contact', { address: e.target.value })} /></Field>
        </div></Card>
        <Card title="Social profiles"><div className="ad-form">{s.socials.map((x, i) => (
          <Field key={x.name} label={x.name}><input value={x.url} placeholder="https://" onChange={(e) => setS({ ...s, socials: s.socials.map((y, n) => (n === i ? { ...y, url: e.target.value } : y)) })} /></Field>
        ))}</div></Card>
      </>}

      {tab === 'tracking' && <Card title="Tracking IDs"><p className="ad-muted small">Scripts load only after a visitor accepts cookies (Consent Mode v2 is already set up).</p><div className="ad-form">
        <Field label="Google Tag Manager ID" hint="Looks like GTM-XXXXXXX"><input value={s.tracking.gtmId} onChange={(e) => up('tracking', { gtmId: e.target.value.trim() })} placeholder="GTM-" /></Field>
        <Field label="Google Analytics 4 ID" hint="Looks like G-XXXXXXXXXX. Skip if GA4 is inside Tag Manager."><input value={s.tracking.ga4Id} onChange={(e) => up('tracking', { ga4Id: e.target.value.trim() })} placeholder="G-" /></Field>
        <Field label="Meta Pixel ID" hint="Numbers only."><input value={s.tracking.metaPixelId} onChange={(e) => up('tracking', { metaPixelId: e.target.value.trim() })} /></Field>
      </div></Card>}

      {tab === 'seo' && <Card title="SEO defaults"><div className="ad-form one">
        <Field label="Default meta description" hint="Used for pages that do not set their own."><textarea rows={3} value={s.seo.defaultDescription} onChange={(e) => up('seo', { defaultDescription: e.target.value })} /></Field>
        <Field label="Default social image" hint="Fallback when a page has no social image."><input value={s.seo.ogImage} onChange={(e) => up('seo', { ogImage: e.target.value })} placeholder="/og-default.png" /></Field>
      </div></Card>}

      {tab === 'email' && <>
        <Card title="SMTP account" ><p className="ad-muted small">Used for new-lead alerts, auto-replies to enquirers and test emails. Use an app password where your provider offers one (Google Workspace, Microsoft 365, Brevo, Mailgun, SendGrid, Amazon SES).</p><div className="ad-form">
          <Field label="SMTP host"><input value={s.smtp.host} onChange={(e) => up('smtp', { host: e.target.value })} placeholder="smtp.example.com" /></Field>
          <Field label="Port" hint="587 (STARTTLS) or 465 (SSL)"><input type="number" value={s.smtp.port} onChange={(e) => up('smtp', { port: Number(e.target.value) })} /></Field>
          <Field label="Username"><input value={s.smtp.user} onChange={(e) => up('smtp', { user: e.target.value })} autoComplete="off" /></Field>
          <Field label="Password" hint={s.smtp.hasPass ? 'A password is saved. Leave blank to keep it.' : 'Stored encrypted.'}><input type="password" value={s.smtp.pass} onChange={(e) => up('smtp', { pass: e.target.value })} autoComplete="new-password" placeholder={s.smtp.hasPass ? '••••••••' : ''} /></Field>
          <label className="ad-check wide"><input type="checkbox" checked={s.smtp.secure} onChange={(e) => up('smtp', { secure: e.target.checked })} /> Use SSL/TLS from the start (turn on for port 465)</label>
          <Field label="From name"><input value={s.smtp.fromName} onChange={(e) => up('smtp', { fromName: e.target.value })} /></Field>
          <Field label="From email"><input type="email" value={s.smtp.fromEmail} onChange={(e) => up('smtp', { fromEmail: e.target.value })} placeholder="hello@yourdomain.com" /></Field>
          <Field label="Send lead alerts to" hint="Comma-separate several addresses. Blank uses the public email."><input value={s.smtp.notifyTo} onChange={(e) => up('smtp', { notifyTo: e.target.value })} /></Field>
          <label className="ad-check wide"><input type="checkbox" checked={s.smtp.autoReply} onChange={(e) => up('smtp', { autoReply: e.target.checked })} /> Send an automatic confirmation email to people who enquire</label>
        </div></Card>
        <Card title="Send a test email"><div className="ad-inline">
          <input type="email" value={testTo} onChange={(e) => setTestTo(e.target.value)} placeholder="Send to (blank = my email)" aria-label="Test recipient" />
          <button className="ad-btn" onClick={test} disabled={testing}><Icon name="lucide:send" size={15} /> {testing ? 'Sending…' : 'Save and send test'}</button>
        </div></Card>
      </>}

      {tab === 'publish' && <Card title="Publishing"><p className="ad-muted small">The public site is pre-rendered for speed, so content changes go live after a rebuild. Paste a deploy hook so <b>Publish site</b> can start one with a single click. In Cloudflare: Workers &amp; Pages → your project → Settings → Builds → Deploy hooks.</p>
        <Field label="Deploy hook URL" hint={s.publish.hasHook ? 'A hook is saved. Paste a new URL to replace it.' : 'Must start with https://'}><input type="password" value={s.publish.deployHook} onChange={(e) => up('publish', { deployHook: e.target.value })} placeholder={s.publish.hasHook ? '••••••••••••' : 'https://api.cloudflare.com/…'} autoComplete="off" /></Field>
      </Card>}
    </>
  );
}
