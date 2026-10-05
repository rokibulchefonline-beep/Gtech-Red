import { findOne } from '@/lib/store';

export const dynamic = 'force-dynamic';

export async function GET(_: Request, { params }: { params: Promise<{ id: string }> }) {
  const m = await findOne('media', { _id: (await params).id }).catch(() => null);
  if (!m) return new Response('Not found', { status: 404 });
  const bytes = Uint8Array.from(atob(m.data), (c) => c.charCodeAt(0));
  return new Response(bytes, { headers: {
    'Content-Type': m.type, 'Cache-Control': 'public, max-age=31536000, immutable',
    'X-Content-Type-Options': 'nosniff', 'Content-Security-Policy': "default-src 'none'; style-src 'unsafe-inline'; sandbox",
  } });
}
