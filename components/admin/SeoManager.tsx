'use client';

import { useEffect, useMemo, useState } from 'react';
import Icon from '@/components/Icon';
import { api } from '@/components/admin/api';
import { Badge, Card, Count, Field, ImageField, PageTitle, useToast } from '@/components/admin/ui';
import { analyse } from '@/lib/seo-analysis';
import { encodeSeoId } from '@/lib/site-pages';

type P = { path: string; label: string; group: string; title?: string; description?: string };
type O = { title: string; description: string; canonical: string; ogImage: string; noindex: boolean; focusKeyword: string };
const empty: O = { title: '', description: '', canonical: '', ogImage: '', noindex: false, focusKeyword: '' };

/** Per-URL title, description, canonical, social image and noindex overrides for any page. */
export default function SeoManager({ pages }: { pages: P[] }) {
  const toast = useToast();
  const [overrides, setOverrides] = useState<Record<string, O>>({});
  const [q, setQ] = useState('');
  const [sel, setSel] = useState<P | null>(null);
  const [form, setForm] = useState<O>(empty);
  const [busy, setBusy] = useState(false);

  useEffect(() => { api('/api/admin/seo?size=200').then((d) => setOverrides(Object.fromEntries(d.rows.map((r: O & { _id: string }) => [r._id, r])))).catch(() => {}); }, []);
  const shown = pages.filter((p) => (p.label + p.path).toLowerCase().includes(q.toLowerCase()));
  const groups = [...new Set(shown.map((p) => p.group))];
  const pick = (p: P) => { setSel(p); setForm({ ...empty, ...overrides[encodeSeoId(p.path)] }); };
  const seo = useMemo(() => sel ? analyse({ title: sel.label, metaTitle: form.title || sel.title, metaDescription: form.description || sel.description, keyword: form.focusKeyword }) : null, [sel, form]);

  async function save() {
    if (!sel) return;
    setBusy(true);
    const id = encodeSeoId(sel.path);
    try {
      await api(`/api/admin/seo/${id}`, { method: 'PUT', body: { ...form, path: sel.path } });
      setOverrides((o) => ({ ...o, [id]: { ...form } })); toast('Saved. Use Publish site to put it live.');
    } catch (e) { toast(e instanceof Error ? e.message : 'Save failed', true); }
    setBusy(false);
  }
  async function clear() {
    if (!sel) return;
    const id = encodeSeoId(sel.path);
    try { await api(`/api/admin/seo/${id}`, { method: 'DELETE' }); } catch { /* none saved */ }
    setOverrides((o) => { const n = { ...o }; delete n[id]; return n; }); setForm(empty); toast('Override removed');
  }

  return (
    <>
      <PageTitle title="SEO manager" sub="Override the title, description, canonical URL, social image and indexing for any page. Blank fields keep the page's built-in values." />
      <div className="ad-two seo-grid">
        <Card title="Pages">
          <div className="ad-search"><Icon name="lucide:search" size={16} /><input value={q} onChange={(e) => setQ(e.target.value)} placeholder="Search pages…" aria-label="Search pages" /></div>
          <div className="seo-pages">{groups.map((g) => <div key={g}><h3>{g}</h3>{shown.filter((p) => p.group === g).map((p) => (
            <button key={p.path} className={sel?.path === p.path ? 'on' : ''} onClick={() => pick(p)}><span>{p.label}<small>{p.path}</small></span>{overrides[encodeSeoId(p.path)] && <Badge tone="blue">custom</Badge>}</button>
          ))}</div>)}</div>
        </Card>
        <Card title={sel ? sel.label : 'Select a page'}>
          {!sel ? <p className="ad-empty">Choose a page on the left to edit how it appears in Google and when shared.</p> : (
            <>
              <div className="serp"><small>gtechdigital.co.uk{sel.path}</small><b>{(form.title || sel.title || sel.label).slice(0, 60)}</b><p>{(form.description || sel.description || '').slice(0, 160)}</p></div>
              <div className="ad-form one">
                <Field label="Focus keyword"><input value={form.focusKeyword} onChange={(e) => setForm({ ...form, focusKeyword: e.target.value })} /></Field>
                <Field label="SEO title" hint={<><Count n={(form.title || sel.title || '').length} max={60} /> Built-in: {sel.title || '(automatic)'}</>}><input value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} placeholder={sel.title} /></Field>
                <Field label="Meta description" hint={<Count n={(form.description || sel.description || '').length} max={160} />}><textarea rows={3} value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} placeholder={sel.description} /></Field>
                <Field label="Canonical URL"><input value={form.canonical} onChange={(e) => setForm({ ...form, canonical: e.target.value })} placeholder={sel.path} /></Field>
                <Field label="Social sharing image" hint="Used by Facebook, LinkedIn and X. 1200×630."><ImageField value={form.ogImage} onChange={(v) => setForm({ ...form, ogImage: v })} label="Social image" /></Field>
                <label className="ad-check"><input type="checkbox" checked={form.noindex} onChange={(e) => setForm({ ...form, noindex: e.target.checked })} /> Hide this page from search engines (noindex)</label>
              </div>
              {seo && <ul className="seo-checks">{seo.checks.map((k) => <li key={k.label} className={k.ok === true ? 'ok' : k.ok === 'warn' ? 'warn' : 'bad'}>{k.label}</li>)}</ul>}
              <div className="ad-foot"><button className="ad-btn ghost" onClick={clear}>Remove override</button><button className="ad-btn" onClick={save} disabled={busy}>{busy ? 'Saving…' : 'Save'}</button></div>
            </>
          )}
        </Card>
      </div>
    </>
  );
}
