import PagesList from '@/components/admin/PagesList';
import { industryContent } from '@/content/industries';
import { serviceContent } from '@/content/services';
import { list } from '@/lib/store';
import { scoped } from '@/lib/mongo';

export default async function Pages() {
  return scoped(async () => {
  const edited = new Set((await list('page_content', { limit: 500 }).catch(() => [])).map((d) => d._id as string));
  const rows = [
    { kind: 'page', slug: 'home', name: 'Home', path: '/', edited: edited.has('page~home') },
    { kind: 'page', slug: 'about', name: 'About', path: '/about', edited: edited.has('page~about') },
    { kind: 'page', slug: 'contact', name: 'Contact', path: '/contact', edited: edited.has('page~contact') },
    { kind: 'page', slug: 'services-hub', name: 'Services hub', path: '/services', edited: edited.has('page~services-hub') },
    { kind: 'page', slug: 'industries-hub', name: 'Industries hub', path: '/industries', edited: edited.has('page~industries-hub') },
    ...Object.values(serviceContent).map((c) => ({ kind: 'service', slug: c.slug, name: c.short ?? c.slug, path: `/services/${c.slug}`, edited: edited.has(`service~${c.slug}`) })),
    ...Object.values(industryContent).map((c) => ({ kind: 'industry', slug: c.slug, name: c.short ?? c.slug, path: `/industries/${c.slug}`, edited: edited.has(`industry~${c.slug}`) })),
  ];
  return <PagesList rows={rows} />;
});
}
