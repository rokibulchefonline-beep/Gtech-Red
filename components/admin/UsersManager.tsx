'use client';

import { useCallback, useEffect, useState } from 'react';
import Icon from '@/components/Icon';
import { api } from '@/components/admin/api';
import { Badge, Card, Field, PageTitle, confirmDelete, fmtDate, useToast } from '@/components/admin/ui';
import { roleInfo, roleList, type Role } from '@/lib/auth-shared';

type U = { _id?: string; name: string; email: string; role: Role; active: boolean; password?: string; lastLogin?: string };
const blank: U = { name: '', email: '', role: 'editor', active: true, password: '' };

export default function UsersManager({ meId }: { meId: string }) {
  const toast = useToast();
  const [rows, setRows] = useState<U[] | null>(null);
  const [edit, setEdit] = useState<U | null>(null);
  const [busy, setBusy] = useState(false);
  const load = useCallback(() => api('/api/admin/users?size=200').then((d) => setRows(d.rows)).catch((e) => { toast(e.message, true); setRows([]); }), [toast]);
  useEffect(() => { load(); }, [load]);

  async function save() {
    if (!edit) return;
    setBusy(true);
    try {
      if (edit._id) await api(`/api/admin/users/${edit._id}`, { method: 'PUT', body: edit }); else await api('/api/admin/users', { body: edit });
      toast(edit._id ? 'User updated' : 'User created'); setEdit(null); load();
    } catch (e) { toast(e instanceof Error ? e.message : 'Save failed', true); }
    setBusy(false);
  }
  async function del(u: U) {
    if (!confirmDelete(`the account for ${u.name}`)) return;
    try { await api(`/api/admin/users/${u._id}`, { method: 'DELETE' }); toast('User deleted'); load(); } catch (e) { toast(e instanceof Error ? e.message : 'Failed', true); }
  }
  const gen = () => { const a = crypto.getRandomValues(new Uint32Array(3)); return `Gt${[...a].map((n) => n.toString(36)).join('')}9x`; };

  return (
    <>
      <PageTitle title="Users and roles" sub="Only super admins can see this page." actions={<button className="ad-btn" onClick={() => setEdit({ ...blank, password: gen() })}><Icon name="lucide:user-plus" size={16} /> Add user</button>} />
      <div className="ad-roles">{roleList.map((r) => <div key={r}><b>{roleInfo[r].label}</b><span>{roleInfo[r].about}</span></div>)}</div>
      <div className="ad-tablewrap"><table className="ad-table">
        <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Last sign-in</th><th style={{ width: 90 }} /></tr></thead>
        <tbody>
          {rows === null && <tr><td colSpan={5} className="ad-empty">Loading…</td></tr>}
          {rows?.map((u) => (
            <tr key={u._id}>
              <td><b>{u.name}{u._id === meId && <em className="ad-you"> (you)</em>}</b><small className="ad-sub">{u.email}</small></td>
              <td><Badge tone={u.role === 'super_admin' ? 'red' : 'blue'}>{roleInfo[u.role]?.label ?? u.role}</Badge></td>
              <td>{u.active ? <Badge tone="green">active</Badge> : <Badge>deactivated</Badge>}</td>
              <td>{u.lastLogin ? fmtDate(u.lastLogin) : 'Never'}</td>
              <td className="ad-actions"><button className="ad-ico" onClick={() => setEdit({ ...u, password: '' })} aria-label="Edit"><Icon name="lucide:pencil" size={16} /></button>{u._id !== meId && <button className="ad-ico danger" onClick={() => del(u)} aria-label="Delete"><Icon name="lucide:trash-2" size={16} /></button>}</td>
            </tr>
          ))}
        </tbody>
      </table></div>

      {edit && (
        <div className="ad-modal" role="dialog" aria-modal="true" onMouseDown={(e) => { if (e.target === e.currentTarget) setEdit(null); }}>
          <div className="ad-modal-box narrow">
            <header><h2>{edit._id ? 'Edit user' : 'Add user'}</h2><button className="ad-ico" onClick={() => setEdit(null)} aria-label="Close"><Icon name="lucide:x" size={18} /></button></header>
            <Card><div className="ad-form one">
              <Field label="Name"><input value={edit.name} onChange={(e) => setEdit({ ...edit, name: e.target.value })} autoFocus /></Field>
              <Field label="Email"><input type="email" value={edit.email} onChange={(e) => setEdit({ ...edit, email: e.target.value })} /></Field>
              <Field label="Role" hint={roleInfo[edit.role].about}><select value={edit.role} disabled={edit._id === meId} onChange={(e) => setEdit({ ...edit, role: e.target.value as Role })}>{roleList.map((r) => <option key={r} value={r}>{roleInfo[r].label}</option>)}</select></Field>
              <Field label={edit._id ? 'Reset password (optional)' : 'Password'} hint="At least 10 characters with letters and numbers. Share it securely; the user can change it under My account."><input value={edit.password ?? ''} onChange={(e) => setEdit({ ...edit, password: e.target.value })} autoComplete="new-password" placeholder={edit._id ? 'Leave blank to keep the current password' : ''} /></Field>
              <label className="ad-check"><input type="checkbox" checked={edit.active} disabled={edit._id === meId} onChange={(e) => setEdit({ ...edit, active: e.target.checked })} /> Account is active (can sign in)</label>
            </div></Card>
            <footer><button className="ad-btn ghost" onClick={() => setEdit(null)}>Cancel</button><button className="ad-btn" onClick={save} disabled={busy}>{busy ? 'Saving…' : 'Save'}</button></footer>
          </div>
        </div>
      )}
    </>
  );
}
