import { fail, guard, json, str } from '@/lib/admin-api';
import { mailShell, sendMail } from '@/lib/mail';

export const dynamic = 'force-dynamic';

export async function POST(req: Request) {
  const g = await guard(req, 'settings');
  if ('res' in g) return g.res;
  const b = await req.json().catch(() => ({}));
  const to = str(b.to, 160) || g.user.email;
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(to)) return fail('Enter a valid recipient address.');
  const r = await sendMail({ to, subject: 'GTech Digital admin: SMTP test', html: mailShell('SMTP test', '<p>Your email settings work. Lead notifications and replies will be sent from this account.</p>') });
  return r.ok ? json({ ok: true }) : fail(r.error ?? 'Could not send.', 502);
}
