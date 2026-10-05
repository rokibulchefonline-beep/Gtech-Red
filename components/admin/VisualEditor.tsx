'use client';

import { useCallback, useEffect, useRef, useState } from 'react';
import Icon from '@/components/Icon';
import { MediaModal, useToast } from '@/components/admin/ui';
import { stripHtml } from '@/lib/blog-utils';
import { sanitizeHtml } from '@/lib/sanitize-html';

// WordPress-classic style editor: Visual (rich text) and Code (HTML) tabs, a two-row toolbar with the
// block-format menu, an Add Media button and a status bar with the element path and word count.
// Uses the browser's built-in editing commands, so there is no editor library to load.

const blocks = [
  { tag: 'p', label: 'Paragraph', key: '7', cls: 'f-p' }, { tag: 'h1', label: 'Heading 1', key: '1', cls: 'f-h1' }, { tag: 'h2', label: 'Heading 2', key: '2', cls: 'f-h2' },
  { tag: 'h3', label: 'Heading 3', key: '3', cls: 'f-h3' }, { tag: 'h4', label: 'Heading 4', key: '4', cls: 'f-h4' }, { tag: 'h5', label: 'Heading 5', key: '5', cls: 'f-h5' },
  { tag: 'h6', label: 'Heading 6', key: '6', cls: 'f-h6' }, { tag: 'pre', label: 'Preformatted', key: '', cls: 'f-pre' },
];
const colors = ['#161616', '#e8202f', '#b0122c', '#f59e0b', '#16a34a', '#0ea5e9', '#2563eb', '#7c3aed', '#db2777', '#6b7280', '#9ca3af', '#ffffff'];
const chars = '€£$¥©®™°±×÷½¼¾§¶†‡•…–—‘’“”«»‹›←→↑↓↔✓✗★☆♥♦♣♠∞≈≠≤≥√∑πΩµ'.split('');
const helpKeys = [['Bold', 'Ctrl + B'], ['Italic', 'Ctrl + I'], ['Underline', 'Ctrl + U'], ['Insert link', 'Ctrl + K'], ['Undo', 'Ctrl + Z'], ['Redo', 'Ctrl + Y'], ['Paragraph', 'Shift + Alt + 7'], ['Heading 1 to 6', 'Shift + Alt + 1 to 6']];

