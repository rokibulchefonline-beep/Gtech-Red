import { json, guard, route } from '@/lib/admin-api';
import { seedAll, seedPlan } from '@/lib/seed';

export const dynamic = 'force-dynamic';

async function GET_(req: Request) {
  const g = await guard(req, 'content');
  if ('res' in g) return g.res;
  return json({ ok: true, plan: await seedPlan() });
}
async function POST_(req: Request) {
  const g = await guard(req, 'content');
  if ('res' in g) return g.res;
  return json({ ok: true, imported: await seedAll() });
}
export const GET = route(GET_);
export const POST = route(POST_);
