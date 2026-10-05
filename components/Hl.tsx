// Two-tone heading text: the opening words stay dark and the closing words turn red, e.g.
// "Experience Working with Industry <red>Leading Brands.</red>". Roughly the last third of the
// words are highlighted (1 to 3 words), so every heading gets the same treatment automatically.
export default function Hl({ children }: { children: string }) {
  const words = children.trim().split(/\s+/);
  if (words.length < 2) return <>{children}</>;
  const n = Math.min(3, Math.max(1, Math.round(words.length / 3)));
  return <>{words.slice(0, -n).join(' ')} <span className="hl">{words.slice(-n).join(' ')}</span></>;
}
