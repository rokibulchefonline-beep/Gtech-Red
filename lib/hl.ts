// Heading highlight syntax, shared by the website and the admin editor.
//   "Local SEO [[Agency UK]]"  -> "Agency UK" is highlighted in red
//   "Plain heading [[]]"       -> nothing highlighted
//   no markers                  -> the last 1 to 3 words are highlighted automatically
export type HlPart = { t: string; hl: boolean };

export function parseHeading(input: string): HlPart[] {
  const text = input ?? '';
  if (text.includes('[[')) {
    const out: HlPart[] = [];
    let last = 0;
    for (const m of text.matchAll(/\[\[(.*?)\]\]/g)) {
      if (m.index! > last) out.push({ t: text.slice(last, m.index), hl: false });
      if (m[1]) out.push({ t: m[1], hl: true });
      last = m.index! + m[0].length;
    }
    if (last < text.length) out.push({ t: text.slice(last), hl: false });
    return out.length ? out : [{ t: text.replace(/\[\[|\]\]/g, ''), hl: false }];
  }
  const words = text.trim().split(/\s+/);
  if (words.length < 2) return [{ t: text, hl: false }];
  const n = Math.min(3, Math.max(1, Math.round(words.length / 3)));
  return [{ t: words.slice(0, -n).join(' ') + ' ', hl: false }, { t: words.slice(-n).join(' '), hl: true }];
}
/** The heading without any highlight markers (for page titles, schema and the table of contents). */
export const plainHeading = (s: string) => (s ?? '').replace(/\[\[|\]\]/g, '');
