import { json, route } from '@/lib/admin-api';
import { destroySession } from '@/lib/auth';

export const dynamic = 'force-dynamic';
async function POST_() { await destroySession(); return json({ ok: true }); }

export const POST = route(POST_);
