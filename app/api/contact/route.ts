import { route } from '@/lib/admin-api';
import { NextResponse } from 'next/server';
import { esc, mailShell, sendMail } from '@/lib/mail';
import { getSettings } from '@/lib/settings';
import { forward } from '@/lib/forward';
import { insert, laravel } from '@/lib/store';

export const dynamic = 'force-dynamic';

// Accepts both forms: the /contact proposal form (name, business, budget) and the
// home-page inquiry form (name, company, phone, email, postcode, service).
// Leads are saved to the `leads` collection (managed in Admin > Leads) and emailed through SMTP.
async function POST_(req: Request) {
  const body = await req.json().catch(() => ({}));
  const lv = laravel();
  if (lv) return forward(`${lv.url}/api/v1/contact`, body, req);
  const v = (k: string) => String(body[k] ?? '').trim();

  if (v('hp_field')) return NextResponse.json({ ok: true }); // honeypot

  const name = v('name') || [v('firstName'), v('lastName')].filter(Boolean).join(' ');
  const business = v('business') || v('company');
  const phone = [v('countryCode'), v('phone')].filter(Boolean).join(' ');
  const email = v('email');
  const service = v('service');

  if (!name || !business || !phone || !service || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    return NextResponse.json({ ok: false, error: 'Fill all required fields with a valid email.' }, { status: 400 });
  }
  if (v('source') !== 'inquiry' && !v('budget')) {
    return NextResponse.json({ ok: false, error: 'Fill all required fields with a valid email.' }, { status: 400 });
  }

  const lead = {
    name: name.slice(0, 120), business: business.slice(0, 160), email: email.slice(0, 160), phone: phone.slice(0, 40), service: service.slice(0, 120),
    budget: v('budget').slice(0, 60), designation: v('designation').slice(0, 80), companySize: v('size').slice(0, 40),
    website: v('website').slice(0, 200), postcode: v('postcode').slice(0, 20), message: v('message').slice(0, 3000),
    source: v('source') || 'contact', status: 'new', notes: '', assignee: '',
  };
  try {
    await insert('leads', lead);
  } catch {
    return NextResponse.json({ ok: false, error: 'Could not save request. Try again later.' }, { status: 500 });
  }

  // Email is best effort: the lead is already safely stored.
  try {
    const s = await getSettings();
    const rows = Object.entries({ Name: lead.name, Business: lead.business, Email: lead.email, Phone: lead.phone, Service: lead.service, Budget: lead.budget, Website: lead.website, Message: lead.message })
      .filter(([, x]) => x).map(([k, x]) => `<tr><td style="padding:6px 12px 6px 0;color:#666">${k}</td><td style="padding:6px 0"><b>${esc(String(x))}</b></td></tr>`).join('');
    const to = s.smtp.notifyTo || s.contact.email;
    if (to) await sendMail({ to, subject: `New lead: ${lead.name} (${lead.service})`, html: mailShell('New website lead', `<table>${rows}</table>`), replyTo: lead.email });
    if (s.smtp.autoReply) {
      await sendMail({ to: lead.email, subject: `Thanks, ${lead.name.split(' ')[0]}. We have your enquiry`, html: mailShell(`Thanks for contacting ${s.general.siteName}`,
        `<p>Hi ${esc(lead.name.split(' ')[0])},</p><p>We have received your enquiry about <b>${esc(lead.service)}</b> and will send your free proposal within one working day.</p><p>${esc(s.general.siteName)}<br>${esc(s.contact.phone)}</p>`) });
    }
  } catch { /* ignore */ }
  return NextResponse.json({ ok: true });
}

export const POST = route(POST_);

