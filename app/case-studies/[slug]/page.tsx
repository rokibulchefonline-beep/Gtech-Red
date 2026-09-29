import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import PageHead from '@/components/PageHead';
import { getDoc } from '@/lib/mongo';

type Props = { params: Promise<{ slug: string }> };

export const dynamic = 'force-dynamic';

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const doc = await getDoc('case_studies', (await params).slug);
  return { title: doc?.title, description: doc?.excerpt };
}

export default async function Page({ params }: Props) {
  const doc = await getDoc('case_studies', (await params).slug);
  if (!doc) notFound();
  return (
    <>
      <PageHead title={doc.title} back={{ href: '/case-studies', label: 'Case Studies' }} />
      <article className="wrap block prose" style={{ whiteSpace: 'pre-line' }}>{doc.body}</article>
    </>
  );
}
