import { NextResponse } from 'next/server';
import { withDb } from '@/lib/mongo';

export const dynamic = 'force-dynamic';

export async function GET() {
  try {
    await withDb((db) => db.command({ ping: 1 }));
    return NextResponse.json({ status: 'ok', mongodb: 'connected' });
  } catch (e) {
    return NextResponse.json({ status: 'error', detail: (e as Error).message }, { status: 500 });
  }
}
