'use client';

import { useRouter } from 'next/navigation';
import { useState } from 'react';
import Icon from '@/components/Icon';
import { api } from '@/components/admin/api';
import { useToast } from '@/components/admin/ui';

type Plan = { posts: number; caseStudies: number; partners: number; clients: number };

/** One-click import of the content that ships with the website, so it shows up here and can be edited. */
export default function ImportCard({ plan }: { plan: Plan }) {
  const router = useRouter();
  const toast = useToast();
  const [busy, setBusy] = useState(false);
  const total = plan.posts + plan.caseStudies + plan.partners + plan.clients;
  if (!total) return null;
  async function run() {
    setBusy(true);
    try { await api('/api/admin/seed', { method: 'POST', body: {} }); toast('Imported. Everything is now editable here.'); router.refresh(); } catch (e) { toast(e instanceof Error ? e.message : 'Import failed', true); }
    setBusy(false);
  }
  return (
    <div className="ad-import">
      <Icon name="lucide:database" size={26} />
      <div>
        <h2>Import your current website content</h2>
        <p>Your database is empty, so the admin has nothing to edit yet. One click copies what the website already shows into it: <b>{plan.posts}</b> blog posts, <b>{plan.caseStudies}</b> case studies, <b>{plan.partners}</b> partner badges and <b>{plan.clients}</b> client logos. After that you can change, add or delete any of them. Nothing on the live site changes until you click Publish site.</p>
      </div>
      <button className="ad-btn" onClick={run} disabled={busy}>{busy ? 'Importing…' : 'Import now'}</button>
    </div>
  );
}
