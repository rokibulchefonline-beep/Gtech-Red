import SeoAudit from '@/components/admin/SeoAudit';
import { seoMap } from '@/content/seo-map';
import { auditSite } from '@/lib/seo-audit';

export default async function SeoAuditPage() {
  const a = await auditSite();
  return <SeoAudit data={a} map={Object.fromEntries(Object.entries(seoMap).map(([k, v]) => [k, { kw: v.kw, sec: v.sec, ent: v.ent }]))} />;
}
