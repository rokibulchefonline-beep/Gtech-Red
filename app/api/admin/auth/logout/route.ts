import { json } from '@/lib/admin-api';
import { destroySession } from '@/lib/auth';

export const dynamic = 'force-dynamic';
export async function POST() { await destroySession(); return json({ ok: true }); }
