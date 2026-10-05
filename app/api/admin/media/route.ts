import { fail, guard, json, route } from '@/lib/admin-api';
import { insert, list } from '@/lib/store';

export const dynamic = 'force-dynamic';
const TYPES = ['image/png', 'image/jpeg', 'image/webp', 'image/gif', 'image/svg+xml'];
const MAX = 1_500_000;

async function GET_(req: Request) {
  const g = await guard(req, 'content');
  if ('res' in g) return g.res;
  const rows = await list('media', { sort: { createdAt: -1 }, limit: 200 });
  return json({ ok: true, rows: rows.map(({ data, ...m }) => { void data; return { ...m, url: `/api/media/${m._id}` }; }) });
}

// Images are stored in MongoDB (base64) so uploads work on any host, including Cloudflare Workers.
async function POST_(req: Request) {
  const g = await guard(req, 'content');
  if ('res' in g) return g.res;
  const f = (await req.formData().catch(() => null))?.get('file');
  if (!(f instanceof File)) return fail('Choose an image to upload.');
  if (!TYPES.includes(f.type)) return fail('Use a PNG, JPG, WebP, GIF or SVG image.');
  if (f.size > MAX) return fail('Images must be under 1.5 MB. Compress it first (WebP works well).');
  const buf = new Uint8Array(await f.arrayBuffer());
  let bin = '';
  for (let i = 0; i < buf.length; i += 8192) bin += String.fromCharCode(...buf.subarray(i, i + 8192));
  const data = btoa(bin);
  const m = await insert('media', { name: f.name.slice(0, 120), type: f.type, size: f.size, data, by: g.user.email });
  return json({ ok: true, media: { _id: m._id, name: m.name, url: `/api/media/${m._id}` } }, 201);
}

export const GET = route(GET_);

export const POST = route(POST_);
