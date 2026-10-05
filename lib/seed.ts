import { demoPosts } from '@/content/posts';
import { brandLogos, demoCaseStudies } from '@/lib/data';
import { markdownToHtml } from '@/lib/blog-utils';
import { builtInPartners } from '@/lib/content';
import { count, findOne, insert } from '@/lib/store';

// One-click import of the content that ships with the website (demo blog posts, case studies, partner
// badges and client logos) into the database, so it appears in the admin and can be edited there.
// Safe to run again: anything already imported (same slug or name) is skipped.

export type SeedPlan = { posts: number; caseStudies: number; partners: number; clients: number };

export async function seedPlan(): Promise<SeedPlan> {
  const [p, c, pa, cl] = await Promise.all([count('posts'), count('case_studies'), count('partners'), count('clients')]);
  return { posts: p ? 0 : demoPosts.length, caseStudies: c ? 0 : demoCaseStudies.length, partners: pa ? 0 : builtInPartners.length, clients: cl ? 0 : brandLogos.length };
}

export async function seedAll(): Promise<SeedPlan> {
  const done: SeedPlan = { posts: 0, caseStudies: 0, partners: 0, clients: 0 };
  for (const p of demoPosts) {
    if (await findOne('posts', { slug: p.slug })) continue;
    await insert('posts', {
      title: p.title, slug: p.slug, excerpt: p.excerpt, format: 'html', body: markdownToHtml(p.body), category: p.category, categories: [p.category], tags: [],
      image: p.image, imageAlt: '', author: 'GTech Editorial Team', featured: !!p.featured, status: 'published', date: new Date(p.date).toISOString(), visibility: 'public',
      postFormat: 'standard', allowComments: true, allowPingbacks: true, customFields: [], metaTitle: '', metaDescription: '', focusKeyword: '', canonical: '', noindex: false,
    });
    done.posts++;
  }
  let order = 1;
  for (const c of demoCaseStudies) {
    const x = c as unknown as Record<string, unknown>;
    if (!(await findOne('case_studies', { slug: c.slug }))) {
      await insert('case_studies', {
        title: c.title, slug: c.slug, client: x.client ?? c.title, industry: x.industry ?? '', duration: x.duration ?? '', website: '', excerpt: c.excerpt, image: c.image, imageAlt: '', logo: '',
        services: c.services, metrics: x.metrics ?? [], challenge: x.challenge ?? '', solution: x.solution ?? '', results: x.results ?? [], quote: x.quote ?? { text: '', name: '', role: '' },
        status: 'published', order: order * 10, metaTitle: '', metaDescription: '', focusKeyword: '',
      });
      done.caseStudies++;
    }
    order++;
  }
  order = 1;
  for (const b of builtInPartners) {
    if (!(await findOne('partners', { name: b.name }))) { await insert('partners', { name: b.name, logo: b.logo, url: '', order: order * 10, visible: true }); done.partners++; }
    order++;
  }
  order = 1;
  for (const b of brandLogos) {
    if (!(await findOne('clients', { name: b.name }))) { await insert('clients', { name: b.name, logo: b.logo, url: '', order: order * 10, visible: true }); done.clients++; }
    order++;
  }
  return done;
}
