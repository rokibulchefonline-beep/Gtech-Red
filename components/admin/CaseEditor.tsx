'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useEffect, useMemo, useState } from 'react';
import Icon from '@/components/Icon';
import { api } from '@/components/admin/api';
import { Card, Count, Field, ImageField, ListField, useToast } from '@/components/admin/ui';
import { services } from '@/lib/data';
import { analyse } from '@/lib/seo-analysis';

type Metric = { value: string; label: string };
type Case = {
  title: string; slug: string; client: string; industry: string; duration: string; website: string; excerpt: string; image: string; imageAlt: string; logo: string;
  services: string[]; metrics: Metric[]; challenge: string; solution: string; results: string[]; quote: { text: string; name: string; role: string };
  status: 'draft' | 'published'; order: number; metaTitle: string; metaDescription: string; focusKeyword: string;
};
const blank: Case = { title: '', slug: '', client: '', industry: '', duration: '', website: '', excerpt: '', image: '', imageAlt: '', logo: '', services: [], metrics: [{ value: '', label: '' }, { value: '', label: '' }, { value: '', label: '' }], challenge: '', solution: '', results: [], quote: { text: '', name: '', role: '' }, status: 'draft', order: 100, metaTitle: '', metaDescription: '', focusKeyword: '' };
const slugify = (s: string) => s.toLowerCase().replace(/&/g, ' and ').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '').slice(0, 80);
const examples = ['organic traffic', 'online revenue', 'qualified leads', 'ROAS', 'sales', 'bookings', 'cost per lead', 'conversion rate'];

