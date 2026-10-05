'use client';

import Link from 'next/link';
import { useCallback, useEffect, useState, type ReactNode } from 'react';
import Icon from '@/components/Icon';
import { api } from '@/components/admin/api';
import { Badge, PageTitle, confirmDelete, useToast } from '@/components/admin/ui';

type Row = Record<string, any>; // eslint-disable-line @typescript-eslint/no-explicit-any
export type Col = { label: string; render: (r: Row) => ReactNode; width?: string };

export const statusBadge = (s: string) => <Badge tone={s === 'published' ? 'green' : s === 'scheduled' ? 'blue' : 'amber'}>{s}</Badge>;

/** Searchable, filterable table for a collection with edit and delete. */
export default function ResourceList({ resource, title, sub, newHref, rowHref, cols, statuses, initialStatus = '', noun }: {
  resource: string; title: string; sub?: string; newHref: string; rowHref: (r: Row) => string; cols: Col[]; statuses?: string[]; initialStatus?: string; noun: string;
}) {
  const toast = useToast();
  const [rows, setRows] = useState<Row[] | null>(null);
  const [q, setQ] = useState('');
  const [status, setStatus] = useState(initialStatus);

  const load = useCallback(async () => {
    try { setRows((await api(`/api/admin/${resource}?size=200&q=${encodeURIComponent(q)}${status ? `&status=${status}` : ''}`)).rows); } catch (e) { toast(e instanceof Error ? e.message : 'Failed to load', true); setRows([]); }
  }, [resource, q, status, toast]);
  useEffect(() => { const t = setTimeout(load, 250); return () => clearTimeout(t); }, [load]);

  async function del(r: Row) {
    if (!confirmDelete(`“${r.title || r.name}”`)) return;
    try { await api(`/api/admin/${resource}/${r._id}`, { method: 'DELETE' }); toast(`${noun} deleted`); load(); } catch (e) { toast(e instanceof Error ? e.message : 'Failed', true); }
  }

  return (
    <>
      <PageTitle title={title} sub={sub} actions={<Link className="ad-btn" href={newHref}><Icon name="lucide:plus" size={16} /> New {noun.toLowerCase()}</Link>} />
      <div className="ad-toolbar">
        <div className="ad-search"><Icon name="lucide:search" size={16} /><input value={q} onChange={(e) => setQ(e.target.value)} placeholder={`Search ${title.toLowerCase()}…`} aria-label="Search" /></div>
        {statuses && <div className="ad-seg">{['', ...statuses].map((s) => <button key={s} className={status === s ? 'on' : ''} onClick={() => setStatus(s)}>{s || 'All'}</button>)}</div>}
      </div>
      <div className="ad-tablewrap"><table className="ad-table">
        <thead><tr>{cols.map((c) => <th key={c.label} style={{ width: c.width }}>{c.label}</th>)}<th style={{ width: 90 }} /></tr></thead>
        <tbody>
          {rows === null && <tr><td colSpan={cols.length + 1} className="ad-empty">Loading…</td></tr>}
          {rows?.length === 0 && <tr><td colSpan={cols.length + 1} className="ad-empty">Nothing here yet.</td></tr>}
          {rows?.map((r) => (
            <tr key={r._id}>
              {cols.map((c, i) => <td key={c.label}>{i === 0 ? <Link href={rowHref(r)} className="ad-rowlink">{c.render(r)}</Link> : c.render(r)}</td>)}
              <td className="ad-actions"><Link className="ad-ico" href={rowHref(r)} aria-label="Edit"><Icon name="lucide:pencil" size={16} /></Link><button className="ad-ico danger" onClick={() => del(r)} aria-label="Delete"><Icon name="lucide:trash-2" size={16} /></button></td>
            </tr>
          ))}
        </tbody>
      </table></div>
    </>
  );
}
