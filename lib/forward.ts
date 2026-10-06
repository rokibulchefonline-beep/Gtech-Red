import { NextResponse } from 'next/server';

/** With the Laravel backend, the form is saved and emailed there. */
export async function forward(url: string, body: unknown, req: Request) {
  const res = await fetch(url, { method: 'POST', headers: { 'content-type': 'application/json', accept: 'application/json', 'x-forwarded-for': req.headers.get('cf-connecting-ip') ?? req.headers.get('x-forwarded-for') ?? '' }, body: JSON.stringify(body) });
  const json = await res.json().catch(() => ({ ok: false, error: 'Could not send right now. Try again later.' }));
  return NextResponse.json(json, { status: res.status === 429 ? 429 : res.ok ? 200 : res.status >= 500 ? 500 : 400 });
}
