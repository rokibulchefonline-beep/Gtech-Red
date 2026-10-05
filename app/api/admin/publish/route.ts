import { fail, guard, json } from '@/lib/admin-api';
import { getSettings } from '@/lib/settings';

export const dynamic = 'force-dynamic';

// The public site is pre-rendered. Content changes go live after a rebuild, which this triggers
// through a deploy hook (Cloudflare: Workers & Pages > project > Settings > Builds > Deploy hooks).
export async function POST(req: Request) {
  const g = await guard(req, 'content');
  if ('res' in g) return g.res;
  const hook = (await getSettings()).publish.deployHook || process.env.DEPLOY_HOOK_URL || '';
  if (!hook) return fail('No deploy hook is set. Add one in Settings > Publishing.', 400);
  try {
    const r = await fetch(hook, { method: 'POST' });
    return r.ok ? json({ ok: true }) : fail(`The deploy hook returned ${r.status}.`, 502);
  } catch { return fail('Could not reach the deploy hook.', 502); }
}
