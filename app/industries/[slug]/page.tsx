import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import IndustryPage from '@/components/industry/IndustryPage';
import { industryContent } from '@/content/industries';

type Props = { params: Promise<{ slug: string }> };

export const dynamicParams = false;

export function generateStaticParams() {
  return Object.keys(industryContent).map((slug) => ({ slug }));
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const c = industryContent[(await params).slug];
  if (!c) return {};
  return { title: { absolute: c.metaTitle }, description: c.metaDescription, alternates: { canonical: `/industries/${c.slug}` } };
}

export default async function Industry({ params }: Props) {
  const c = industryContent[(await params).slug];
  if (!c) notFound();
  return <IndustryPage c={c} />;
}
