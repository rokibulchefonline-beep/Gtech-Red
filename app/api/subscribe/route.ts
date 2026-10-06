import { route } from '@/lib/admin-api';
import { NextResponse } from 'next/server';
import { withDb } from '@/lib/mongo';
import { laravel } from '@/lib/store';
import { forward } from '@/lib/forward';

export const dynamic = 'force-dynamic';

// Newsletter sign-ups from the blog. Saved to the `subscribers` collection.
async function POST_(req: Request) {
  const body = await req.json().catch(() => ({}));
  const lv = laravel();
  if (lv) return forward(`${lv.url}/api/v1/subscribe`, body, req);
  const email = String(body.email ?? '').trim().toLowerCase();
  if (body.website) return NextResponse.json({ ok: true }); // honeypot
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return NextResponse.json({ ok: false, error: 'Enter a valid email address.' }, { status: 400 });
  try {
    await withDb((db) => db.collection('subscribers').updateOne({ email }, { $setOnInsert: { email, source: 'blog', created_at: new Date() } }, { upsert: true }));
    return NextResponse.json({ ok: true });
  } catch {
    return NextResponse.json({ ok: false, error: 'Could not subscribe right now. Try again later.' }, { status: 500 });
  }
}

export const POST = route(POST_);
