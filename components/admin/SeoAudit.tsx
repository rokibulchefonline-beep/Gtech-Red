'use client';

import Link from 'next/link';
import { useMemo, useState } from 'react';
import Icon from '@/components/Icon';
import { Badge, Card, PageTitle } from '@/components/admin/ui';
import SemanticNetwork from '@/components/admin/SemanticNetwork';
import type { PageAudit } from '@/lib/seo-audit';

type Data = { summary: { pages: number; seo: number; aeo: number; geo: number; total: number; conflicts: number; orphans: number; broken: number }; pages: PageAudit[]; conflicts: { keyword: string; paths: string[] }[]; orphans: string[]; broken: string[];
  graph: { nodes: { id: string; label: string; kind: string; cluster: string }[]; edges: { from: string; to: string; type: string; anchor: string }[]; stats: Record<string, { in: number; out: number }> } };
const tone = (n: number) => (n >= 85 ? 'good' : n >= 65 ? 'mid' : 'low');
const Score = ({ n, label }: { n: number; label: string }) => <div className={`au-score ${tone(n)}`}><strong>{n}</strong><span>{label}</span></div>;
const editPath = (p: PageAudit) => `/admin/pages/${p.kind}/${p.path.split('/')[2]}`;

export default function SeoAudit({ data, map }: { data: Data; map: Record<string, { kw: string; sec: string[]; ent: string[] }> }) {
  const [tab, setTab] = useState<'pages' | 'links' | 'network' | 'keywords'>('pages');
  const [q, setQ] = useState('');
  const [sort, setSort] = useState<'total' | 'seo' | 'aeo' | 'geo' | 'name'>('total');
  const [open, setOpen] = useState<string | null>(null);
  const [only, setOnly] = useState<'' | 'service' | 'industry'>('');
  const s = data.summary;
  const rows = useMemo(() => data.pages.filter((p) => (!only || p.kind === only) && (p.name + p.path + p.keyword).toLowerCase().includes(q.toLowerCase()))
    .sort((a, b) => (sort === 'name' ? a.name.localeCompare(b.name) : a.scores[sort] - b.scores[sort])), [data.pages, q, sort, only]);
  const issues = data.pages.reduce((n, p) => n + p.checks.filter((c) => c.level === 'fail').length, 0);

  return (
    <>
      <PageTitle title="SEO audit" sub="SEO, AEO (answer engines) and GEO (generative AI) checks for every service and industry page, plus the internal link and semantic network. Re-runs on every visit." />
      <div className="au-top">
        <Score n={s.total} label="Overall" /><Score n={s.seo} label="SEO" /><Score n={s.aeo} label="AEO" /><Score n={s.geo} label="GEO" />
        <div className="au-facts"><span><b>{s.pages}</b> pages audited</span><span><b>{issues}</b> failing checks</span><span><b>{s.orphans}</b> orphan pages</span><span><b>{s.conflicts}</b> keyword conflicts</span><span><b>{s.broken}</b> broken semantic links</span></div>
      </div>
      <div className="ed-tabs inline">{([['pages', 'Page audit'], ['keywords', 'Keyword map'], ['links', 'Internal links'], ['network', 'Semantic network']] as const).map(([k, l]) => <button key={k} className={tab === k ? 'on' : ''} onClick={() => setTab(k)}>{l}</button>)}</div>

      {tab === 'pages' && <>
        <div className="ad-toolbar">
          <div className="ad-search"><Icon name="lucide:search" size={16} /><input value={q} onChange={(e) => setQ(e.target.value)} placeholder="Search pages or keywords…" aria-label="Search" /></div>
          <div className="ad-seg">{[['', 'All'], ['service', 'Services'], ['industry', 'Industries']].map(([v, l]) => <button key={v} className={only === v ? 'on' : ''} onClick={() => setOnly(v as typeof only)}>{l}</button>)}</div>
          <select className="au-sort" value={sort} onChange={(e) => setSort(e.target.value as typeof sort)} aria-label="Sort"><option value="total">Lowest overall first</option><option value="seo">Lowest SEO first</option><option value="aeo">Lowest AEO first</option><option value="geo">Lowest GEO first</option><option value="name">A to Z</option></select>
        </div>
        <div className="ad-tablewrap"><table className="ad-table">
          <thead><tr><th>Page</th><th>Target keyword</th><th>SEO</th><th>AEO</th><th>GEO</th><th>Total</th><th /></tr></thead>
          <tbody>{rows.map((p) => (
            <>
              <tr key={p.path} className="click" onClick={() => setOpen(open === p.path ? null : p.path)}>
                <td><b>{p.name}</b><small className="ad-sub">{p.path}</small></td><td>{p.keyword}</td>
                {(['seo', 'aeo', 'geo', 'total'] as const).map((k) => <td key={k}><span className={`au-chip ${tone(p.scores[k])}`}>{p.scores[k]}</span></td>)}
                <td><Icon name={open === p.path ? 'lucide:chevron-up' : 'lucide:chevron-down'} size={16} /></td>
              </tr>
              {open === p.path && (
                <tr key={p.path + 'x'}><td colSpan={7} className="au-detail">
                  <div className="au-stats"><span>{p.stats.words} words</span><span>{p.stats.h2} sections</span><span>{p.stats.faqs} FAQs</span><span>title {p.stats.titleLen}</span><span>description {p.stats.descLen}</span><span>links in {p.stats.linksIn}</span><span>links out {p.stats.linksOut}</span>
                    <Link className="ad-btn small" href={editPath(p)}>Edit this page</Link></div>
                  {(['SEO', 'AEO', 'GEO'] as const).map((g) => (
                    <div key={g} className="au-group"><h4>{g}</h4>
                      <ul>{p.checks.filter((c) => c.group === g).map((c) => (
                        <li key={c.id} className={c.level}><Icon name={c.level === 'pass' ? 'lucide:circle-check' : c.level === 'warn' ? 'lucide:circle-alert' : 'lucide:circle-x'} size={16} /><span>{c.label}{c.level !== 'pass' && (c.detail || c.fix) && <small>{c.detail} {c.fix && <em>Fix: {c.fix}</em>}</small>}</span></li>
                      ))}</ul></div>
                  ))}
                </td></tr>
              )}
            </>
          ))}</tbody>
        </table></div>
      </>}

      {tab === 'keywords' && (
        <Card title="Keyword and entity map">
          <p className="ad-muted small">Each page targets one primary keyword, supports it with related (LSI) phrases, and names the entities people and AI systems associate with it. Edit the map in <code>content/seo-map.ts</code>; set a page-specific focus keyword in Admin &gt; Page content &gt; SEO.</p>
          <div className="ad-tablewrap"><table className="ad-table"><thead><tr><th>Page</th><th>Primary</th><th>Related keywords</th><th>Entities</th></tr></thead>
            <tbody>{data.pages.map((p) => { const m = map[p.path.split('/')[2]]; return <tr key={p.path}><td><Link className="ad-rowlink" href={editPath(p)}><b>{p.name}</b></Link></td><td>{m?.kw}</td><td className="au-tags">{m?.sec.map((x) => <i key={x}>{x}</i>)}</td><td className="au-tags">{m?.ent.map((x) => <i key={x} className="e">{x}</i>)}</td></tr>; })}</tbody></table></div>
          {data.conflicts.length > 0 && <div className="ad-note">Keyword conflicts (two pages competing for the same phrase): {data.conflicts.map((c) => `${c.keyword} → ${c.paths.join(', ')}`).join(' | ')}</div>}
        </Card>
      )}

      {tab === 'links' && (
        <Card title="Contextual internal links">
          <p className="ad-muted small">Counts links placed in the body of each page (related services, industries, semantic links). Navigation, footer and breadcrumb links are excluded because they do not pass topical relevance.</p>
          {data.orphans.length > 0 && <div className="ad-note">Orphan pages (no contextual links in): {data.orphans.join(', ')}</div>}
          {data.broken.length > 0 && <div className="ad-note">Broken semantic targets: {data.broken.join(', ')}</div>}
          <div className="ad-tablewrap"><table className="ad-table"><thead><tr><th>Page</th><th>Links in</th><th>Links out</th></tr></thead>
            <tbody>{data.graph.nodes.filter((n) => data.graph.stats[n.id]).sort((a, b) => data.graph.stats[a.id].in - data.graph.stats[b.id].in).map((n) => { const st = data.graph.stats[n.id]; return <tr key={n.id}><td><b>{n.label}</b><small className="ad-sub">{n.id}</small></td><td><span className={`au-chip ${st.in >= 4 ? 'good' : st.in >= 1 ? 'mid' : 'low'}`}>{st.in}</span></td><td>{st.out}</td></tr>; })}</tbody></table></div>
        </Card>
      )}

      {tab === 'network' && <Card title="Semantic network"><SemanticNetwork nodes={data.graph.nodes} edges={data.graph.edges} stats={data.graph.stats} /></Card>}
    </>
  );
}
