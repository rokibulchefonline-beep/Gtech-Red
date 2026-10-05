'use client';

import { useCallback, useEffect, useState } from 'react';
import Icon from '@/components/Icon';
import { api } from '@/components/admin/api';
import { Badge, Field, PageTitle, confirmDelete, fmtDate, useToast } from '@/components/admin/ui';

type Lead = { _id: string; name: string; business: string; email: string; phone: string; service: string; budget?: string; website?: string; message?: string; source?: string; status: string; notes?: string; assignee?: string; value?: number; createdAt: string };
const statuses = ['new', 'contacted', 'qualified', 'won', 'lost'] as const;
const tone = (s: string) => (s === 'new' ? 'red' : s === 'won' ? 'green' : s === 'lost' ? 'grey' : s === 'qualified' ? 'blue' : 'amber') as 'red' | 'green' | 'grey' | 'blue' | 'amber';

export default function LeadsManager() {
  const toast = useToast();
  const [rows, setRows] = useState<Lead[] | null>(null);
  const [q, setQ] = useState('');
  const [status, setStatus] = useState('');
  const [open, setOpen] = useState<Lead | null>(null);
  const [busy, setBusy] = useState(false);

  const load = useCallback(async () => {
    try { setRows((await api(`/api/admin/leads?size=200&q=${encodeURIComponent(q)}${status ? `&status=${status}` : ''}`)).rows); } catch (e) { toast(e instanceof Error ? e.message : 'Failed', true); setRows([]); }
  }, [q, status, toast]);
  useEffect(() => { const t = setTimeout(load, 250); return () => clearTimeout(t); }, [load]);
  useEffect(() => {
    const id = new URLSearchParams(window.location.search).get('open');
    if (id) api(`/api/admin/leads/${id}`).then((d) => setOpen(d.doc)).catch(() => {});
  }, []);

  async function save() {
    if (!open) return;
    setBusy(true);
    try { await api(`/api/admin/leads/${open._id}`, { method: 'PUT', body: open }); toast('Lead updated'); setOpen(null); load(); } catch (e) { toast(e instanceof Error ? e.message : 'Failed', true); }
    setBusy(false);
  }
  async function del() {
    if (!open || !confirmDelete(`the lead from ${open.name}`)) return;
    try { await api(`/api/admin/leads/${open._id}`, { method: 'DELETE' }); toast('Lead deleted'); setOpen(null); load(); } catch (e) { toast(e instanceof Error ? e.message : 'Failed', true); }
  }
  const quick = async (l: Lead, s: string) => { try { await api(`/api/admin/leads/${l._id}`, { method: 'PUT', body: { ...l, status: s } }); load(); } catch (e) { toast(e instanceof Error ? e.message : 'Failed', true); } };

  return (
    <>
      <PageTitle title="Leads" sub="Enquiries from the website forms. Click a lead to follow it up." actions={<a className="ad-btn ghost" href="/api/admin/leads-export"><Icon name="lucide:download" size={16} /> Export CSV</a>} />
      <div className="ad-toolbar">
        <div className="ad-search"><Icon name="lucide:search" size={16} /><input value={q} onChange={(e) => setQ(e.target.value)} placeholder="Search name, business, email, service…" aria-label="Search leads" /></div>
        <div className="ad-seg">{['', ...statuses].map((s) => <button key={s} className={status === s ? 'on' : ''} onClick={() => setStatus(s)}>{s || 'All'}</button>)}</div>
      </div>
      <div className="ad-tablewrap"><table className="ad-table">
        <thead><tr><th>Lead</th><th>Service</th><th>Budget</th><th>Status</th><th>Received</th></tr></thead>
        <tbody>
          {rows === null && <tr><td colSpan={5} className="ad-empty">Loading…</td></tr>}
          {rows?.length === 0 && <tr><td colSpan={5} className="ad-empty">No leads found.</td></tr>}
          {rows?.map((l) => (
            <tr key={l._id} className="click" onClick={() => setOpen(l)}>
              <td><b>{l.name}</b><small className="ad-sub">{l.business} · {l.email}</small></td>
              <td>{l.service}</td><td>{l.budget || '—'}</td>
              <td onClick={(e) => e.stopPropagation()}><select className={`ad-pill ${tone(l.status)}`} value={l.status} onChange={(e) => quick(l, e.target.value)} aria-label="Status">{statuses.map((s) => <option key={s}>{s}</option>)}</select></td>
              <td>{fmtDate(l.createdAt)}</td>
            </tr>
          ))}
        </tbody>
      </table></div>

      {open && (
        <div className="ad-drawer-wrap" onMouseDown={(e) => { if (e.target === e.currentTarget) setOpen(null); }}>
          <aside className="ad-drawer" role="dialog" aria-modal="true" aria-label="Lead details">
            <header><div><h2>{open.name}</h2><Badge tone={tone(open.status)}>{open.status}</Badge></div><button className="ad-ico" onClick={() => setOpen(null)} aria-label="Close"><Icon name="lucide:x" size={18} /></button></header>
            <dl className="ad-dl">
              <div><dt>Business</dt><dd>{open.business}</dd></div>
              <div><dt>Email</dt><dd><a href={`mailto:${open.email}`}>{open.email}</a></dd></div>
              <div><dt>Phone</dt><dd><a href={`tel:${open.phone}`}>{open.phone}</a></dd></div>
              <div><dt>Service</dt><dd>{open.service}</dd></div>
              {open.budget && <div><dt>Budget</dt><dd>{open.budget}</dd></div>}
              {open.website && <div><dt>Website</dt><dd>{open.website}</dd></div>}
              <div><dt>Source</dt><dd>{open.source} form · {fmtDate(open.createdAt)}</dd></div>
              {open.message && <div className="wide"><dt>Message</dt><dd>{open.message}</dd></div>}
            </dl>
            <div className="ad-form one">
              <Field label="Status"><select value={open.status} onChange={(e) => setOpen({ ...open, status: e.target.value })}>{statuses.map((s) => <option key={s}>{s}</option>)}</select></Field>
              <Field label="Assigned to"><input value={open.assignee ?? ''} onChange={(e) => setOpen({ ...open, assignee: e.target.value })} placeholder="Team member name" /></Field>
              <Field label="Deal value (£)"><input type="number" value={open.value ?? 0} onChange={(e) => setOpen({ ...open, value: Number(e.target.value) })} /></Field>
              <Field label="Notes"><textarea rows={5} value={open.notes ?? ''} onChange={(e) => setOpen({ ...open, notes: e.target.value })} placeholder="Call notes, next steps…" /></Field>
            </div>
            <footer><button className="ad-btn danger ghost" onClick={del}><Icon name="lucide:trash-2" size={15} /> Delete</button><a className="ad-btn ghost" href={`mailto:${open.email}`}><Icon name="lucide:mail" size={15} /> Email</a><button className="ad-btn" onClick={save} disabled={busy}>{busy ? 'Saving…' : 'Save'}</button></footer>
          </aside>
        </div>
      )}
    </>
  );
}
