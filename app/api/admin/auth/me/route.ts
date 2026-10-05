import { fail, json, route } from '@/lib/admin-api';
import { currentUser } from '@/lib/auth';

export const dynamic = 'force-dynamic';
async function GET_() { const u = await currentUser(); return u ? json({ ok: true, user: u }) : fail('Not signed in.', 401); }

export const GET = route(GET_);
