'use client';

import ResourceList, { statusBadge } from '@/components/admin/ResourceList';
import { fmtDate } from '@/lib/fmt';

export default function CasesList() {
  return <ResourceList resource="case-studies" noun="Case study" title="Case studies" sub="Results stories shown on the site. Until you publish one, demo studies appear." newHref="/admin/case-studies/new"
    rowHref={(r) => `/admin/case-studies/${r._id}`} statuses={['published', 'draft']}
    cols={[{ label: 'Case study', render: (r) => <><b>{r.title}</b><small className="ad-sub">{(r.metrics ?? []).slice(0, 3).map((m: { value: string; label: string }) => `${m.value} ${m.label}`).join(' · ')}</small></> },
      { label: 'Industry', render: (r) => r.industry, width: '160px' }, { label: 'Status', render: (r) => statusBadge(r.status), width: '110px' }, { label: 'Updated', render: (r) => fmtDate(r.updatedAt), width: '120px' }]} />;
}
