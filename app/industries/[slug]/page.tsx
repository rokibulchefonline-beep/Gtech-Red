import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import IndustryPage from '@/components/industry/IndustryPage';
import { industryContent } from '@/content/industries';
import { withOverrides } from '@/lib/content';
import { seoFor } from '@/lib/seo';

type Props = { params: Promise<{ slug: string }> };

// Pre-rendered at build time and served as static HTML (no per-request rendering).
export const dynamicParams = false;

export function generateStaticParams() {
  return Object.keys(industryContent).map((slug) => ({ slug }));
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const base = industryContent[(await params).slug];
  if (!base) return {};
  const c = await withOverrides('industry', base);
  return seoFor(`/industries/${c.slug}`, { title: { absolute: c.metaTitle }, description: c.metaDescription, alternates: { canonical: `/industries/${c.slug}` } });
}

export default async function Industry({ params }: Props) {
  const c = industryContent[(await params).slug];
  if (!c) notFound();
  return <IndustryPage c={await withOverrides('industry', c)} />;
}
