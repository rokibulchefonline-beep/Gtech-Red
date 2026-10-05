'use client';

import { useState } from 'react';
import { api } from '@/components/admin/api';
import { Badge, Card, Field, PageTitle, useToast } from '@/components/admin/ui';
import { roleInfo, type Role } from '@/lib/auth-shared';

export default function ProfileForm({ name: n0, email, role }: { name: string; email: string; role: Role }) {
  const toast = useToast();
  const [name, setName] = useState(n0);
  const [pw, setPw] = useState({ currentPassword: '', newPassword: '', confirm: '' });
  const [busy, setBusy] = useState(false);

  async function saveName() { setBusy(true); try { await api('/api/admin/profile', { method: 'PUT', body: { name } }); toast('Name updated'); } catch (e) { toast(e instanceof Error ? e.message : 'Failed', true); } setBusy(false); }
  async function savePw() {
    if (pw.newPassword !== pw.confirm) { toast('The new passwords do not match.', true); return; }
    setBusy(true);
    try { await api('/api/admin/profile', { method: 'PUT', body: { currentPassword: pw.currentPassword, newPassword: pw.newPassword } }); toast('Password changed'); setPw({ currentPassword: '', newPassword: '', confirm: '' }); } catch (e) { toast(e instanceof Error ? e.message : 'Failed', true); }
    setBusy(false);
  }
  return (
    <>
      <PageTitle title="My account" sub="Your personal details and password." />
      <div className="ad-two">
        <Card title="Profile"><div className="ad-form one">
          <Field label="Name"><input value={name} onChange={(e) => setName(e.target.value)} /></Field>
          <Field label="Email" hint="Ask a super admin to change your email."><input value={email} disabled /></Field>
          <div><Badge tone="blue">{roleInfo[role].label}</Badge> <span className="ad-muted small">{roleInfo[role].about}</span></div>
          <div className="ad-foot"><button className="ad-btn" onClick={saveName} disabled={busy}>Save name</button></div>
        </div></Card>
        <Card title="Change password"><div className="ad-form one">
          <Field label="Current password"><input type="password" value={pw.currentPassword} onChange={(e) => setPw({ ...pw, currentPassword: e.target.value })} autoComplete="current-password" /></Field>
          <Field label="New password" hint="At least 10 characters with letters and numbers."><input type="password" value={pw.newPassword} onChange={(e) => setPw({ ...pw, newPassword: e.target.value })} autoComplete="new-password" /></Field>
          <Field label="Confirm new password"><input type="password" value={pw.confirm} onChange={(e) => setPw({ ...pw, confirm: e.target.value })} autoComplete="new-password" /></Field>
          <div className="ad-foot"><button className="ad-btn" onClick={savePw} disabled={busy}>Change password</button></div>
        </div></Card>
      </div>
    </>
  );
}
