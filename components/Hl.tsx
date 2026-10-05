import { parseHeading } from '@/lib/hl';

// Two-tone heading text: dark text with the highlighted words in red. Highlights are automatic (the
// closing words) unless the heading uses [[double brackets]] to choose exactly which words.
export default function Hl({ children }: { children: string }) {
  return <>{parseHeading(children).map((p, i) => (p.hl ? <span key={i} className="hl">{p.t}</span> : p.t))}</>;
}
