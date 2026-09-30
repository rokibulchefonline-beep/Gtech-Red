import type { Metadata } from 'next';
import CaseCard from '@/components/CaseCard';
import PageHead from '@/components/PageHead';
import { listDocs } from '@/lib/mongo';

export const metadata: Metadata = { title: 'Case Studies' };
export const dynamic = 'force-dynamic';

export default async function Page() {
  const docs = await listDocs('case_studies');
  return (
    <>
      <PageHead title="Case Studies" />
      <section className="wrap block"><div className="case-grid">{docs.map((d, i) => <CaseCard key={d.slug} doc={d} index={i} />)}</div></section>
    </>
  );
}
