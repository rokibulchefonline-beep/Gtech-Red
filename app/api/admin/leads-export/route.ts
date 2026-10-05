import { guard } from '@/lib/admin-api';
import { list } from '@/lib/store';

export const dynamic = 'force-dynamic';
const cell = (v: unknown) => { let s = String(v ?? ''); if (/^[=+\-@]/.test(s)) s = "'" + s; return `"${s.replace(/"/g, '""')}"`; }; // blocks CSV formula injection

export async function GET(req: Request) {
  const g = await guard(req, 'leads');
  if ('res' in g) return g.res;
  const rows = await list('leads', { sort: { createdAt: -1 }, limit: 5000 });
  const cols = ['createdAt', 'status', 'name', 'business', 'email', 'phone', 'service', 'budget', 'website', 'source', 'assignee', 'value', 'notes'];
  const csv = [cols.join(','), ...rows.map((r) => cols.map((c) => cell(c === 'createdAt' ? new Date(r[c]).toISOString() : r[c])).join(','))].join('\n');
  return new Response(csv, { headers: { 'Content-Type': 'text/csv; charset=utf-8', 'Content-Disposition': `attachment; filename="leads-${new Date().toISOString().slice(0, 10)}.csv"`, 'Cache-Control': 'no-store' } });
}
