'use client';

import { useMemo, useState } from 'react';
import Link from 'next/link';

type N = { id: string; label: string; kind: string; cluster: string };
type E = { from: string; to: string; type: string; anchor: string };

const palette: Record<string, string> = { 'digital-marketing': '#e8202f', 'social-media-marketing': '#2563eb', 'web-design-development': '#16a34a', 'custom-software-development': '#7c3aed', 'branding-strategy': '#f59e0b', industries: '#0ea5e9', site: '#111' };
const SHOW = ['semantic', 'related', 'industries', 'sector-services'];

/** Radial map of the site: categories around the centre, their services on an outer ring, industries as a sixth cluster. */
export default function SemanticNetwork({ nodes, edges, stats }: { nodes: N[]; edges: E[]; stats: Record<string, { in: number; out: number }> }) {
  const [sel, setSel] = useState<string | null>(null);
  const [types, setTypes] = useState<string[]>(['semantic']);

  const layout = useMemo(() => {
    const clusters = ['digital-marketing', 'social-media-marketing', 'web-design-development', 'custom-software-development', 'branding-strategy', 'industries'];
    const pos: Record<string, { x: number; y: number; r: number }> = { '/': { x: 0, y: 0, r: 22 } };
    clusters.forEach((c, i) => {
      const a = (i / clusters.length) * Math.PI * 2 - Math.PI / 2, span = (Math.PI * 2) / clusters.length - 0.12;
      const hub = c === 'industries' ? '/industries' : `/services/${c}`;
      pos[hub] = { x: Math.cos(a) * 150, y: Math.sin(a) * 150, r: 15 };
      const kids = nodes.filter((n) => n.cluster === c && n.id !== hub && (n.kind === 'service' || n.kind === 'industry'));
      kids.forEach((k, j) => {
        const aa = a - span / 2 + (kids.length > 1 ? (j / (kids.length - 1)) * span : span / 2);
        const rad = 320 + (j % 2) * 70;
        pos[k.id] = { x: Math.cos(aa) * rad, y: Math.sin(aa) * rad, r: 7 + Math.min(7, (stats[k.id]?.in ?? 0) * 0.9) };
      });
    });
    ['/services', '/case-studies', '/blogs', '/about', '/contact'].forEach((id, i) => { pos[id] = { x: Math.cos(i * 1.256 + 0.6) * 70, y: Math.sin(i * 1.256 + 0.6) * 70, r: 8 }; });
    return pos;
  }, [nodes, stats]);

  const shown = edges.filter((e) => types.includes(e.type) && layout[e.from] && layout[e.to]);
  const active = sel ? shown.filter((e) => e.from === sel || e.to === sel) : [];
  const info = sel ? { out: edges.filter((e) => e.from === sel && SHOW.includes(e.type)), inn: edges.filter((e) => e.to === sel && SHOW.includes(e.type)) } : null;
  const label = (id: string) => nodes.find((n) => n.id === id)?.label ?? id;
  const adminPath = (id: string) => (id.startsWith('/services/') ? `/admin/pages/service/${id.split('/')[2]}` : id.startsWith('/industries/') ? `/admin/pages/industry/${id.split('/')[2]}` : null);

  return (
    <div className="net">
      <div className="net-main">
        <div className="net-types">
          {[['semantic', 'Semantic links'], ['related', 'Related services'], ['industries', 'Industry links'], ['sector-services', 'Sector → service']].map(([k, l]) => (
            <label key={k} className="ad-check"><input type="checkbox" checked={types.includes(k)} onChange={(e) => setTypes(e.target.checked ? [...types, k] : types.filter((x) => x !== k))} /> {l}</label>
          ))}
        </div>
        <svg viewBox="-470 -440 940 880" role="img" aria-label="Semantic network of site pages" className="net-svg">
          {shown.map((e, i) => {
            const a = layout[e.from], b = layout[e.to], hot = sel && (e.from === sel || e.to === sel);
            const mx = (a.x + b.x) / 2 * 0.55, my = (a.y + b.y) / 2 * 0.55;
            return <path key={i} d={`M${a.x},${a.y} Q${mx},${my} ${b.x},${b.y}`} fill="none" stroke={hot ? '#e8202f' : '#b8bcc6'} strokeWidth={hot ? 2 : 0.8} opacity={sel && !hot ? 0.12 : hot ? 0.95 : 0.5} />;
          })}
          {nodes.filter((n) => layout[n.id]).map((n) => {
            const p = layout[n.id], hot = sel === n.id || active.some((e) => e.from === n.id || e.to === n.id);
            return (
              <g key={n.id} transform={`translate(${p.x},${p.y})`} onClick={() => setSel(sel === n.id ? null : n.id)} className="net-node" opacity={sel && !hot ? 0.3 : 1} role="button" tabIndex={0} aria-label={n.label} onKeyDown={(e) => { if (e.key === 'Enter') setSel(sel === n.id ? null : n.id); }}>
                <circle r={p.r} fill={palette[n.cluster] ?? '#999'} stroke="#fff" strokeWidth="2" />
                {(p.r >= 14 || hot) && <text y={p.r + 13} textAnchor="middle" fontSize="10.5" fontWeight="600" fill="#222" stroke="#fafbfc" strokeWidth="3" paintOrder="stroke">{n.label.length > 22 ? n.label.slice(0, 21) + '…' : n.label}</text>}
                {p.r < 14 && !hot && <title>{n.label}</title>}
              </g>
            );
          })}
        </svg>
        <p className="ad-muted small">Node size = contextual links pointing in. Click a page to see its links. Colours show the service cluster.</p>
      </div>
      <aside className="net-side">
        {!info ? <p className="ad-empty">Select a page to see which pages link to it and where it links.</p> : (
          <>
            <h3>{label(sel!)}</h3>
            {adminPath(sel!) && <p><Link className="ad-btn small ghost" href={adminPath(sel!)!}>Edit page content</Link> <a className="ad-btn small ghost" href={sel!} target="_blank" rel="noopener noreferrer">View</a></p>}
            <h4>Links out ({info.out.length})</h4>
            <ul>{info.out.map((e, i) => <li key={i}><button onClick={() => setSel(e.to)}>{label(e.to)}</button><small>“{e.anchor}” · {e.type}</small></li>)}</ul>
            <h4>Links in ({info.inn.length})</h4>
            <ul>{info.inn.map((e, i) => <li key={i}><button onClick={() => setSel(e.from)}>{label(e.from)}</button><small>“{e.anchor}” · {e.type}</small></li>)}</ul>
          </>
        )}
      </aside>
    </div>
  );
}
