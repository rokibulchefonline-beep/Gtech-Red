import { NextResponse } from 'next/server';
import { withDb } from '@/lib/mongo';

export const dynamic = 'force-dynamic';

// Newsletter sign-ups from the blog. Saved to the `subscribers` collection.
export async function POST(req: Request) {
  const body = await req.json().catch(() => ({}));
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
