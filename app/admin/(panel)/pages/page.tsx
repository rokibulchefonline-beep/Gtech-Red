import PagesList from '@/components/admin/PagesList';
import { industryContent } from '@/content/industries';
import { serviceContent } from '@/content/services';
import { list } from '@/lib/store';

export default async function Pages() {
  const edited = new Set((await list('page_content', { limit: 500 }).catch(() => [])).map((d) => d._id as string));
  const rows = [
    ...Object.values(serviceContent).map((c) => ({ kind: 'service', slug: c.slug, name: c.short ?? c.slug, path: `/services/${c.slug}`, edited: edited.has(`service~${c.slug}`) })),
    ...Object.values(industryContent).map((c) => ({ kind: 'industry', slug: c.slug, name: c.short ?? c.slug, path: `/industries/${c.slug}`, edited: edited.has(`industry~${c.slug}`) })),
  ];
  return <PagesList rows={rows} />;
}
