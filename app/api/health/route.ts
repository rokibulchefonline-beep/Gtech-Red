import { NextResponse } from 'next/server';
import { getDb } from '@/lib/mongo';

export const dynamic = 'force-dynamic';

export async function GET() {
  try {
    await (await getDb()).command({ ping: 1 });
    return NextResponse.json({ status: 'ok', mongodb: 'connected' });
  } catch (e) {
    return NextResponse.json({ status: 'error', detail: (e as Error).message }, { status: 500 });
  }
}