export default function VisualEditor({ value, onChange }: { value: string; onChange: (html: string) => void }) {
  const toast = useToast();
  const ed = useRef<HTMLDivElement>(null);
  const code = useRef<HTMLTextAreaElement>(null);
  const range = useRef<Range | null>(null);
  const [mode, setMode] = useState<'visual' | 'code'>('visual');
  const [two, setTwo] = useState(true);
  const [full, setFull] = useState(false);
  const [plain, setPlain] = useState(false);
  const [menu, setMenu] = useState<'' | 'fmt' | 'color' | 'chars'>('');
  const [link, setLink] = useState<{ url: string; blank: boolean } | null>(null);
  const [help, setHelp] = useState(false);
  const [media, setMedia] = useState(false);
  const [state, setState] = useState({ block: 'p', path: 'p', bold: false, italic: false, ul: false, ol: false, quote: false, left: false, center: false, right: false, strike: false });

  // keep the editable area in step with the value when it changes from outside (loading, Code tab)
  useEffect(() => {
    const el = ed.current;
    if (mode !== 'visual' || !el || document.activeElement === el) return;
    const clean = sanitizeHtml(value);
    if (el.innerHTML !== clean) el.innerHTML = clean;
  }, [value, mode]);

  useEffect(() => { try { document.execCommand('defaultParagraphSeparator', false, 'p'); } catch { /* unsupported */ } }, []);
  const startPara = () => {
    const el = ed.current;
    if (el && !el.innerHTML.trim()) { el.innerHTML = '<p><br></p>'; const r = document.createRange(); r.setStart(el.firstChild!, 0); r.collapse(true); const s = window.getSelection(); s?.removeAllRanges(); s?.addRange(r); }
  };

  const sync = useCallback(() => { if (ed.current) onChange(ed.current.innerHTML); }, [onChange]);

  const exec = useCallback((cmd: string, arg?: string) => {
    if (mode !== 'visual') return;
    ed.current?.focus();
    document.execCommand(cmd, false, arg);
    sync();
  }, [mode, sync]);
  const setBlock = (tag: string) => { exec('formatBlock', `<${tag}>`); setMenu(''); };

  // toolbar state follows the caret
  useEffect(() => {
    const update = () => {
      const el = ed.current, sel = window.getSelection();
      if (!el || !sel?.anchorNode || !el.contains(sel.anchorNode)) return;
      range.current = sel.rangeCount ? sel.getRangeAt(0).cloneRange() : null;
      const names: string[] = [];
      for (let n: Node | null = sel.anchorNode; n && n !== el; n = n.parentNode) if (n.nodeType === 1) names.unshift((n as Element).tagName.toLowerCase());
      const block = names.find((n) => blocks.some((b) => b.tag === n)) ?? 'p';
      const q = (c: string) => { try { return document.queryCommandState(c); } catch { return false; } };
      setState({ block, path: names.join(' » ') || 'p', bold: q('bold'), italic: q('italic'), strike: q('strikeThrough'), ul: q('insertUnorderedList'), ol: q('insertOrderedList'), quote: names.includes('blockquote'), left: q('justifyLeft'), center: q('justifyCenter'), right: q('justifyRight') });
    };
    document.addEventListener('selectionchange', update);
    return () => document.removeEventListener('selectionchange', update);
  }, []);
  useEffect(() => {
    if (!full) return;
    const esc = (e: KeyboardEvent) => { if (e.key === 'Escape') setFull(false); };
    window.addEventListener('keydown', esc);
    return () => window.removeEventListener('keydown', esc);
  }, [full]);

  function onKeyDown(e: React.KeyboardEvent) {
    if (e.shiftKey && e.altKey) {
      const b = blocks.find((x) => x.key && e.code === `Digit${x.key}`);
      if (b) { e.preventDefault(); setBlock(b.tag); return; }
    }
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); openLink(); }
  }

  function openLink() {
    const a = (window.getSelection()?.anchorNode?.parentElement as HTMLElement | null)?.closest('a');
    setLink({ url: a?.getAttribute('href') ?? 'https://', blank: a?.getAttribute('target') === '_blank' });
  }
  function applyLink() {
    if (!link) return;
    ed.current?.focus();
    const sel = window.getSelection();
    if (range.current) { sel?.removeAllRanges(); sel?.addRange(range.current); }
    const url = link.url.trim();
    if (!url) { exec('unlink'); setLink(null); return; }
    if (sel?.isCollapsed) document.execCommand('insertHTML', false, `<a href="${url.replace(/"/g, '&quot;')}"${link.blank ? ' target="_blank"' : ''}>${url.replace(/[<>&]/g, '')}</a>`);
    else {
      document.execCommand('createLink', false, url);
      if (link.blank) ed.current?.querySelectorAll(`a[href="${CSS.escape(url)}"]`).forEach((a) => a.setAttribute('target', '_blank'));
    }
    sync(); setLink(null);
  }

  function insertImage(src: string, alt: string) {
    const html = `<img src="${src}" alt="${alt.replace(/"/g, '&quot;')}"/>`;
    if (mode === 'code') {
      const t = code.current; if (!t) return;
      const s = t.selectionStart; onChange(value.slice(0, s) + html + value.slice(t.selectionEnd));
      return;
    }
    ed.current?.focus();
    const sel = window.getSelection();
    if (range.current) { sel?.removeAllRanges(); sel?.addRange(range.current); }
    document.execCommand('insertHTML', false, html); sync();
  }
  async function uploadAndInsert(f: File) {
    try {
      const fd = new FormData(); fd.append('file', f);
      const res = await fetch('/api/admin/media', { method: 'POST', body: fd }); const d = await res.json();
      if (!res.ok) throw new Error(d.error);
      insertImage(d.media.url, f.name.replace(/\.[^.]+$/, '')); toast('Image inserted');
    } catch (e) { toast(e instanceof Error ? e.message : 'Upload failed', true); }
  }

  function onPaste(e: React.ClipboardEvent) {
    e.preventDefault();
    const html = e.clipboardData.getData('text/html'), text = e.clipboardData.getData('text/plain');
    if (!plain && html) document.execCommand('insertHTML', false, sanitizeHtml(html)); else document.execCommand('insertText', false, text);
    sync();
  }
  function onDrop(e: React.DragEvent) {
    const f = e.dataTransfer.files?.[0];
    if (f?.type.startsWith('image/')) { e.preventDefault(); uploadAndInsert(f); }
  }

  const T = ({ icon, tip, on, run, off }: { icon: string; tip: string; on?: boolean; run: () => void; off?: boolean }) => (
    <button type="button" className={on ? 'on' : ''} title={tip} aria-label={tip} aria-pressed={on} disabled={off} onMouseDown={(e) => e.preventDefault()} onClick={run}><Icon name={icon} size={17} /></button>
  );
  const cur = blocks.find((b) => b.tag === state.block) ?? blocks[0];
  const words = stripHtml(value).match(/\S+/g)?.length ?? 0;
  const visual = mode === 'visual';

  return (
    <div className={`wp-ed${full ? ' full' : ''}`}>
      <div className="wp-top">
        <button type="button" className="wp-media" onClick={() => { setMedia(true); }}><Icon name="lucide:image-plus" size={16} /> Add Media</button>
        <div className="wp-tabs"><button type="button" className={visual ? 'on' : ''} onClick={() => setMode('visual')}>Visual</button><button type="button" className={!visual ? 'on' : ''} onClick={() => setMode('code')}>Code</button></div>
      </div>

      <div className="wp-box">
        <div className="wp-tb" role="toolbar" aria-label="Formatting" onMouseDown={(e) => { if ((e.target as HTMLElement).closest('.wp-pop')) return; }}>
          <div className="wp-row">
            <div className="wp-fmt">
              <button type="button" className="wp-fmt-btn" disabled={!visual} onMouseDown={(e) => e.preventDefault()} onClick={() => setMenu(menu === 'fmt' ? '' : 'fmt')} aria-haspopup="listbox" aria-expanded={menu === 'fmt'}>{cur.label}<Icon name="lucide:chevron-down" size={12} /></button>
              {menu === 'fmt' && <ul className="wp-pop wp-fmt-list" role="listbox">{blocks.map((b) => (
                <li key={b.tag}><button type="button" role="option" aria-selected={b.tag === state.block} className={b.tag === state.block ? 'on' : ''} onMouseDown={(e) => e.preventDefault()} onClick={() => setBlock(b.tag)}><span className={b.cls}>{b.label}</span>{b.key && <small>(Shift+Alt+{b.key})</small>}</button></li>
              ))}</ul>}
            </div>
            <T icon="lucide:bold" tip="Bold (Ctrl+B)" on={state.bold} run={() => exec('bold')} off={!visual} />
            <T icon="lucide:italic" tip="Italic (Ctrl+I)" on={state.italic} run={() => exec('italic')} off={!visual} />
            <i className="wp-sep" />
            <T icon="lucide:list" tip="Bulleted list" on={state.ul} run={() => exec('insertUnorderedList')} off={!visual} />
            <T icon="lucide:list-ordered" tip="Numbered list" on={state.ol} run={() => exec('insertOrderedList')} off={!visual} />
            <T icon="lucide:quote" tip="Blockquote" on={state.quote} run={() => setBlock(state.quote ? 'p' : 'blockquote')} off={!visual} />
            <i className="wp-sep" />
            <T icon="lucide:align-left" tip="Align left" on={state.left} run={() => exec('justifyLeft')} off={!visual} />
            <T icon="lucide:align-center" tip="Align centre" on={state.center} run={() => exec('justifyCenter')} off={!visual} />
            <T icon="lucide:align-right" tip="Align right" on={state.right} run={() => exec('justifyRight')} off={!visual} />
            <i className="wp-sep" />
            <T icon="lucide:link" tip="Insert/edit link (Ctrl+K)" run={openLink} off={!visual} />
            <T icon="lucide:keyboard" tip="Toolbar toggle" on={two} run={() => setTwo(!two)} />
            <span className="wp-grow" />
            <T icon={full ? 'lucide:minimize' : 'lucide:maximize'} tip={full ? 'Exit fullscreen (Esc)' : 'Distraction-free writing'} run={() => setFull(!full)} />
          </div>
          {two && (
            <div className="wp-row">
              <T icon="lucide:strikethrough" tip="Strikethrough" on={state.strike} run={() => exec('strikeThrough')} off={!visual} />
              <T icon="lucide:minus" tip="Horizontal line" run={() => exec('insertHorizontalRule')} off={!visual} />
              <div className="wp-fmt">
                <button type="button" className="wp-colorbtn" disabled={!visual} title="Text colour" aria-label="Text colour" onMouseDown={(e) => e.preventDefault()} onClick={() => setMenu(menu === 'color' ? '' : 'color')}><span>A</span><Icon name="lucide:chevron-down" size={11} /></button>
                {menu === 'color' && <div className="wp-pop wp-colors">{colors.map((c) => <button key={c} type="button" style={{ background: c }} aria-label={`Colour ${c}`} onMouseDown={(e) => e.preventDefault()} onClick={() => { exec('foreColor', c); setMenu(''); }} />)}</div>}
              </div>
              <T icon="lucide:clipboard-type" tip="Paste as text" on={plain} run={() => { setPlain(!plain); toast(!plain ? 'Paste as text is on' : 'Paste as text is off'); }} off={!visual} />
              <T icon="lucide:eraser" tip="Clear formatting" run={() => { exec('removeFormat'); exec('formatBlock', '<p>'); }} off={!visual} />
              <div className="wp-fmt">
                <button type="button" title="Special character" aria-label="Special character" disabled={!visual} onMouseDown={(e) => e.preventDefault()} onClick={() => setMenu(menu === 'chars' ? '' : 'chars')}><Icon name="lucide:omega" size={17} /></button>
                {menu === 'chars' && <div className="wp-pop wp-chars">{chars.map((c) => <button key={c} type="button" onMouseDown={(e) => e.preventDefault()} onClick={() => { exec('insertText', c); setMenu(''); }}>{c}</button>)}</div>}
              </div>
              <i className="wp-sep" />
              <T icon="lucide:indent-decrease" tip="Decrease indent" run={() => exec('outdent')} off={!visual} />
              <T icon="lucide:indent-increase" tip="Increase indent" run={() => exec('indent')} off={!visual} />
              <T icon="lucide:undo-2" tip="Undo (Ctrl+Z)" run={() => exec('undo')} off={!visual} />
              <T icon="lucide:redo-2" tip="Redo (Ctrl+Y)" run={() => exec('redo')} off={!visual} />
              <T icon="lucide:circle-help" tip="Keyboard shortcuts" run={() => setHelp(true)} />
            </div>
          )}
        </div>

        {visual ? (
          <div className="wp-area"><div ref={ed} className="wp-visual bp-body" contentEditable suppressContentEditableWarning spellCheck role="textbox" aria-multiline="true" aria-label="Post content"
            onFocus={startPara} onInput={sync} onBlur={sync} onKeyDown={onKeyDown} onPaste={onPaste} onDrop={onDrop} onClick={() => setMenu('')} /></div>
        ) : (
          <textarea ref={code} className="wp-code" value={value} onChange={(e) => onChange(e.target.value)} spellCheck={false} aria-label="Post content (HTML)" />
        )}
        <div className="wp-status"><span>{visual ? state.path : 'html'}</span><span>Word count: {words}</span></div>
      </div>

      {link && (
        <div className="ad-modal" role="dialog" aria-modal="true" aria-label="Insert link" onMouseDown={(e) => { if (e.target === e.currentTarget) setLink(null); }}>
          <div className="ad-modal-box narrow">
            <header><h2>Insert/edit link</h2></header>
            <div className="ad-form one">
              <label className="ad-field"><span>URL</span><input autoFocus value={link.url} onChange={(e) => setLink({ ...link, url: e.target.value })} onKeyDown={(e) => { if (e.key === 'Enter') { e.preventDefault(); applyLink(); } }} placeholder="https://… or /services/seo" /><small>Leave empty and apply to remove the link.</small></label>
              <label className="ad-check"><input type="checkbox" checked={link.blank} onChange={(e) => setLink({ ...link, blank: e.target.checked })} /> Open link in a new tab</label>
            </div>
            <footer><button className="ad-btn ghost" onClick={() => setLink(null)}>Cancel</button><button className="ad-btn" onClick={applyLink}>Apply</button></footer>
          </div>
        </div>
      )}
      {help && (
        <div className="ad-modal" role="dialog" aria-modal="true" aria-label="Keyboard shortcuts" onMouseDown={(e) => { if (e.target === e.currentTarget) setHelp(false); }}>
          <div className="ad-modal-box narrow"><header><h2>Keyboard shortcuts</h2><button className="ad-ico" onClick={() => setHelp(false)} aria-label="Close"><Icon name="lucide:x" size={18} /></button></header>
            <table className="ad-table" style={{ minWidth: 0 }}><tbody>{helpKeys.map(([a, b]) => <tr key={a}><td>{a}</td><td><kbd>{b}</kbd></td></tr>)}</tbody></table>
          </div>
        </div>
      )}
      {media && <MediaModal altField title="Add Media" onClose={() => setMedia(false)} onPick={(m) => { insertImage(m.url, m.alt || m.name.replace(/\.[^.]+$/, '')); setMedia(false); }} />}
    </div>
  );
}
