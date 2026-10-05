import type { Metadata } from 'next';
import CaseCard from '@/components/CaseCard';
import PageHead from '@/components/PageHead';
import { listDocs } from '@/lib/mongo';
import { seoFor } from '@/lib/seo';
import Schema from '@/components/Schema';
import { BASE, breadcrumbNode, itemListNode, pageNode } from '@/lib/schema';

const baseMeta: Metadata = {
  title: { absolute: 'Digital Marketing Case Studies | GTech Digital' },
  description: 'Real results from SEO, Google Ads, social media, web design and software projects: more traffic, leads, revenue and sales for UK businesses.',
  alternates: { canonical: '/case-studies' },
};
export const generateMetadata = () => seoFor('/case-studies', baseMeta);

export default async function Page() {
  const docs = await listDocs('case_studies');
  return (
    <>
      <Schema path="/case-studies" nodes={[
        pageNode({ path: '/case-studies', type: 'CollectionPage', name: 'Digital Marketing Case Studies of GTech Digital', description: baseMeta.description as string, mainEntity: `${BASE}/case-studies#list` }),
        breadcrumbNode('/case-studies', [['Case Studies', '/case-studies']]),
        itemListNode('/case-studies', 'GTech Digital case studies', docs.map((d) => [d.title, `/case-studies/${d.slug}`] as [string, string])),
      ]} />
      <PageHead title="Digital Marketing Case Studies of GTech Digital" sub="GTech Digital case studies show measurable results from SEO, Google Ads, social media, web design and custom software projects for UK businesses." />
      <section className="wrap block"><div className="case-grid">{docs.map((d, i) => <CaseCard key={d.slug} doc={d} index={i} />)}</div></section>
    </>
  );
}
