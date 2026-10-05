'use client';

import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { useState } from 'react';
import Icon from '@/components/Icon';
import { api } from '@/components/admin/api';
import { useToast } from '@/components/admin/ui';
import { can, roleInfo, type SessionUser } from '@/lib/auth-shared';

const nav = [
  { href: '/admin', label: 'Dashboard', icon: 'lucide:layout-dashboard', exact: true },
  { group: 'Content' },
  { href: '/admin/pages', label: 'Page content', icon: 'lucide:file-pen', perm: 'content' },
  { href: '/admin/posts', label: 'Blog posts', icon: 'lucide:newspaper', perm: 'content' },
  { href: '/admin/case-studies', label: 'Case studies', icon: 'lucide:trophy', perm: 'content' },
  { href: '/admin/partners', label: 'Partner badges', icon: 'lucide:badge-check', perm: 'content' },
  { href: '/admin/clients', label: 'Client logos', icon: 'lucide:building-2', perm: 'content' },
  { href: '/admin/seo', label: 'SEO manager', icon: 'lucide:search-check', perm: 'content' },
  { href: '/admin/seo-audit', label: 'SEO audit', icon: 'lucide:gauge', perm: 'content' },
  { group: 'Sales' },
  { href: '/admin/leads', label: 'Leads', icon: 'lucide:inbox', perm: 'leads' },
  { group: 'System' },
  { href: '/admin/users', label: 'Users and roles', icon: 'lucide:users', perm: 'users' },
  { href: '/admin/settings', label: 'Settings', icon: 'lucide:settings', perm: 'settings' },
  { href: '/admin/profile', label: 'My account', icon: 'lucide:user-round' },
] as const;

export default function Shell({ user, children }: { user: SessionUser; children: React.ReactNode }) {
  const path = usePathname();
  const router = useRouter();
  const toast = useToast();
  const [open, setOpen] = useState(false);
  const [pub, setPub] = useState(false);

  const items = nav.filter((n, i) => {
    if ('group' in n) { // keep a group heading only if something under it is visible
      for (let j = i + 1; j < nav.length && !('group' in nav[j]); j++) { const x = nav[j]; if (!('perm' in x) || can(user.role, x.perm)) return true; }
      return false;
    }
    return !('perm' in n) || can(user.role, n.perm);
  });

  async function logout() { await api('/api/admin/auth/logout', { method: 'POST', body: {} }); router.push('/admin/login'); router.refresh(); }
  async function publish() {
    if (!window.confirm('Rebuild the public site now? Your saved changes go live in a few minutes.')) return;
    setPub(true);
    try { await api('/api/admin/publish', { method: 'POST', body: {} }); toast('Rebuild started. Changes will be live shortly.'); } catch (e) { toast(e instanceof Error ? e.message : 'Failed', true); }
    setPub(false);
  }

  return (
    <div className={`ad-shell${open ? ' open' : ''}`}>
      <aside className="ad-side">
        <Link href="/admin" className="ad-brand" onClick={() => setOpen(false)}><span>G</span> GTech Admin</Link>
        <nav>
          {items.map((n, i) => 'group' in n
            ? <p key={i} className="ad-group">{n.group}</p>
            : <Link key={n.href} href={n.href} onClick={() => setOpen(false)} className={('exact' in n && n.exact ? path === n.href : n.href === '/admin/seo' ? path === '/admin/seo' : path.startsWith(n.href)) ? 'on' : ''}><Icon name={n.icon} size={18} />{n.label}</Link>)}
        </nav>
        <div className="ad-me">
          <b>{user.name}</b><small>{roleInfo[user.role].label}</small>
          <button onClick={logout}><Icon name="lucide:log-out" size={16} /> Sign out</button>
        </div>
      </aside>
      {open && <button className="ad-scrim" aria-label="Close menu" onClick={() => setOpen(false)} />}
      <div className="ad-main">
        <header className="ad-top">
          <button className="ad-burger" aria-label="Open menu" onClick={() => setOpen(true)}><Icon name="lucide:menu" size={22} /></button>
          <div className="ad-top-r">
            <a className="ad-btn ghost small" href="/" target="_blank" rel="noopener noreferrer"><Icon name="lucide:external-link" size={15} /> View site</a>
            {can(user.role, 'content') && <button className="ad-btn small" onClick={publish} disabled={pub}><Icon name="lucide:rocket" size={15} /> {pub ? 'Starting…' : 'Publish site'}</button>}
          </div>
        </header>
        <div className="ad-content">{children}</div>
      </div>
    </div>
  );
}
