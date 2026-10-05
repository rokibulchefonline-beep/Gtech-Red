// Browser-safe SEO checks used by the admin editors (a lightweight, Yoast-style analysis).
export type Check = { ok: boolean | 'warn'; label: string; hint?: string };

const words = (s: string) => (s.toLowerCase().match(/[a-z0-9£%'-]+/g) ?? []);

export function analyse(i: { links?: number; title: string; metaTitle?: string; metaDescription?: string; slug?: string; keyword?: string; body?: string; image?: string; imageAlt?: string }): { score: number; checks: Check[] } {
  const kw = (i.keyword ?? '').trim().toLowerCase();
  const title = (i.metaTitle || i.title || '').trim();
  const desc = (i.metaDescription ?? '').trim();
  const body = i.body ?? '';
  const wc = words(body).length;
  const first = body.replace(/^#+ .*$/gm, '').trim().slice(0, 400).toLowerCase();
  const h2s = [...body.matchAll(/^## (.+)$/gm)].map((m) => m[1].toLowerCase());
  const links = i.links ?? (body.match(/\]\((https?:\/\/|\/)[^)]+\)/g) ?? []).length;
  const imgs = (body.match(/!\[[^\]]*\]\([^)]+\)/g) ?? []).length;
  const checks: Check[] = [
    { ok: title.length >= 30 && title.length <= 60 ? true : title.length ? 'warn' : false, label: `SEO title length (${title.length}/60)`, hint: 'Aim for 30 to 60 characters.' },
    { ok: desc.length >= 120 && desc.length <= 160 ? true : desc.length ? 'warn' : false, label: `Meta description length (${desc.length}/160)`, hint: 'Aim for 120 to 160 characters.' },
  ];
  if (i.slug !== undefined) checks.push({ ok: !!i.slug && i.slug.length <= 70, label: 'Short, readable URL slug' });
  if (kw) {
    checks.push(
      { ok: title.toLowerCase().includes(kw), label: 'Focus keyword in the SEO title' },
      { ok: desc.toLowerCase().includes(kw), label: 'Focus keyword in the meta description' },
      { ok: (i.slug ?? '').includes(kw.replace(/\s+/g, '-')), label: 'Focus keyword in the URL slug' },
    );
    if (i.body !== undefined) {
      const dens = wc ? (words(body.toLowerCase()).join(' ').split(kw).length - 1) / wc * 100 : 0;
      checks.push(
        { ok: first.includes(kw), label: 'Focus keyword in the opening paragraph' },
        { ok: h2s.some((h) => h.includes(kw)), label: 'Focus keyword in a subheading' },
        { ok: dens >= 0.3 && dens <= 2.5 ? true : dens > 0 ? 'warn' : false, label: `Keyword density ${dens.toFixed(1)}%`, hint: 'Roughly 0.5% to 2%.' },
      );
    }
  } else checks.push({ ok: false, label: 'Set a focus keyword', hint: 'The phrase you want this page to rank for.' });
  if (i.body !== undefined) {
    checks.push(
      { ok: wc >= 600 ? true : wc >= 300 ? 'warn' : false, label: `Length: ${wc} words`, hint: 'Long-form guides of 800+ words tend to rank best.' },
      { ok: h2s.length >= 3 ? true : h2s.length ? 'warn' : false, label: `Subheadings (${h2s.length})`, hint: 'Use at least 3 H2 headings.' },
      { ok: links > 0, label: `Links in the text (${links})`, hint: 'Link to related services and sources.' },
      { ok: imgs > 0 || !!i.image, label: 'Has an image', hint: i.image && !i.imageAlt ? 'Add alt text to the cover image.' : undefined },
    );
  }
  const pts = checks.reduce((n, c) => n + (c.ok === true ? 1 : c.ok === 'warn' ? 0.5 : 0), 0);
  return { score: Math.round((pts / checks.length) * 100), checks };
}
