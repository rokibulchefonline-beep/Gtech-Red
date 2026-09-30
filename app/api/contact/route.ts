import { NextResponse } from 'next/server';
import { getDb } from '@/lib/mongo';

export const dynamic = 'force-dynamic';

// Accepts both forms: the /contact proposal form (name, business, budget) and the
// home-page inquiry form (name, company, phone, email, postcode, service).
export async function POST(req: Request) {
  const body = await req.json().catch(() => ({}));
  const v = (k: string) => String(body[k] ?? '').trim();

  if (v('website')) return NextResponse.json({ ok: true }); // honeypot

  const name = v('name') || [v('firstName'), v('lastName')].filter(Boolean).join(' ');
  const business = v('business') || v('company');
  const phone = [v('countryCode'), v('phone')].filter(Boolean).join(' ');
  const email = v('email');
  const service = v('service');

  if (!name || !business || !phone || !service || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    return NextResponse.json({ ok: false, error: 'Fill all required fields with a valid email.' }, { status: 400 });
  }
  if (v('source') !== 'inquiry' && !v('budget')) {
    return NextResponse.json({ ok: false, error: 'Fill all required fields with a valid email.' }, { status: 400 });
  }

  try {
    const db = await getDb();
    await db.collection('leads').insertOne({
      name, business, email, phone, service,
      budget: v('budget'), designation: v('designation'), companySize: v('size'),
      postcode: v('postcode'), message: v('message'), source: v('source') || 'contact', created_at: new Date(),
    });
    return NextResponse.json({ ok: true });
  } catch {
    return NextResponse.json({ ok: false, error: 'Could not save request. Try again later.' }, { status: 500 });
  }
}
