'use client';

import { useEffect, useMemo, useState } from 'react';
import Icon from '@/components/Icon';
import { api } from '@/components/admin/api';
import { Badge, Card, Count, Field, ImageField, PageTitle, useToast } from '@/components/admin/ui';
import { analyse } from '@/lib/seo-analysis';
import { autoSchemaTypes, validateCustomSchema } from '@/lib/schema';
import { encodeSeoId } from '@/lib/site-pages';

type P = { path: string; label: string; group: string; title?: string; description?: string };
type O = { title: string; description: string; canonical: string; ogImage: string; noindex: boolean; focusKeyword: string; schemaOff: boolean; schemaCustom: string };
const empty: O = { title: '', description: '', canonical: '', ogImage: '', noindex: false, focusKeyword: '', schemaOff: false, schemaCustom: '' };

/** Per-URL title, description, canonical, social image and noindex overrides for any page. */
export function SchemaBox({ path, form, setForm }: { path: string; form: { schemaOff: boolean; schemaCustom: string }; setForm: (f: never) => void }) {
  const set = setForm as unknown as (f: Record<string, unknown>) => void;
  const bad = validateCustomSchema(form.schemaCustom);
  return (
    <div className="sch">
      <h3>Structured data (schema.org)</h3>
      <p className="ad-muted small">This page automatically emits one connected graph: {autoSchemaTypes(path).map((t) => <i key={t} className="sch-t">{t}</i>)}</p>
      <label className="ad-check"><input type="checkbox" checked={form.schemaOff} onChange={(e) => set({ ...form, schemaOff: e.target.checked })} /> Turn off the automatic schema for this page</label>
      <label className="ad-field"><span>Custom JSON-LD (optional)</span>
        <textarea rows={6} value={form.schemaCustom} onChange={(e) => set({ ...form, schemaCustom: e.target.value })} placeholder={'{\n  "@context": "https://schema.org",\n  "@type": "HowTo",\n  "name": "…"\n}'} spellCheck={false} style={{ fontFamily: 'ui-monospace,Menlo,monospace', fontSize: 13 }} />
        <small className={bad ? 'sch-bad' : ''}>{bad ?? (form.schemaCustom.trim() ? 'Valid JSON-LD. It is added alongside the automatic schema.' : 'Added alongside the automatic schema. Use it for HowTo, Event, Product, Review or anything the template does not cover.')}</small>
      </label>
    </div>
  );
}

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
              <SchemaBox path={sel.path} form={form} setForm={setForm} />
              {seo && <ul className="seo-checks">{seo.checks.map((k) => <li key={k.label} className={k.ok === true ? 'ok' : k.ok === 'warn' ? 'warn' : 'bad'}>{k.label}</li>)}</ul>}
              <div className="ad-foot"><button className="ad-btn ghost" onClick={clear}>Remove override</button><button className="ad-btn" onClick={save} disabled={busy}>{busy ? 'Saving…' : 'Save'}</button></div>
            </>
          )}
        </Card>
      </div>
    </>
  );
}
