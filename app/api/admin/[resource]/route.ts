import { fail, guard, json, route } from '@/lib/admin-api';
import { listResource, resources } from '@/lib/admin-resources';
import { insert } from '@/lib/store';

export const dynamic = 'force-dynamic';
type Ctx = { params: Promise<{ resource: string }> };

async function GET_(req: Request, { params }: Ctx) {
  const r = resources[(await params).resource];
  if (!r) return fail('Unknown resource.', 404);
  const g = await guard(req, r.perm);
  if ('res' in g) return g.res;
  const u = new URL(req.url);
  const filter: Record<string, string> = {};
  for (const k of ['status', 'kind', 'category']) if (u.searchParams.get(k)) filter[k] = u.searchParams.get(k)!;
  return json({ ok: true, ...(await listResource(r, { search: u.searchParams.get('q') ?? '', filter, page: Number(u.searchParams.get('page')) || 1, size: Number(u.searchParams.get('size')) || 50 })) });
}

async function POST_(req: Request, { params }: Ctx) {
  const r = resources[(await params).resource];
  if (!r || r.noCreate || r.keyed) return fail('Not allowed.', 405);
  const g = await guard(req, r.perm);
  if ('res' in g) return g.res;
  const body = await req.json().catch(() => ({}));
  const out = await r.clean(body, { user: g.user, creating: true });
  if ('error' in out) return fail(String(out.error));
  const doc = await insert(r.coll, out);
  return json({ ok: true, doc: (r.view ?? ((d) => d))(doc) }, 201);
}

export const GET = route(GET_);

export const POST = route(POST_);
