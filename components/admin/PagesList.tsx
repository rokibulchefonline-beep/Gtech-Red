'use client';

import Link from 'next/link';
import { useState } from 'react';
import Icon from '@/components/Icon';
import { Badge, PageTitle } from '@/components/admin/ui';

type Row = { kind: string; slug: string; name: string; path: string; edited: boolean };

export default function PagesList({ rows }: { rows: Row[] }) {
  const [q, setQ] = useState('');
  const [kind, setKind] = useState('');
  const shown = rows.filter((r) => (!kind || r.kind === kind) && (r.name + r.path).toLowerCase().includes(q.toLowerCase()));
  return (
    <>
      <PageTitle title="Page content" sub="Edit the hero, section text, FAQs and search appearance of every service and industry page." />
      <div className="ad-toolbar">
        <div className="ad-search"><Icon name="lucide:search" size={16} /><input value={q} onChange={(e) => setQ(e.target.value)} placeholder="Search pages…" aria-label="Search pages" /></div>
        <div className="ad-seg">{[['', 'All'], ['service', 'Services'], ['industry', 'Industries']].map(([v, l]) => <button key={v} className={kind === v ? 'on' : ''} onClick={() => setKind(v)}>{l}</button>)}</div>
      </div>
      <div className="ad-tablewrap"><table className="ad-table">
        <thead><tr><th>Page</th><th>Type</th><th>Status</th><th style={{ width: 70 }} /></tr></thead>
        <tbody>{shown.map((r) => (
          <tr key={r.path}>
            <td><Link className="ad-rowlink" href={`/admin/pages/${r.kind}/${r.slug}`}><b>{r.name}</b><small className="ad-sub">{r.path}</small></Link></td>
            <td>{r.kind}</td><td>{r.edited ? <Badge tone="blue">edited</Badge> : <Badge>original</Badge>}</td>
            <td className="ad-actions"><Link className="ad-ico" href={`/admin/pages/${r.kind}/${r.slug}`} aria-label="Edit"><Icon name="lucide:pencil" size={16} /></Link></td>
          </tr>
        ))}</tbody>
      </table></div>
    </>
  );
}
