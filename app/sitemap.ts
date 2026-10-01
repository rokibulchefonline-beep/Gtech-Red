import type { MetadataRoute } from 'next';
import { industryContent } from '@/content/industries';
import { serviceContent } from '@/content/services';
import { getPosts } from '@/lib/blog';
import { listDocs } from '@/lib/mongo';

const base = 'https://www.gtechdigital.co.uk';

export const dynamic = 'force-dynamic';

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const pages = ['', '/services', '/about', '/contact', '/case-studies', '/industries', '/blogs', '/privacy-policy', '/terms', '/cookie-policy'];
  const [posts, cases] = await Promise.all([getPosts(), listDocs('case_studies', 200)]);
  return [
    ...pages.map((p) => ({ url: `${base}${p}`, changeFrequency: 'monthly' as const, priority: p === '' ? 1 : 0.7 })),
    ...Object.keys(serviceContent).map((s) => ({ url: `${base}/services/${s}`, changeFrequency: 'monthly' as const, priority: 0.8 })),
    ...Object.keys(industryContent).map((s) => ({ url: `${base}/industries/${s}`, changeFrequency: 'monthly' as const, priority: 0.7 })),
    ...posts.map((p) => ({ url: `${base}/blogs/${p.slug}`, lastModified: p.date, changeFrequency: 'yearly' as const, priority: 0.6 })),
    ...cases.map((c) => ({ url: `${base}/case-studies/${c.slug}`, changeFrequency: 'yearly' as const, priority: 0.5 })),
  ];
}
