'use client';

import { createContext, useCallback, useContext, useEffect, useRef, useState, type ReactNode } from 'react';
import Icon from '@/components/Icon';
import { api } from '@/components/admin/api';

// ---- toasts ------------------------------------------------------------------------------------
type Toast = { id: number; text: string; bad?: boolean };
const ToastCtx = createContext<(text: string, bad?: boolean) => void>(() => {});
export const useToast = () => useContext(ToastCtx);
export function ToastProvider({ children }: { children: ReactNode }) {
  const [list, setList] = useState<Toast[]>([]);
  const push = useCallback((text: string, bad?: boolean) => {
    const id = Date.now() + Math.random();
    setList((l) => [...l, { id, text, bad }]);
    setTimeout(() => setList((l) => l.filter((t) => t.id !== id)), bad ? 6000 : 3500);
  }, []);
  return <ToastCtx.Provider value={push}>{children}<div className="ad-toasts" role="status">{list.map((t) => <div key={t.id} className={`ad-toast${t.bad ? ' bad' : ''}`}>{t.text}</div>)}</div></ToastCtx.Provider>;
}

// ---- form bits ---------------------------------------------------------------------------------
export function Field({ label, hint, children, wide }: { label: string; hint?: ReactNode; children: ReactNode; wide?: boolean }) {
  return <label className={`ad-field${wide ? ' wide' : ''}`}><span>{label}</span>{children}{hint && <small>{hint}</small>}</label>;
}
export const Card = ({ title, actions, children }: { title?: string; actions?: ReactNode; children: ReactNode }) => (
  <section className="ad-card">{(title || actions) && <header><h2>{title}</h2><div>{actions}</div></header>}{children}</section>
);
export const PageTitle = ({ title, sub, actions }: { title: string; sub?: string; actions?: ReactNode }) => (
  <div className="ad-pt"><div><h1>{title}</h1>{sub && <p>{sub}</p>}</div><div className="ad-pt-actions">{actions}</div></div>
);
export const Badge = ({ tone = 'grey', children }: { tone?: 'grey' | 'green' | 'red' | 'amber' | 'blue'; children: ReactNode }) => <span className={`ad-badge ${tone}`}>{children}</span>;

/** Counter for text inputs with a recommended length. */
export const Count = ({ n, max }: { n: number; max: number }) => <em className={`ad-count${n > max ? ' over' : ''}`}>{n}/{max}</em>;

/** String-list editor (bullets, results, tags). */
export function ListField({ value, onChange, placeholder, max = 20 }: { value: string[]; onChange: (v: string[]) => void; placeholder?: string; max?: number }) {
  return (
    <div className="ad-list">
      {value.map((v, i) => (
        <div key={i} className="ad-list-row">
          <input value={v} placeholder={placeholder} onChange={(e) => onChange(value.map((x, n) => (n === i ? e.target.value : x)))} />
          <button type="button" className="ad-ico" aria-label="Remove" onClick={() => onChange(value.filter((_, n) => n !== i))}><Icon name="lucide:x" size={16} /></button>
        </div>
      ))}
      {value.length < max && <button type="button" className="ad-btn small" onClick={() => onChange([...value, ''])}><Icon name="lucide:plus" size={15} /> Add</button>}
    </div>
  );
}

// ---- image picker with upload + media library ----------------------------------------------------
type Media = { _id: string; name: string; url: string };
export function ImageField({ value, onChange, label = 'Image' }: { value: string; onChange: (v: string) => void; label?: string }) {
  const toast = useToast();
  const [open, setOpen] = useState(false);
  const [lib, setLib] = useState<Media[]>([]);
  const [busy, setBusy] = useState(false);
  const file = useRef<HTMLInputElement>(null);

  useEffect(() => { if (open) api('/api/admin/media').then((d) => setLib(d.rows)).catch((e) => toast(e.message, true)); }, [open, toast]);

  async function upload(f: File) {
    setBusy(true);
    try {
      const fd = new FormData(); fd.append('file', f);
      const res = await fetch('/api/admin/media', { method: 'POST', body: fd });
      const d = await res.json();
      if (!res.ok) throw new Error(d.error || 'Upload failed');
      onChange(d.media.url); setOpen(false); toast('Image uploaded');
    } catch (e) { toast(e instanceof Error ? e.message : 'Upload failed', true); }
    setBusy(false);
  }

  return (
    <div className="ad-img">
      <div className="ad-img-row">
        <div className="ad-img-prev">{value ? /* eslint-disable-next-line @next/next/no-img-element */ <img src={value} alt="" /> : <Icon name="lucide:image" size={26} />}</div>
        <div className="ad-img-ctl">
          <input value={value} placeholder="https://… or choose an image" aria-label={label} onChange={(e) => onChange(e.target.value)} />
          <div>
            <button type="button" className="ad-btn small" onClick={() => file.current?.click()} disabled={busy}><Icon name="lucide:upload" size={15} /> {busy ? 'Uploading…' : 'Upload'}</button>
            <button type="button" className="ad-btn small" onClick={() => setOpen(true)}><Icon name="lucide:images" size={15} /> Library</button>
            {value && <button type="button" className="ad-btn small ghost" onClick={() => onChange('')}>Remove</button>}
          </div>
        </div>
        <input ref={file} type="file" hidden accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml" onChange={(e) => { const f = e.target.files?.[0]; if (f) upload(f); e.target.value = ''; }} />
      </div>
      {open && (
        <div className="ad-modal" role="dialog" aria-modal="true" onMouseDown={(e) => { if (e.target === e.currentTarget) setOpen(false); }}>
          <div className="ad-modal-box">
            <header><h2>Media library</h2><button className="ad-ico" onClick={() => setOpen(false)} aria-label="Close"><Icon name="lucide:x" size={18} /></button></header>
            {lib.length === 0 ? <p className="ad-empty">No images yet. Upload one to start your library.</p> : (
              <div className="ad-media">{lib.map((m) => <button key={m._id} type="button" onClick={() => { onChange(m.url); setOpen(false); }} title={m.name}>{/* eslint-disable-next-line @next/next/no-img-element */}<img src={m.url} alt={m.name} /><span>{m.name}</span></button>)}</div>
            )}
          </div>
        </div>
      )}
    </div>
  );
}

// ---- confirm + debounce ------------------------------------------------------------------------
export const confirmDelete = (what: string) => window.confirm(`Delete ${what}? This cannot be undone.`);
export { fmtDate } from '@/lib/fmt';
