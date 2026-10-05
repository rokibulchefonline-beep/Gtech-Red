'use client';

import Link from 'next/link';
import { useEffect, useMemo, useState } from 'react';
import Icon from '@/components/Icon';
import { api } from '@/components/admin/api';
import { Card, Count, Field, ListField, useToast } from '@/components/admin/ui';
import { analyse } from '@/lib/seo-analysis';

export type EditSection = { id: string; type: string; nav?: string; heading?: string; paras?: string[]; bullets?: string[]; text?: string };
export type PageBase = { kind: 'service' | 'industry'; slug: string; name: string; metaTitle: string; metaDescription: string; hero: { keyword: string; lead: string; points: string[] }; sections: EditSection[]; faqs: { q: string; a: string }[] };

const same = (a: unknown, b: unknown) => JSON.stringify(a) === JSON.stringify(b);

/** Edits the copy of a service or industry page. Only fields that differ from the built-in text are saved as overrides. */
export default function PageEditor({ base }: { base: PageBase }) {
  const toast = useToast();
  const key = `${base.kind}~${base.slug}`;
  const [tab, setTab] = useState<'content' | 'faqs' | 'seo'>('content');
  const [hero, setHero] = useState(base.hero);
  const [sections, setSections] = useState(base.sections);
  const [faqs, setFaqs] = useState(base.faqs);
  const [meta, setMeta] = useState({ metaTitle: base.metaTitle, metaDescription: base.metaDescription, focusKeyword: '' });
  const [hasOverride, setHasOverride] = useState(false);
  const [busy, setBusy] = useState(false);
  const [dirty, setDirty] = useState(false);

  useEffect(() => {
    api(`/api/admin/page-content/${key}`).then(({ doc }) => {
      if (!doc) return;
      setHasOverride(true);
      setHero((h) => ({ keyword: doc.hero?.keyword || h.keyword, lead: doc.hero?.lead || h.lead, points: doc.hero?.points?.length ? doc.hero.points : h.points }));
      setSections((ss) => ss.map((s) => { const o = doc.sections?.[s.id]; return o ? { ...s, ...o } : s; }));
      if (doc.faqs?.length) setFaqs(doc.faqs);
      setMeta({ metaTitle: doc.metaTitle || base.metaTitle, metaDescription: doc.metaDescription || base.metaDescription, focusKeyword: doc.focusKeyword || '' });
    }).catch(() => {});
  }, [key, base.metaTitle, base.metaDescription]);
  useEffect(() => {
    const warn = (e: BeforeUnloadEvent) => { if (dirty) e.preventDefault(); };
    window.addEventListener('beforeunload', warn); return () => window.removeEventListener('beforeunload', warn);
  }, [dirty]);

  const upd = (id: string, patch: Partial<EditSection>) => { setSections((ss) => ss.map((s) => (s.id === id ? { ...s, ...patch } : s))); setDirty(true); };
  const seo = useMemo(() => analyse({ title: base.name, metaTitle: meta.metaTitle, metaDescription: meta.metaDescription, keyword: meta.focusKeyword }), [base.name, meta]);

  async function save() {
    // keep only what differs from the built-in copy
    const changed: Record<string, Partial<EditSection>> = {};
    for (const s of sections) {
      const b = base.sections.find((x) => x.id === s.id)!;
      const d: Partial<EditSection> = {};
      for (const f of ['heading', 'paras', 'bullets', 'text'] as const) if (s[f] !== undefined && !same(s[f], b[f])) (d as Record<string, unknown>)[f] = s[f];
      if (Object.keys(d).length) changed[s.id] = d;
    }
    setBusy(true);
    try {
      await api(`/api/admin/page-content/${key}`, { method: 'PUT', body: {
        kind: base.kind, slug: base.slug, metaTitle: meta.metaTitle === base.metaTitle ? '' : meta.metaTitle, metaDescription: meta.metaDescription === base.metaDescription ? '' : meta.metaDescription, focusKeyword: meta.focusKeyword,
        hero: { keyword: hero.keyword === base.hero.keyword ? '' : hero.keyword, lead: hero.lead === base.hero.lead ? '' : hero.lead, points: same(hero.points, base.hero.points) ? [] : hero.points },
        sections: changed, faqs: same(faqs, base.faqs) ? [] : faqs,
      } });
      setHasOverride(true); setDirty(false); toast('Saved. Use Publish site to put it live.');
    } catch (e) { toast(e instanceof Error ? e.message : 'Save failed', true); }
    setBusy(false);
  }
  async function reset() {
    if (!window.confirm('Remove all edits and go back to the original text for this page?')) return;
    try { await api(`/api/admin/page-content/${key}`, { method: 'DELETE' }); toast('Reset to the original text'); window.location.reload(); } catch (e) { toast(e instanceof Error ? e.message : 'Failed', true); }
  }
  const path = `/${base.kind === 'service' ? 'services' : 'industries'}/${base.slug}`;

  return (
    <div className="ed">
      <div className="ed-bar">
        <Link href="/admin/pages" className="ad-btn ghost small"><Icon name="lucide:arrow-left" size={15} /> Pages</Link>
        <span className="ed-state">{busy ? 'Saving…' : dirty ? 'Unsaved changes' : hasOverride ? 'Edited' : 'Original text'}</span>
        <div className="ed-bar-r">
          <a className="ad-btn ghost small" href={path} target="_blank" rel="noopener noreferrer"><Icon name="lucide:external-link" size={15} /> View</a>
          {hasOverride && <button className="ad-btn ghost small" onClick={reset}>Reset</button>}
          <button className="ad-btn small" onClick={save} disabled={busy}>Save</button>
        </div>
      </div>
      <h1 className="ed-h1">{base.name} <small>{path}</small></h1>
      <div className="ed-tabs inline">{(['content', 'faqs', 'seo'] as const).map((t) => <button key={t} className={tab === t ? 'on' : ''} onClick={() => setTab(t)}>{t === 'faqs' ? 'FAQs' : t === 'seo' ? `SEO (${seo.score})` : 'Content'}</button>)}</div>

      {tab === 'content' && (
        <div className="ad-stack">
          <Card title="Hero">
            <div className="ad-form one">
              <Field label="Main keyword" hint={`The H1 reads “${hero.keyword || base.name} Services of GTech Digital”.`}><input value={hero.keyword} onChange={(e) => { setHero({ ...hero, keyword: e.target.value }); setDirty(true); }} /></Field>
              <Field label="Opening description" hint={<><Count n={hero.lead.length} max={260} /> Write it as a direct answer that names GTech Digital.</>}><textarea rows={3} value={hero.lead} onChange={(e) => { setHero({ ...hero, lead: e.target.value }); setDirty(true); }} /></Field>
              <Field label="Highlights (3 short points)"><ListField value={hero.points} onChange={(v) => { setHero({ ...hero, points: v }); setDirty(true); }} max={5} /></Field>
            </div>
          </Card>
          {sections.filter((s) => s.heading !== undefined || s.paras || s.bullets || s.text !== undefined).map((s) => (
            <Card key={s.id} title={`${s.nav || s.type}: ${s.id}`}>
              <div className="ad-form one">
                {s.heading !== undefined && <Field label="Heading"><input value={s.heading} onChange={(e) => upd(s.id, { heading: e.target.value })} /></Field>}
                {s.text !== undefined && <Field label="Intro text"><textarea rows={2} value={s.text} onChange={(e) => upd(s.id, { text: e.target.value })} /></Field>}
                {s.paras && <Field label="Paragraphs">{s.paras.map((p, i) => <textarea key={i} rows={4} value={p} onChange={(e) => upd(s.id, { paras: s.paras!.map((x, n) => (n === i ? e.target.value : x)) })} />)}</Field>}
                {s.bullets && <Field label="Bullet points"><ListField value={s.bullets} onChange={(v) => upd(s.id, { bullets: v })} /></Field>}
              </div>
            </Card>
          ))}
          <p className="ad-muted small">Card grids, steps, pricing tables and images are part of the page design and are changed in code.</p>
        </div>
      )}

      {tab === 'faqs' && (
        <Card title="Frequently asked questions" actions={<button className="ad-btn small" onClick={() => { setFaqs([...faqs, { q: '', a: '' }]); setDirty(true); }}><Icon name="lucide:plus" size={15} /> Add question</button>}>
          <p className="ad-muted small">These also feed the FAQ rich-result markup. Keep answers short and direct (40 to 60 words) so search engines and AI assistants can quote them.</p>
          {faqs.map((f, i) => (
            <div key={i} className="ad-faq">
              <input value={f.q} placeholder="Question" onChange={(e) => { setFaqs(faqs.map((x, n) => (n === i ? { ...x, q: e.target.value } : x))); setDirty(true); }} />
              <textarea rows={3} value={f.a} placeholder="Answer" onChange={(e) => { setFaqs(faqs.map((x, n) => (n === i ? { ...x, a: e.target.value } : x))); setDirty(true); }} />
              <button className="ad-ico danger" aria-label="Remove question" onClick={() => { setFaqs(faqs.filter((_, n) => n !== i)); setDirty(true); }}><Icon name="lucide:trash-2" size={16} /></button>
            </div>
          ))}
        </Card>
      )}

      {tab === 'seo' && (
        <Card title="Search appearance">
          <div className="serp"><small>gtechdigital.co.uk{path}</small><b>{meta.metaTitle.slice(0, 60)}</b><p>{meta.metaDescription.slice(0, 160)}</p></div>
          <div className="ad-form one">
            <Field label="Focus keyword"><input value={meta.focusKeyword} onChange={(e) => { setMeta({ ...meta, focusKeyword: e.target.value }); setDirty(true); }} /></Field>
            <Field label="SEO title" hint={<Count n={meta.metaTitle.length} max={60} />}><input value={meta.metaTitle} onChange={(e) => { setMeta({ ...meta, metaTitle: e.target.value }); setDirty(true); }} /></Field>
            <Field label="Meta description" hint={<Count n={meta.metaDescription.length} max={160} />}><textarea rows={3} value={meta.metaDescription} onChange={(e) => { setMeta({ ...meta, metaDescription: e.target.value }); setDirty(true); }} /></Field>
          </div>
          <ul className="seo-checks">{seo.checks.map((k) => <li key={k.label} className={k.ok === true ? 'ok' : k.ok === 'warn' ? 'warn' : 'bad'}>{k.label}</li>)}</ul>
        </Card>
      )}
    </div>
  );
}
