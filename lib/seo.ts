import type { Metadata } from 'next';
import { encodeSeoId } from '@/lib/site-pages';
import { findOne } from '@/lib/store';

/** Merge admin SEO overrides (Admin > SEO) over a page's built-in metadata. Falls back silently. */
export async function seoFor(path: string, base: Metadata = {}): Promise<Metadata> {
  try {
    const o = await findOne('seo', { _id: encodeSeoId(path) });
    if (!o) return base;
    const title = o.title || (typeof base.title === 'object' && base.title && 'absolute' in base.title ? base.title.absolute : undefined);
    const description = o.description || base.description || undefined;
    return {
      ...base,
      ...(o.title && { title: { absolute: o.title } }),
      ...(o.description && { description: o.description }),
      alternates: { ...base.alternates, ...(o.canonical && { canonical: o.canonical }) },
      ...(o.noindex && { robots: { index: false, follow: true } }),
      ...(o.ogImage && { openGraph: { title, description, images: [o.ogImage] } }),
    };
  } catch { return base; }
}
