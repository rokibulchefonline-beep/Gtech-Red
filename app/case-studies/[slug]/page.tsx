import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import PageHead from '@/components/PageHead';
import { getDoc, listDocs } from '@/lib/mongo';

type Props = { params: Promise<{ slug: string }> };

// Pre-rendered at build time and served as static HTML (no per-request rendering).
export const dynamicParams = false;

export async function generateStaticParams() {
  return (await listDocs('case_studies', 200)).map((d) => ({ slug: d.slug }));
}

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
