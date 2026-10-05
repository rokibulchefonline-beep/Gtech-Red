import { NextResponse } from 'next/server';
import { scoped, withDb } from '@/lib/mongo';

export const dynamic = 'force-dynamic';

// Database health check with timings. Open /api/health to see whether the site can reach MongoDB
// and how long a connection and a query take (slow connections are the usual cause of save errors).
export async function GET() {
  const t0 = Date.now();
  try {
    const out = await scoped(async () => {
      await withDb((db) => db.command({ ping: 1 }));
      const connected = Date.now() - t0;
      const t1 = Date.now();
      await withDb((db) => db.collection('users').countDocuments({}));
      return { connectMs: connected, secondQueryMs: Date.now() - t1 };
    });
    return NextResponse.json({ status: 'ok', mongodb: 'connected', ...out });
  } catch (e) {
    return NextResponse.json({ status: 'error', detail: (e as Error).message, afterMs: Date.now() - t0 }, { status: 500 });
  }
}
