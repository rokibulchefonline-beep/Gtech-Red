'use client';

import Link from 'next/link';
import { useEffect, useMemo, useState } from 'react';
import Icon from '@/components/Icon';
import { api } from '@/components/admin/api';
import { Card, Count, Field, ListField, useToast } from '@/components/admin/ui';
import { SchemaBox } from '@/components/admin/SeoManager';
import { analyse } from '@/lib/seo-analysis';
import { encodeSeoId } from '@/lib/site-pages';

export type EditSection = { id: string; type: string; nav?: string; heading?: string; paras?: string[]; bullets?: string[]; text?: string };
export type PageInfo = { kw: string; sec: string[]; ent: string[]; linksIn: number; linksOut: number; anchorsIn: { from: string; anchor: string }[]; out: { href: string; anchor: string }[] };
export type PageBase = { kind: 'service' | 'industry'; slug: string; name: string; metaTitle: string; metaDescription: string; hero: { keyword: string; lead: string; points: string[] }; sections: EditSection[]; faqs: { q: string; a: string }[] };

const same = (a: unknown, b: unknown) => JSON.stringify(a) === JSON.stringify(b);

/** Edits the copy of a service or industry page. Only fields that differ from the built-in text are saved as overrides. */
export default function PageEditor({ base, info }: { base: PageBase; info: PageInfo }) {
  const toast = useToast();
  const key = `${base.kind}~${base.slug}`;
  const [tab, setTab] = useState<'content' | 'faqs' | 'seo'>('content');
  const [hero, setHero] = useState(base.hero);
  const [sections, setSections] = useState(base.sections);
  const [faqs, setFaqs] = useState(base.faqs);
  const [meta, setMeta] = useState({ metaTitle: base.metaTitle, metaDescription: base.metaDescription, focusKeyword: '' });
  const [seoDoc, setSeoDoc] = useState<Record<string, unknown>>({ schemaOff: false, schemaCustom: '' });
  const pagePath = `/${base.kind === 'service' ? 'services' : 'industries'}/${base.slug}`;
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
  useEffect(() => { api(`/api/admin/seo/${encodeSeoId(pagePath)}`).then(({ doc }) => { if (doc) setSeoDoc(doc); }).catch(() => {}); }, [pagePath]);
  useEffect(() => {
    const warn = (e: BeforeUnloadEvent) => { if (dirty) e.preventDefault(); };
    window.addEventListener('beforeunload', warn); return () => window.removeEventListener('beforeunload', warn);
  }, [dirty]);

  const upd = (id: string, patch: Partial<EditSection>) => { setSections((ss) => ss.map((s) => (s.id === id ? { ...s, ...patch } : s))); setDirty(true); };
  const text = useMemo(() => [hero.lead, ...hero.points, ...sections.flatMap((s) => [s.heading ?? '', s.text ?? '', ...(s.paras ?? []), ...(s.bullets ?? [])]), ...faqs.flatMap((f) => [f.q, f.a])].join(' \n'), [hero, sections, faqs]);
  const keyword = meta.focusKeyword || info.kw;
  const md = useMemo(() => [hero.lead, ...sections.flatMap((s) => [s.heading ? `## ${s.heading}` : '', s.text ?? '', ...(s.paras ?? []), ...(s.bullets ?? [])])].join('\n\n'), [hero, sections]);
  const seo = useMemo(() => analyse({ title: base.name, metaTitle: meta.metaTitle, metaDescription: meta.metaDescription, keyword: keyword.replace(/\b(services?|company|agency|uk)\b/gi, '').trim(), body: md, slug: base.slug, links: info.linksOut, image: 'hero' }), [base.name, base.slug, meta, keyword, md, info.linksOut]);
  const cover = (list: string[]) => list.map((t) => ({ t, ok: text.toLowerCase().includes(t.toLowerCase().replace(/^(a|an|the) /, '')) }));
  const secCov = useMemo(() => cover(info.sec), [text, info.sec]); // eslint-disable-line react-hooks/exhaustive-deps
  const entCov = useMemo(() => cover(info.ent), [text, info.ent]); // eslint-disable-line react-hooks/exhaustive-deps

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
      await api(`/api/admin/seo/${encodeSeoId(pagePath)}`, { method: 'PUT', body: { ...seoDoc, path: pagePath, focusKeyword: meta.focusKeyword } });
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
        <div className="ad-stack">
          <Card title="Search appearance">
            <div className="serp"><small>gtechdigital.co.uk{pagePath}</small><b>{meta.metaTitle.slice(0, 60)}</b><p>{meta.metaDescription.slice(0, 160)}</p></div>
            <div className="ad-form one">
              <Field label="Focus keyword" hint={`Defaults to the keyword map: “${info.kw}”.`}><input value={meta.focusKeyword} onChange={(e) => { setMeta({ ...meta, focusKeyword: e.target.value }); setDirty(true); }} placeholder={info.kw} /></Field>
              <Field label="SEO title" hint={<Count n={meta.metaTitle.length} max={60} />}><input value={meta.metaTitle} onChange={(e) => { setMeta({ ...meta, metaTitle: e.target.value }); setDirty(true); }} /></Field>
              <Field label="Meta description" hint={<Count n={meta.metaDescription.length} max={160} />}><textarea rows={3} value={meta.metaDescription} onChange={(e) => { setMeta({ ...meta, metaDescription: e.target.value }); setDirty(true); }} /></Field>
            </div>
            <div className="seo-list"><div className={`seo-score ${seo.score >= 75 ? 'good' : seo.score >= 45 ? 'mid' : 'low'}`}><strong>{seo.score}</strong><span>Page SEO score (live, from the text above)</span></div></div>
            <ul className="seo-checks">{seo.checks.map((k) => <li key={k.label} className={k.ok === true ? 'ok' : k.ok === 'warn' ? 'warn' : 'bad'}>{k.label}</li>)}</ul>
          </Card>
          <Card title="Keyword and entity coverage">
            <p className="ad-muted small">Related keywords and entities from the keyword map. Highlighted ones are missing from this page&apos;s copy.</p>
            <h4 className="ad-h4">Related keywords ({secCov.filter((x) => x.ok).length}/{secCov.length})</h4>
            <p className="au-tags">{secCov.map((x) => <i key={x.t} className={x.ok ? '' : 'miss'}>{x.t}</i>)}</p>
            <h4 className="ad-h4">Entities ({entCov.filter((x) => x.ok).length}/{entCov.length})</h4>
            <p className="au-tags">{entCov.map((x) => <i key={x.t} className={x.ok ? 'e' : 'miss'}>{x.t}</i>)}</p>
          </Card>
          <Card title="Internal links">
            <p className="ad-muted small"><b>{info.linksOut}</b> contextual links out · <b>{info.linksIn}</b> in. Edit them in <code>content/seo-map.ts</code>; they appear in the “Explore topics related to …” block.</p>
            <div className="ad-two"><div><h4 className="ad-h4">Links out</h4><ul className="lk">{info.out.map((l) => <li key={l.href}><a href={l.href} target="_blank" rel="noopener noreferrer">{l.anchor}</a><small>{l.href}</small></li>)}</ul></div>
              <div><h4 className="ad-h4">Links in</h4><ul className="lk">{info.anchorsIn.length ? info.anchorsIn.map((l, i) => <li key={i}>“{l.anchor}”<small>from {l.from}</small></li>) : <li className="ad-muted">No contextual links point here yet.</li>}</ul></div></div>
          </Card>
          <Card title="Structured data"><SchemaBox path={pagePath} form={{ schemaOff: !!seoDoc.schemaOff, schemaCustom: String(seoDoc.schemaCustom ?? '') }} setForm={((f: Record<string, unknown>) => { setSeoDoc({ ...seoDoc, ...f }); setDirty(true); }) as never} /></Card>
        </div>
      )}
    </div>
  );
}
