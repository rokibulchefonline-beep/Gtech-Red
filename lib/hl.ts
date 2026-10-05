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
  const [pre, hl, post] = pickHighlight(text.trim());
  if (!hl) return [{ t: text, hl: false }];
  return [...(pre ? [{ t: pre, hl: false }] : []), { t: hl, hl: true }, ...(post ? [{ t: post, hl: false }] : [])];
}

const SMALL = new Set(['a', 'an', 'the', 'to', 'of', 'at', 'in', 'on', 'for', 'with', 'by', 'and', 'or', 'your', 'our', 'we', 'you', 'is', 'are', 'that', 'it']);
const wc = (s: string) => s.split(/\s+/).filter(Boolean).length;

/** Splits a heading into [before, highlighted, after] so the highlight lands on the keyword or the payoff phrase. */
export function pickHighlight(h: string): [string, string, string] {
  const cut = (hl: string): [string, string, string] => {
    const i = h.indexOf(hl);
    return i < 0 ? ['', '', ''] : [h.slice(0, i), hl, h.slice(i + hl.length)];
  };
  let m: RegExpMatchArray | null;
  if (wc(h) < 2) return ['', '', ''];
  // "Frequently Asked Questions About X"
  if ((m = h.match(/^(Frequently Asked Questions)\b/i))) return cut(m[1]);
  // "How Much Does X Cost?", "What's Included in Our X Services"
  if ((m = h.match(/^How Much Does (.+) Cost\??$/i))) return cut(trimSmall(m[1]));
  if ((m = h.match(/^What's Included in Our (.+?)(?: Services)?$/i))) return cut(m[1]);
  // "X Case Studies and Results", "X Client Reviews and Testimonials", "X Compared"
  if ((m = h.match(/^(.+?) (?:Case Studies and Results|Client Reviews and Testimonials)$/i))) return cut(m[1]);
  if ((m = h.match(/^(.+?) Compared$/i))) return cut(m[1]);
  // "X: details" -> the keyword before the colon (or the payoff after it when the lead-in is long)
  if ((m = h.match(/^([^:]+):\s*(.+)$/))) return wc(m[1]) <= 6 ? cut(m[1]) : cut(trimSmall(m[2]));
  // "What Is X and How Does It Work?", "What Are X?"
  if ((m = h.match(/^What (?:Is|Are) (.+?)(?: and How\b.*|\?)$/i))) return cut(trimSmall(m[1]));
  if ((m = h.match(/^(?:Why|How|When) (?:Choose|Does|Do|Can|Should) (.+?)\??$/i)) && wc(m[1]) <= 4) return cut(m[1].replace(/\?$/, ''));
  // "Our X Process, Step by Step" / "X Results and Key Statistics" / "X Services by Industry"
  if ((m = h.match(/^Our (.+?) Process, Step by Step$/i))) return cut(m[1]);
  if ((m = h.match(/^(.+?) (?:Results and Key Statistics|Services by Industry|Services for Every Industry)$/i))) return cut(m[1]);
  // "X We Serve"
  if ((m = h.match(/^(.+?) We (?:Serve|Work With|Help)$/i))) return cut(m[1]);
  // "Our Results in Numbers" -> the noun after "Our"
  if ((m = h.match(/^Our (\w+)\b/)) && wc(h) <= 4) return cut(m[1]);
  // payoff after "Into", "Instead of", "Without", "Not"
  if ((m = h.match(/\b(?:Into|Instead of|Without|Not Just)\s+(.+?)\??$/i))) return cut(trimSmall(m[1]).replace(/\?$/, '') || m[1]);
  // "List, Of and Things" -> the keyword before the first comma; "A, Payoff" -> the payoff after it
  if ((m = h.match(/^([^,]+),\s*(.+)$/))) {
    if (/\band\b/.test(m[2]) || wc(m[2]) > 4) { const k = keyword(m[1]); return wc(k) >= 2 ? cut(k) : cut(lastWords(h)); }
    return cut(trimSmall(m[2]));
  }
  // "A vs B": the option after the first vs is the payoff
  if ((m = h.match(/\bvs\b\s+(.+?)\??$/i)) && wc(h) <= 9) return cut(m[1]);
  return cut(lastWords(h));
}

/** The leading noun phrase of a list item, cut at the first preposition ("Content Distribution Across Social" -> "Content Distribution"). */
function keyword(s: string) {
  const w = s.split(/\s+/);
  const i = w.findIndex((x, n) => n > 0 && /^(across|with|for|in|on|of|from|to|by|and|or|vs)$/i.test(x));
  return (i > 0 ? w.slice(0, i) : w).join(' ');
}
function trimSmall(s: string) {
  const w = s.split(/\s+/);
  while (w.length > 1 && SMALL.has(w[0].toLowerCase())) w.shift();
  return w.join(' ');
}
function lastWords(h: string) {
  const clean = h.replace(/\?$/, '');
  const w = clean.split(/\s+/);
  const n = w.length <= 3 ? 1 : w.length <= 6 ? 2 : 3;
  let tail = w.slice(-n);
  while (tail.length > 1 && SMALL.has(tail[0].toLowerCase())) tail = tail.slice(1);
  return tail.join(' ');
}

/** The heading without any highlight markers (for page titles, schema and the table of contents). */
export const plainHeading = (s: string) => (s ?? '').replace(/\[\[|\]\]/g, '');
