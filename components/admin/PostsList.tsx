'use client';

import ResourceList, { statusBadge } from '@/components/admin/ResourceList';
import { fmtDate } from '@/lib/fmt';

export default function PostsList({ initialStatus }: { initialStatus?: string }) {
  return <ResourceList resource="posts" noun="Post" title="Blog posts" sub="Write, schedule and optimise articles." newHref="/admin/posts/new" initialStatus={initialStatus}
    rowHref={(r) => `/admin/posts/${r._id}`} statuses={['published', 'draft', 'scheduled']}
    cols={[{ label: 'Title', render: (r) => <><b>{r.title}</b><small className="ad-sub">/blogs/{r.slug}</small></> },
      { label: 'Category', render: (r) => r.category, width: '140px' }, { label: 'Status', render: (r) => statusBadge(r.status), width: '110px' },
      { label: 'Updated', render: (r) => fmtDate(r.updatedAt), width: '120px' }]} />;
}
