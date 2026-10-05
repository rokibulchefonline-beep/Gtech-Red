import { fail, json } from '@/lib/admin-api';
import { currentUser } from '@/lib/auth';

export const dynamic = 'force-dynamic';
export async function GET() { const u = await currentUser(); return u ? json({ ok: true, user: u }) : fail('Not signed in.', 401); }
