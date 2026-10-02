import type { Metadata } from 'next';
import CaseCard from '@/components/CaseCard';
import PageHead from '@/components/PageHead';
import { listDocs } from '@/lib/mongo';

export const metadata: Metadata = { title: 'Case Studies' };

export default async function Page() {
  const docs = await listDocs('case_studies');
  return (
    <>
      <PageHead title="Digital Marketing Case Studies of GTech Digital" sub="GTech Digital case studies show measurable results from SEO, Google Ads, social media, web design and custom software projects for UK businesses." />
      <section className="wrap block"><div className="case-grid">{docs.map((d, i) => <CaseCard key={d.slug} doc={d} index={i} />)}</div></section>
    </>
  );
}
