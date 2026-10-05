import { notFound } from 'next/navigation';
import PageEditor, { type PageBase } from '@/components/admin/PageEditor';
import { industryContent } from '@/content/industries';
import { serviceContent } from '@/content/services';

export default async function PageEdit({ params }: { params: Promise<{ kind: string; slug: string }> }) {
  const { kind, slug } = await params;
  const c = (kind === 'service' ? serviceContent : kind === 'industry' ? industryContent : {})[slug];
  if (!c) notFound();
  const base: PageBase = {
    kind: kind as 'service' | 'industry', slug, name: c.short ?? c.slug, metaTitle: c.metaTitle, metaDescription: c.metaDescription,
    hero: { keyword: c.hero.keyword ?? c.short ?? slug, lead: c.hero.lead, points: c.hero.points },
    sections: c.sections.map((s) => {
      const x = s as unknown as Record<string, unknown>;
      return { id: s.id, type: s.type, nav: x.nav as string | undefined, heading: x.heading as string | undefined, paras: x.paras as string[] | undefined, bullets: x.bullets as string[] | undefined, text: x.text as string | undefined };
    }),
    faqs: c.faqs,
  };
  return <PageEditor base={base} />;
}
