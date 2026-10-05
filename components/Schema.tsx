import { buildGraph, toScript, validateCustomSchema, type JsonLd } from '@/lib/schema';
import { findOne } from '@/lib/store';
import { encodeSeoId } from '@/lib/site-pages';
import { getSettings } from '@/lib/settings';

/**
 * Emits the page's connected JSON-LD graph. Admin > SEO can switch the automatic schema off for a
 * page or add custom JSON-LD that is emitted alongside it.
 */
export default async function Schema({ path, nodes }: { path: string; nodes: JsonLd[] }) {
  const [s, o] = await Promise.all([getSettings(), findOne('seo', { _id: encodeSeoId(path) }).catch(() => null)]);
  const custom = o?.schemaCustom && !validateCustomSchema(o.schemaCustom) ? JSON.parse(o.schemaCustom) : null;
  return (
    <>
      {!o?.schemaOff && <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: toScript(buildGraph(s, nodes)) }} />}
      {custom && <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: toScript(custom) }} />}
    </>
  );
}
