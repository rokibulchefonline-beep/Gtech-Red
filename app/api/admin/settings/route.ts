import { fail, guard, json } from '@/lib/admin-api';
import { getSettings, redact, saveSettings } from '@/lib/settings';

export const dynamic = 'force-dynamic';

export async function GET(req: Request) {
  const g = await guard(req, 'settings');
  if ('res' in g) return g.res;
  return json({ ok: true, settings: redact(await getSettings()) });
}

export async function PUT(req: Request) {
  const g = await guard(req, 'settings');
  if ('res' in g) return g.res;
  const b = await req.json().catch(() => null);
  if (!b || typeof b !== 'object') return fail('Invalid settings.');
  const hook = b.publish?.deployHook;
  if (hook && !/^https:\/\//.test(hook)) return fail('The deploy hook must be an https:// URL.');
  const port = Number(b.smtp?.port);
  if (b.smtp && (!Number.isInteger(port) || port < 1 || port > 65535)) return fail('Enter a valid SMTP port.');
  await saveSettings(b);
  return json({ ok: true, settings: redact(await getSettings()) });
}
