'use client';

import { useCallback, useEffect, useState } from 'react';
import Icon from '@/components/Icon';
import { api } from '@/components/admin/api';
import { Card, Field, ImageField, PageTitle, confirmDelete, useToast } from '@/components/admin/ui';

type Item = { _id?: string; name: string; logo: string; url: string; order: number; visible: boolean };
const blank: Item = { name: '', logo: '', url: '', order: 100, visible: true };

/** Manager for logo collections: partner badges and client logos. */
export default function LogoManager({ resource, title, sub, noun }: { resource: 'partners' | 'clients'; title: string; sub: string; noun: string }) {
  const toast = useToast();
  const [rows, setRows] = useState<Item[] | null>(null);
  const [edit, setEdit] = useState<Item | null>(null);
  const [busy, setBusy] = useState(false);

  const load = useCallback(() => api(`/api/admin/${resource}?size=200`).then((d) => setRows(d.rows)).catch((e) => { toast(e.message, true); setRows([]); }), [resource, toast]);
  useEffect(() => { load(); }, [load]);

  async function save() {
    if (!edit) return;
    setBusy(true);
    try {
      if (edit._id) await api(`/api/admin/${resource}/${edit._id}`, { method: 'PUT', body: edit }); else await api(`/api/admin/${resource}`, { body: edit });
      toast('Saved'); setEdit(null); load();
    } catch (e) { toast(e instanceof Error ? e.message : 'Save failed', true); }
    setBusy(false);
  }
  async function del(r: Item) {
    if (!confirmDelete(`“${r.name}”`)) return;
    try { await api(`/api/admin/${resource}/${r._id}`, { method: 'DELETE' }); toast('Deleted'); load(); } catch (e) { toast(e instanceof Error ? e.message : 'Failed', true); }
  }
  async function toggle(r: Item) { await api(`/api/admin/${resource}/${r._id}`, { method: 'PUT', body: { ...r, visible: !r.visible } }).catch((e) => toast(e.message, true)); load(); }

  return (
    <>
      <PageTitle title={title} sub={sub} actions={<button className="ad-btn" onClick={() => setEdit({ ...blank })}><Icon name="lucide:plus" size={16} /> Add {noun}</button>} />
      {rows && rows.length === 0 && <p className="ad-note">None added yet, so the site shows its built-in {resource === 'partners' ? 'partner badges' : 'demo logos'}. Add your own and they replace those automatically.</p>}
      <div className="ad-logos">
        {rows?.map((r) => (
          <div key={r._id} className={`ad-logo${r.visible ? '' : ' off'}`}>
            <div className="ad-logo-img">{r.logo ? /* eslint-disable-next-line @next/next/no-img-element */ <img src={r.logo} alt={r.name} /> : <Icon name="lucide:image" size={26} />}</div>
            <b>{r.name}</b><small>Order {r.order}{r.visible ? '' : ' · hidden'}</small>
            <div className="ad-logo-act"><button className="ad-ico" onClick={() => setEdit(r)} aria-label="Edit"><Icon name="lucide:pencil" size={16} /></button><button className="ad-ico" onClick={() => toggle(r)} aria-label={r.visible ? 'Hide' : 'Show'}><Icon name={r.visible ? 'lucide:eye' : 'lucide:eye-off'} size={16} /></button><button className="ad-ico danger" onClick={() => del(r)} aria-label="Delete"><Icon name="lucide:trash-2" size={16} /></button></div>
          </div>
        ))}
      </div>
      {edit && (
        <div className="ad-modal" role="dialog" aria-modal="true" onMouseDown={(e) => { if (e.target === e.currentTarget) setEdit(null); }}>
          <div className="ad-modal-box narrow">
            <header><h2>{edit._id ? 'Edit' : 'Add'} {noun}</h2><button className="ad-ico" onClick={() => setEdit(null)} aria-label="Close"><Icon name="lucide:x" size={18} /></button></header>
            <Card><div className="ad-form one">
              <Field label="Name"><input value={edit.name} onChange={(e) => setEdit({ ...edit, name: e.target.value })} autoFocus /></Field>
              <Field label="Logo" hint="PNG, WebP or SVG with a transparent background works best."><ImageField value={edit.logo} onChange={(v) => setEdit({ ...edit, logo: v })} label="Logo" /></Field>
              <Field label="Link (optional)"><input value={edit.url} onChange={(e) => setEdit({ ...edit, url: e.target.value })} placeholder="https://" /></Field>
              <Field label="Order" hint="Lower numbers come first."><input type="number" value={edit.order} onChange={(e) => setEdit({ ...edit, order: Number(e.target.value) })} /></Field>
              <label className="ad-check"><input type="checkbox" checked={edit.visible} onChange={(e) => setEdit({ ...edit, visible: e.target.checked })} /> Show on the website</label>
            </div></Card>
            <footer><button className="ad-btn ghost" onClick={() => setEdit(null)}>Cancel</button><button className="ad-btn" onClick={save} disabled={busy}>{busy ? 'Saving…' : 'Save'}</button></footer>
          </div>
        </div>
      )}
    </>
  );
}
