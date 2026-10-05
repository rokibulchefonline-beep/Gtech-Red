import Link from 'next/link';
import Icon from '@/components/Icon';
import { Badge, Card, PageTitle } from '@/components/admin/ui';
import { fmtDate } from '@/lib/fmt';
import { can, currentUser } from '@/lib/auth';
import ImportCard from '@/components/admin/ImportCard';
import { seedPlan } from '@/lib/seed';
import { count, list } from '@/lib/store';
import { scoped } from '@/lib/mongo';

export default async function Dashboard() {
  return scoped(async () => {
  const user = (await currentUser())!;
  const safe = <T,>(p: Promise<T>, d: T) => p.catch(() => d);
  const plan = can(user.role, 'content') ? await safe(seedPlan(), { posts: 0, caseStudies: 0, partners: 0, clients: 0 }) : { posts: 0, caseStudies: 0, partners: 0, clients: 0 };
  const [newLeads, leads, posts, drafts, cases, recent] = await Promise.all([
    safe(count('leads', { status: 'new' }), 0), safe(count('leads'), 0), safe(count('posts', { status: 'published' }), 0),
    safe(count('posts', { status: 'draft' }), 0), safe(count('case_studies', { status: 'published' }), 0),
    can(user.role, 'leads') ? safe(list('leads', { sort: { createdAt: -1 }, limit: 6 }), []) : Promise.resolve([]),
  ]);
  const stats = [
    can(user.role, 'leads') && { icon: 'lucide:inbox', label: 'New leads', value: newLeads, href: '/admin/leads', tone: newLeads ? 'hot' : '' },
    can(user.role, 'leads') && { icon: 'lucide:users', label: 'Total leads', value: leads, href: '/admin/leads' },
    can(user.role, 'content') && { icon: 'lucide:newspaper', label: 'Published posts', value: posts, href: '/admin/posts' },
    can(user.role, 'content') && { icon: 'lucide:file-clock', label: 'Draft posts', value: drafts, href: '/admin/posts?status=draft' },
    can(user.role, 'content') && { icon: 'lucide:trophy', label: 'Case studies', value: cases, href: '/admin/case-studies' },
  ].filter(Boolean) as { icon: string; label: string; value: number; href: string; tone?: string }[];

  return (
    <>
      <PageTitle title={`Welcome back, ${user.name.split(' ')[0]}`} sub="Manage your website content, SEO and leads." />
      <ImportCard plan={plan} />
      <div className="ad-stats">{stats.map((s) => <Link key={s.label} href={s.href} className={`ad-stat ${s.tone ?? ''}`}><Icon name={s.icon} size={22} /><strong>{s.value}</strong><span>{s.label}</span></Link>)}</div>
      <div className="ad-two">
        {can(user.role, 'leads') && (
          <Card title="Latest leads" actions={<Link className="ad-btn small ghost" href="/admin/leads">View all</Link>}>
            {recent.length === 0 ? <p className="ad-empty">No leads yet. They appear here when someone submits a form.</p> : (
              <ul className="ad-recent">{recent.map((l) => (
                <li key={l._id}><Link href={`/admin/leads?open=${l._id}`}><b>{l.name}</b><span>{l.service}</span><Badge tone={l.status === 'new' ? 'red' : 'grey'}>{l.status}</Badge><small>{fmtDate(l.createdAt)}</small></Link></li>
              ))}</ul>
            )}
          </Card>
        )}
        <Card title="Quick actions">
          <div className="ad-quick">
            {can(user.role, 'content') && <>
              <Link href="/admin/posts/new"><Icon name="lucide:pen-line" size={18} />Write a blog post</Link>
              <Link href="/admin/case-studies/new"><Icon name="lucide:trophy" size={18} />Add a case study</Link>
              <Link href="/admin/pages"><Icon name="lucide:file-pen" size={18} />Edit page content</Link>
              <Link href="/admin/seo"><Icon name="lucide:search-check" size={18} />Manage SEO</Link>
            </>}
            {can(user.role, 'settings') && <Link href="/admin/settings"><Icon name="lucide:mail" size={18} />Email and site settings</Link>}
            {can(user.role, 'users') && <Link href="/admin/users"><Icon name="lucide:users" size={18} />Manage users</Link>}
          </div>
          {can(user.role, 'content') && <p className="ad-muted small">The public site is pre-rendered for speed. After editing content, use <b>Publish site</b> (top right) to rebuild it.</p>}
        </Card>
      </div>
    </>
  );
});
}
