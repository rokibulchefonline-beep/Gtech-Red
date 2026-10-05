'use client';

import { useEffect, useRef, useState } from 'react';
import Icon from '@/components/Icon';
import { sanitizeHtml } from '@/lib/sanitize-html';
import { parseHeading } from '@/lib/hl';

/** Small word-processor style box: bold, italic, add link, remove link, clear formatting. */
export function RichField({ value, onChange, rows = 3, placeholder }: { value: string; onChange: (html: string) => void; rows?: number; placeholder?: string }) {
  const ref = useRef<HTMLDivElement>(null);
  const last = useRef(value);
  useEffect(() => { if (ref.current && value !== last.current) { ref.current.innerHTML = sanitizeHtml(value); last.current = value; } }, [value]);
  useEffect(() => { if (ref.current) ref.current.innerHTML = sanitizeHtml(value); }, []); // eslint-disable-line react-hooks/exhaustive-deps
  const emit = () => { const h = sanitizeHtml(ref.current?.innerHTML ?? ''); last.current = h; onChange(h); };
  const run = (cmd: string, arg?: string) => { ref.current?.focus(); document.execCommand(cmd, false, arg); emit(); };
  const link = () => {
    const sel = window.getSelection();
    if (!sel || sel.isCollapsed) { window.alert('Select the words you want to link first.'); return; }
    const u = window.prompt('Link address (https://… or /services/seo)');
    if (u) run('createLink', u.trim());
  };
  const btn = (label: string, icon: string, fn: () => void) => <button type="button" className="ad-ico" title={label} aria-label={label} onMouseDown={(e) => e.preventDefault()} onClick={fn}><Icon name={icon} size={15} /></button>;
  return (
    <div className="rf">
      <div className="rf-bar">
        {btn('Bold', 'lucide:bold', () => run('bold'))}
        {btn('Italic', 'lucide:italic', () => run('italic'))}
        {btn('Add link', 'lucide:link', link)}
        {btn('Remove link', 'lucide:unlink', () => run('unlink'))}
        {btn('Clear formatting', 'lucide:remove-formatting', () => { run('removeFormat'); run('unlink'); })}
      </div>
      <div ref={ref} className="rf-box" contentEditable suppressContentEditableWarning data-ph={placeholder} style={{ minHeight: rows * 24 }} onInput={emit} onBlur={emit}
        onPaste={(e) => { e.preventDefault(); const t = e.clipboardData.getData('text/plain'); document.execCommand('insertText', false, t); }} />
    </div>
  );
}

/** Heading input with a live preview and one-click highlight controls. */
export function HeadingField({ value, onChange, placeholder }: { value: string; onChange: (v: string) => void; placeholder?: string }) {
  const ref = useRef<HTMLInputElement>(null);
  const [, bump] = useState(0);
  const highlight = () => {
    const el = ref.current; if (!el) return;
    const { selectionStart: a, selectionEnd: b } = el;
    if (a == null || b == null || a === b) { window.alert('Select the words to colour red first.'); return; }
    const clean = value.replace(/\[\[|\]\]/g, '');
    const before = value.slice(0, a).replace(/\[\[|\]\]/g, '').length, upto = value.slice(0, b).replace(/\[\[|\]\]/g, '').length;
    onChange(`${clean.slice(0, before)}[[${clean.slice(before, upto)}]]${clean.slice(upto)}`);
  };
  const parts = parseHeading(value);
  return (
    <div className="hf">
      <input ref={ref} value={value} placeholder={placeholder} onChange={(e) => onChange(e.target.value)} onSelect={() => bump((n) => n + 1)} />
      <div className="hf-act">
        <button type="button" className="ad-btn ghost small" onClick={highlight}>Make selection red</button>
        <button type="button" className="ad-btn ghost small" onClick={() => onChange(value.replace(/\[\[|\]\]/g, ''))}>Automatic colour</button>
        <button type="button" className="ad-btn ghost small" onClick={() => onChange(`${value.replace(/\[\[|\]\]/g, '')} [[]]`)}>No colour</button>
      </div>
      <p className="hf-pre">{parts.map((p, i) => (p.hl ? <span key={i} className="hl">{p.t}</span> : <span key={i}>{p.t}</span>))}</p>
    </div>
  );
}
