import { decrypt, getSettings } from '@/lib/settings';

export type MailResult = { ok: boolean; error?: string };
export const esc = (s: string) => s.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]!));

/** Send through the SMTP account configured in Admin > Settings > Email. Never throws. */
export async function sendMail(opts: { to: string; subject: string; html: string; text?: string; replyTo?: string }): Promise<MailResult> {
  try {
    const { smtp } = await getSettings();
    if (!smtp.host || !smtp.fromEmail) return { ok: false, error: 'SMTP is not configured.' };
    const nodemailer = await import('nodemailer');
    const t = nodemailer.createTransport({
      host: smtp.host, port: smtp.port, secure: smtp.secure,
      auth: smtp.user ? { user: smtp.user, pass: await decrypt(smtp.pass) } : undefined,
      connectionTimeout: 10000, greetingTimeout: 10000, socketTimeout: 15000,
    });
    await t.sendMail({ from: `"${smtp.fromName.replace(/"/g, '')}" <${smtp.fromEmail}>`, to: opts.to, subject: opts.subject, html: opts.html, text: opts.text, replyTo: opts.replyTo });
    return { ok: true };
  } catch (e) {
    return { ok: false, error: e instanceof Error ? e.message : 'Could not send email.' };
  }
}

export const mailShell = (title: string, body: string) =>
  `<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;color:#161616"><div style="background:#e8202f;color:#fff;padding:18px 24px;font-size:18px;font-weight:700">${esc(title)}</div><div style="padding:24px;border:1px solid #eee;border-top:0;line-height:1.6">${body}</div></div>`;
