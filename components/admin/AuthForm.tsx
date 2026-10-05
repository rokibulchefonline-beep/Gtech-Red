'use client';

import { useRouter } from 'next/navigation';
import { useState } from 'react';
import { api } from '@/components/admin/api';

export default function AuthForm({ mode, needsKey, dbError }: { mode: 'login' | 'setup'; needsKey?: boolean; dbError?: string }) {
  const router = useRouter();
  const [err, setErr] = useState('');
  const [busy, setBusy] = useState(false);

  async function submit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setBusy(true); setErr('');
    try {
      await api(`/api/admin/auth/${mode}`, { method: 'POST', body: Object.fromEntries(new FormData(e.currentTarget)) });
      router.push('/admin'); router.refresh();
    } catch (x) { setErr(x instanceof Error ? x.message : 'Something went wrong'); setBusy(false); }
  }

  return (
    <div className="ad-auth">
      <form onSubmit={submit} className="ad-auth-box">
        <div className="ad-brand big"><span>G</span> GTech Admin</div>
        <h1>{mode === 'login' ? 'Sign in' : 'Create the super admin'}</h1>
        {mode === 'setup' && <p className="ad-muted">First-time setup. This account can manage users, content and settings.</p>}
        {dbError && <div className="ad-alert bad" role="alert"><b>Database problem.</b> {dbError.replace(/mongodb(\+srv)?:\/\/\S+/gi, 'mongodb://…').slice(0, 220)}</div>}
        {err && <div className="ad-alert bad" role="alert">{err}</div>}
        {mode === 'setup' && <label className="ad-field"><span>Your name</span><input name="name" required autoComplete="name" /></label>}
        <label className="ad-field"><span>Email</span><input name="email" type="email" required autoComplete="username" autoFocus /></label>
        <label className="ad-field"><span>Password</span><input name="password" type="password" required autoComplete={mode === 'login' ? 'current-password' : 'new-password'} minLength={mode === 'setup' ? 10 : undefined} />
          {mode === 'setup' && <small>At least 10 characters with letters and numbers.</small>}</label>
        {mode === 'setup' && needsKey && <label className="ad-field"><span>Setup key</span><input name="setupKey" type="password" required /><small>The SETUP_KEY value from your environment variables.</small></label>}
        <button className="ad-btn big" disabled={busy}>{busy ? 'Please wait…' : mode === 'login' ? 'Sign in' : 'Create account'}</button>
      </form>
    </div>
  );
}
