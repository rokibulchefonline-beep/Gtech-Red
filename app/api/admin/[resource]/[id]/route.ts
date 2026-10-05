import { fail, guard, json, route } from '@/lib/admin-api';
import { resources } from '@/lib/admin-resources';
import { findOne, insert, remove, update } from '@/lib/store';

export const dynamic = 'force-dynamic';
type Ctx = { params: Promise<{ resource: string; id: string }> };

async function GET_(req: Request, { params }: Ctx) {
  const { resource, id } = await params;
  const r = resources[resource];
  if (!r) return fail('Unknown resource.', 404);
  const g = await guard(req, r.perm);
  if ('res' in g) return g.res;
  const doc = await findOne(r.coll, { _id: id });
  if (!doc) return r.keyed ? json({ ok: true, doc: null }) : fail('Not found.', 404);
  return json({ ok: true, doc: (r.view ?? ((d) => d))(doc) });
}

async function PUT_(req: Request, { params }: Ctx) {
  const { resource, id } = await params;
  const r = resources[resource];
  if (!r) return fail('Unknown resource.', 404);
  const g = await guard(req, r.perm);
  if ('res' in g) return g.res;
  const existing = await findOne(r.coll, { _id: id });
  if (!existing && !r.keyed) return fail('Not found.', 404);
  const out = await r.clean(await req.json().catch(() => ({})), { user: g.user, existing, creating: false });
  if ('error' in out) return fail(String(out.error));
  if (existing) await update(r.coll, id, out); else await insert(r.coll, { ...out, _id: id });
  return json({ ok: true, doc: (r.view ?? ((d) => d))({ ...existing, ...out, _id: id }) });
}

async function DELETE_(req: Request, { params }: Ctx) {
  const { resource, id } = await params;
  const r = resources[resource];
  if (!r) return fail('Unknown resource.', 404);
  const g = await guard(req, r.perm);
  if ('res' in g) return g.res;
  const existing = await findOne(r.coll, { _id: id });
  if (!existing) return fail('Not found.', 404);
  const blocked = await r.canDelete?.(existing, { user: g.user, existing, creating: false });
  if (blocked) return fail(blocked, 409);
  await remove(r.coll, id);
  return json({ ok: true });
}

export const GET = route(GET_);

export const PUT = route(PUT_);

export const DELETE = route(DELETE_);
