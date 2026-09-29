import { NextResponse } from 'next/server';
import { getDb } from '@/lib/mongo';

export const dynamic = 'force-dynamic';

const required = ['name', 'business', 'email', 'phone', 'service', 'budget'] as const;

export async function POST(req: Request) {
  const body = await req.json().catch(() => ({}));
  const v = (k: string) => String(body[k] ?? '').trim();

  if (v('website')) return NextResponse.json({ ok: true }); // honeypot

  if (required.some((k) => !v(k)) || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v('email'))) {
    return NextResponse.json({ ok: false, error: 'Fill all required fields with a valid email.' }, { status: 400 });
  }

  try {
    const db = await getDb();
    await db.collection('leads').insertOne({
      name: v('name'), business: v('business'), email: v('email'), phone: v('phone'),
      service: v('service'), budget: v('budget'), message: v('message'), created_at: new Date(),
    });
    return NextResponse.json({ ok: true });
  } catch {
    return NextResponse.json({ ok: false, error: 'Could not save request. Try again later.' }, { status: 500 });
  }
}