export default function CaseEditor({ id }: { id: string }) {
  const router = useRouter();
  const toast = useToast();
  const isNew = id === 'new';
  const [c, setC] = useState<Case>(blank);
  const [loaded, setLoaded] = useState(isNew);
  const [saving, setSaving] = useState(false);
  const [dirty, setDirty] = useState(false);
  const [slugTouched, setSlugTouched] = useState(!isNew);

  useEffect(() => {
    if (isNew) return;
    api(`/api/admin/case-studies/${id}`).then((d) => { setC({ ...blank, ...d.doc, quote: { ...blank.quote, ...d.doc.quote }, metrics: d.doc.metrics?.length ? d.doc.metrics : blank.metrics }); setLoaded(true); }).catch((e) => { toast(e.message, true); setLoaded(true); });
  }, [id, isNew, toast]);
  useEffect(() => {
    const warn = (e: BeforeUnloadEvent) => { if (dirty) e.preventDefault(); };
    window.addEventListener('beforeunload', warn); return () => window.removeEventListener('beforeunload', warn);
  }, [dirty]);

  const set = <K extends keyof Case>(k: K, v: Case[K]) => { setC((x) => ({ ...x, [k]: v })); setDirty(true); };
  const seo = useMemo(() => analyse({ title: c.title, metaTitle: c.metaTitle, metaDescription: c.metaDescription || c.excerpt, slug: c.slug, keyword: c.focusKeyword }), [c]);
  const toggle = (slug: string) => set('services', c.services.includes(slug) ? c.services.filter((s) => s !== slug) : [...c.services, slug]);

  async function save(status?: Case['status']) {
    const body = { ...c, status: status ?? c.status };
    setSaving(true);
    try {
      const d = isNew ? await api('/api/admin/case-studies', { body }) : await api(`/api/admin/case-studies/${id}`, { method: 'PUT', body });
      setDirty(false); setC({ ...blank, ...d.doc }); toast('Saved');
      if (isNew) router.replace(`/admin/case-studies/${d.doc._id}`);
    } catch (e) { toast(e instanceof Error ? e.message : 'Save failed', true); }
    setSaving(false);
  }
  if (!loaded) return <p className="ad-empty">Loading…</p>;

  return (
    <div className="ed">
      <div className="ed-bar">
        <Link href="/admin/case-studies" className="ad-btn ghost small"><Icon name="lucide:arrow-left" size={15} /> Case studies</Link>
        <span className="ed-state">{saving ? 'Saving…' : dirty ? 'Unsaved changes' : 'All changes saved'}</span>
        <div className="ed-bar-r">
          <select value={c.status} onChange={(e) => set('status', e.target.value as Case['status'])} aria-label="Status"><option value="draft">Draft</option><option value="published">Published</option></select>
          <button className="ad-btn ghost small" onClick={() => save()} disabled={saving}>Save</button>
          {c.status !== 'published' && <button className="ad-btn small" onClick={() => save('published')} disabled={saving}>Publish</button>}
        </div>
      </div>

      <div className="ad-two wide-left">
        <div className="ad-stack">
          <Card title="Overview">
            <div className="ad-form">
              <Field label="Case study title" wide><input value={c.title} onChange={(e) => { const v = e.target.value; setC((x) => ({ ...x, title: v, slug: slugTouched ? x.slug : slugify(v) })); setDirty(true); }} /></Field>
              <Field label="URL slug"><input value={c.slug} onChange={(e) => { setSlugTouched(true); set('slug', slugify(e.target.value)); }} /></Field>
              <Field label="Client name" hint="Shown in headings. Defaults to the title."><input value={c.client} onChange={(e) => set('client', e.target.value)} /></Field>
              <Field label="Industry"><input value={c.industry} onChange={(e) => set('industry', e.target.value)} placeholder="e.g. Hospitality" /></Field>
              <Field label="Duration"><input value={c.duration} onChange={(e) => set('duration', e.target.value)} placeholder="e.g. 12 months" /></Field>
              <Field label="Client website"><input value={c.website} onChange={(e) => set('website', e.target.value)} placeholder="https://" /></Field>
              <Field label="Display order" hint="Lower numbers appear first."><input type="number" value={c.order} onChange={(e) => set('order', Number(e.target.value))} /></Field>
              <Field label="Short summary" wide hint={<><Count n={c.excerpt.length} max={160} /> One line shown under the heading.</>}><textarea rows={2} value={c.excerpt} onChange={(e) => set('excerpt', e.target.value)} /></Field>
            </div>
          </Card>

          <Card title="Headline numbers">
            <p className="ad-muted small">Up to four numbers. The first three show when someone hovers the card, and all appear on the case study page. Examples: {examples.join(', ')}.</p>
            {c.metrics.map((m, i) => (
              <div key={i} className="ad-metric-row">
                <input value={m.value} onChange={(e) => set('metrics', c.metrics.map((x, n) => (n === i ? { ...x, value: e.target.value } : x)))} placeholder="+212%" aria-label="Value" />
                <input value={m.label} onChange={(e) => set('metrics', c.metrics.map((x, n) => (n === i ? { ...x, label: e.target.value } : x)))} placeholder="organic traffic" aria-label="Label" list="metric-labels" />
                <button type="button" className="ad-ico" aria-label="Remove" onClick={() => set('metrics', c.metrics.filter((_, n) => n !== i))}><Icon name="lucide:x" size={16} /></button>
              </div>
            ))}
            <datalist id="metric-labels">{examples.map((e) => <option key={e} value={e} />)}</datalist>
            {c.metrics.length < 4 && <button type="button" className="ad-btn small" onClick={() => set('metrics', [...c.metrics, { value: '', label: '' }])}><Icon name="lucide:plus" size={15} /> Add number</button>}
          </Card>

          <Card title="The story">
            <div className="ad-form one">
              <Field label="Challenge"><textarea rows={5} value={c.challenge} onChange={(e) => set('challenge', e.target.value)} /></Field>
              <Field label="Solution"><textarea rows={5} value={c.solution} onChange={(e) => set('solution', e.target.value)} /></Field>
              <Field label="Results (bullet points)"><ListField value={c.results} onChange={(v) => set('results', v)} placeholder="e.g. Organic traffic up 212% in 12 months" max={10} /></Field>
            </div>
          </Card>

          <Card title="Client quote">
            <div className="ad-form">
              <Field label="Quote" wide><textarea rows={3} value={c.quote.text} onChange={(e) => set('quote', { ...c.quote, text: e.target.value })} /></Field>
              <Field label="Name"><input value={c.quote.name} onChange={(e) => set('quote', { ...c.quote, name: e.target.value })} /></Field>
              <Field label="Role and company"><input value={c.quote.role} onChange={(e) => set('quote', { ...c.quote, role: e.target.value })} /></Field>
            </div>
          </Card>
        </div>

        <div className="ad-stack">
          <Card title="Banner and logo">
            <Field label="Banner image" hint="Used on the card and the page hero. No text on the image."><ImageField value={c.image} onChange={(v) => set('image', v)} /></Field>
            <Field label="Image alt text"><input value={c.imageAlt} onChange={(e) => set('imageAlt', e.target.value)} /></Field>
            <Field label="Client logo (optional)"><ImageField value={c.logo} onChange={(v) => set('logo', v)} label="Client logo" /></Field>
          </Card>
          <Card title="Services delivered">
            <div className="ad-svc">{services.map((g) => <div key={g.slug}><b>{g.title}</b>{g.items.map((i) => <label key={i.slug} className="ad-check"><input type="checkbox" checked={c.services.includes(i.slug)} onChange={() => toggle(i.slug)} /> {i.name}</label>)}</div>)}</div>
            <p className="ad-muted small">Tick a service to show this case study on that service page.</p>
          </Card>
          <Card title={`SEO (score ${seo.score})`}>
            <Field label="Focus keyword"><input value={c.focusKeyword} onChange={(e) => set('focusKeyword', e.target.value)} placeholder="e.g. restaurant ordering seo case study" /></Field>
            <Field label="SEO title" hint={<><Count n={(c.metaTitle || c.title).length} max={60} /> Default: “{(c.client || c.title) || 'Client'} Case Study: numbers | GTech Digital”.</>}><input value={c.metaTitle} onChange={(e) => set('metaTitle', e.target.value)} /></Field>
            <Field label="Meta description" hint={<Count n={(c.metaDescription || c.excerpt).length} max={160} />}><textarea rows={3} value={c.metaDescription} onChange={(e) => set('metaDescription', e.target.value)} /></Field>
            <ul className="seo-checks">{seo.checks.map((k) => <li key={k.label} className={k.ok === true ? 'ok' : k.ok === 'warn' ? 'warn' : 'bad'}>{k.label}</li>)}</ul>
          </Card>
        </div>
      </div>
    </div>
  );
}
