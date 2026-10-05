import SeoManager from '@/components/admin/SeoManager';
import { industryContent } from '@/content/industries';
import { serviceContent } from '@/content/services';
import { listDocs } from '@/lib/mongo';
import { staticPages } from '@/lib/site-pages';

export default async function Seo() {
  const cases = await listDocs('case_studies', 200);
  const pages = [
    ...staticPages.map((p) => ({ ...p, group: 'Main pages' })),
    ...Object.values(serviceContent).map((c) => ({ path: `/services/${c.slug}`, label: c.short ?? c.slug, group: 'Services', title: c.metaTitle, description: c.metaDescription })),
    ...Object.values(industryContent).map((c) => ({ path: `/industries/${c.slug}`, label: c.short ?? c.slug, group: 'Industries', title: c.metaTitle, description: c.metaDescription })),
    ...cases.map((c) => ({ path: `/case-studies/${c.slug}`, label: c.title, group: 'Case studies', title: '', description: c.excerpt ?? '' })),
  ];
  return <SeoManager pages={pages} />;
}
