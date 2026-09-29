import type { Metadata } from 'next';
import DocCards from '@/components/DocCards';
import PageHead from '@/components/PageHead';
import { listDocs } from '@/lib/mongo';

export const metadata: Metadata = { title: 'Case Studies' };
export const dynamic = 'force-dynamic';

export default async function Page() {
  const docs = await listDocs('case_studies');
  return (
    <>
      <PageHead title="Case Studies" />
      <section className="wrap block"><DocCards docs={docs} base="/case-studies" /></section>
    </>
  );
}
