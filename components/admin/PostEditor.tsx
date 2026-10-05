'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import Icon from '@/components/Icon';
import PostBody from '@/components/blog/PostBody';
import { api } from '@/components/admin/api';
import { Count, Field, ImageField, ListField, useToast } from '@/components/admin/ui';
import { parseBody, readTime } from '@/lib/blog-utils';
import { analyse } from '@/lib/seo-analysis';

type Post = {
  title: string; slug: string; excerpt: string; body: string; category: string; tags: string[]; image: string; imageAlt: string; author: string; featured: boolean;
  status: 'draft' | 'published' | 'scheduled'; date: string; metaTitle: string; metaDescription: string; focusKeyword: string; canonical: string; noindex: boolean;
};
const blank: Post = { title: '', slug: '', excerpt: '', body: '', category: 'Insights', tags: [], image: '', imageAlt: '', author: 'GTech Editorial Team', featured: false, status: 'draft', date: '', metaTitle: '', metaDescription: '', focusKeyword: '', canonical: '', noindex: false };
const categories = ['SEO', 'Paid Media', 'Social Media', 'Web Design', 'Software', 'Branding', 'Insights'];
const slugify = (s: string) => s.toLowerCase().replace(/&/g, ' and ').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '').slice(0, 80);
const toLocal = (iso: string) => { if (!iso) return ''; const d = new Date(iso); return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 16); };

const tools: { icon: string; tip: string; run: string }[] = [
  { icon: 'lucide:heading-2', tip: 'Heading 2 (section)', run: 'h2' }, { icon: 'lucide:heading-3', tip: 'Heading 3', run: 'h3' },
  { icon: 'lucide:bold', tip: 'Bold (Ctrl+B)', run: 'bold' }, { icon: 'lucide:italic', tip: 'Italic (Ctrl+I)', run: 'italic' },
  { icon: 'lucide:link', tip: 'Link (Ctrl+K)', run: 'link' }, { icon: 'lucide:list', tip: 'Bulleted list', run: 'ul' },
  { icon: 'lucide:list-ordered', tip: 'Numbered list', run: 'ol' }, { icon: 'lucide:quote', tip: 'Quote', run: 'quote' },
  { icon: 'lucide:image', tip: 'Insert image', run: 'image' }, { icon: 'lucide:minus', tip: 'Divider', run: 'hr' },
];

export default function PostEditor({ id }: { id: string }) {
  const router = useRouter();
  const toast = useToast();
  const isNew = id === 'new';
  const [p, setP] = useState<Post>(blank);
  const [loaded, setLoaded] = useState(isNew);
  const [dirty, setDirty] = useState(false);
  const [saving, setSaving] = useState(false);
  const [slugTouched, setSlugTouched] = useState(!isNew);
  const [view, setView] = useState<'write' | 'split' | 'preview'>('split');
  const [tab, setTab] = useState<'publish' | 'seo' | 'media'>('publish');
  const ta = useRef<HTMLTextAreaElement>(null);
  const fileRef = useRef<HTMLInputElement>(null);
  const draftKey = `gt-post-draft-${id}`;

  useEffect(() => {
    if (isNew) return;
    api(`/api/admin/posts/${id}`).then((d) => { setP({ ...blank, ...d.doc }); setLoaded(true); }).catch((e) => { toast(e.message, true); setLoaded(true); });
  }, [id, isNew, toast]);

  // Restore an unsaved local draft (autosaved every 2s while editing).
  useEffect(() => {
    if (!loaded) return;
    try {
      const d = localStorage.getItem(draftKey);
      if (d && window.confirm('Restore your unsaved changes from the last session?')) { setP({ ...blank, ...JSON.parse(d) }); setDirty(true); } else localStorage.removeItem(draftKey);
    } catch { /* storage blocked */ }
  }, [loaded, draftKey]);
  useEffect(() => {
    if (!dirty) return;
    const t = setTimeout(() => { try { localStorage.setItem(draftKey, JSON.stringify(p)); } catch { /* ignore */ } }, 2000);
    return () => clearTimeout(t);
  }, [p, dirty, draftKey]);
  useEffect(() => {
    const warn = (e: BeforeUnloadEvent) => { if (dirty) e.preventDefault(); };
    window.addEventListener('beforeunload', warn);
    return () => window.removeEventListener('beforeunload', warn);
  }, [dirty]);

  const set = <K extends keyof Post>(k: K, v: Post[K]) => { setP((x) => ({ ...x, [k]: v })); setDirty(true); };
  const setTitle = (v: string) => { setP((x) => ({ ...x, title: v, slug: slugTouched ? x.slug : slugify(v) })); setDirty(true); };

  // ---- editor commands ----
  const edit = useCallback((fn: (sel: string, before: string, after: string) => { text: string; start: number; end: number }) => {
    const el = ta.current; if (!el) return;
    const { selectionStart: s, selectionEnd: e, value } = el;
    const r = fn(value.slice(s, e), value.slice(0, s), value.slice(e));
    set('body', r.text);
    requestAnimationFrame(() => { el.focus(); el.setSelectionRange(r.start, r.end); });
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);
  const wrap = (a: string, b = a, ph = 'text') => edit((sel, bf, af) => { const m = sel || ph; return { text: bf + a + m + b + af, start: bf.length + a.length, end: bf.length + a.length + m.length }; });
  const linePrefix = (pre: string | ((i: number) => string)) => edit((sel, bf, af) => {
    const start = bf.lastIndexOf('\n') + 1;
    const block = (bf.slice(start) + sel).split('\n').map((l, i) => (typeof pre === 'string' ? pre : pre(i)) + l.replace(/^(#{1,3} |[-*] |\d+\. |> )/, '')).join('\n');
    return { text: bf.slice(0, start) + block + af, start: start + block.length, end: start + block.length };
  });
  const insert = (text: string) => edit((_s, bf, af) => ({ text: bf + text + af, start: bf.length + text.length, end: bf.length + text.length }));

  async function uploadInline(f: File) {
    try {
      const fd = new FormData(); fd.append('file', f);
      const res = await fetch('/api/admin/media', { method: 'POST', body: fd }); const d = await res.json();
      if (!res.ok) throw new Error(d.error);
      insert(`\n\n![${f.name.replace(/\.[^.]+$/, '')}](${d.media.url})\n\n`); toast('Image inserted');
    } catch (e) { toast(e instanceof Error ? e.message : 'Upload failed', true); }
  }
  function run(cmd: string) {
    if (cmd === 'h2') linePrefix('## '); else if (cmd === 'h3') linePrefix('### ');
    else if (cmd === 'bold') wrap('**'); else if (cmd === 'italic') wrap('*');
    else if (cmd === 'ul') linePrefix('- '); else if (cmd === 'ol') linePrefix((i) => `${i + 1}. `); else if (cmd === 'quote') linePrefix('> ');
    else if (cmd === 'hr') insert('\n\n---\n\n');
    else if (cmd === 'link') { const u = window.prompt('Link URL (https://… or /services/seo)'); if (u) edit((sel, bf, af) => { const t = sel || 'link text'; return { text: `${bf}[${t}](${u})${af}`, start: bf.length + 1, end: bf.length + 1 + t.length }; }); }
    else if (cmd === 'image') fileRef.current?.click();
  }
  function onKey(e: React.KeyboardEvent) {
    if (!(e.ctrlKey || e.metaKey)) return;
    const k = e.key.toLowerCase();
    if (k === 'b') { e.preventDefault(); run('bold'); } else if (k === 'i') { e.preventDefault(); run('italic'); } else if (k === 'k') { e.preventDefault(); run('link'); } else if (k === 's') { e.preventDefault(); save(); }
  }

  // ---- derived ----
  const blocks = useMemo(() => parseBody(p.body), [p.body]);
  const words = (p.body.match(/\S+/g) ?? []).length;
  const seo = useMemo(() => analyse({ title: p.title, metaTitle: p.metaTitle, metaDescription: p.metaDescription || p.excerpt, slug: p.slug, keyword: p.focusKeyword, body: p.body, image: p.image, imageAlt: p.imageAlt }), [p]);
  const serpTitle = (p.metaTitle || p.title || 'Post title') ;
  const serpDesc = p.metaDescription || p.excerpt || 'Your meta description appears here. Write 120 to 160 characters that make people click.';

  async function save(status?: Post['status']) {
    const body = { ...p, status: status ?? p.status };
    if (body.status === 'scheduled' && !body.date) { toast('Pick a publish date to schedule this post.', true); setTab('publish'); return; }
    if (body.status === 'published' && !body.date) body.date = new Date().toISOString();
    setSaving(true);
    try {
      const d = isNew ? await api('/api/admin/posts', { body }) : await api(`/api/admin/posts/${id}`, { method: 'PUT', body });
      setP({ ...blank, ...d.doc }); setDirty(false);
      try { localStorage.removeItem(draftKey); } catch { /* ignore */ }
      toast(body.status === 'published' ? 'Saved. Use Publish site to rebuild.' : 'Saved');
      if (isNew) router.replace(`/admin/posts/${d.doc._id}`);
    } catch (e) { toast(e instanceof Error ? e.message : 'Save failed', true); }
    setSaving(false);
  }

  if (!loaded) return <p className="ad-empty">Loading…</p>;
  const tone = seo.score >= 75 ? 'good' : seo.score >= 45 ? 'mid' : 'low';

  return (
    <div className="ed">
      <div className="ed-bar">
        <Link href="/admin/posts" className="ad-btn ghost small"><Icon name="lucide:arrow-left" size={15} /> Posts</Link>
        <span className="ed-state">{saving ? 'Saving…' : dirty ? 'Unsaved changes' : 'All changes saved'}</span>
        <div className="ed-bar-r">
          <select value={p.status} onChange={(e) => set('status', e.target.value as Post['status'])} aria-label="Status"><option value="draft">Draft</option><option value="published">Published</option><option value="scheduled">Scheduled</option></select>
          <button className="ad-btn ghost small" onClick={() => save()} disabled={saving}>Save</button>
          {p.status !== 'published' && <button className="ad-btn small" onClick={() => save('published')} disabled={saving}>Publish</button>}
        </div>
      </div>

      <div className="ed-grid">
        <div className="ed-main">
          <input className="ed-title" value={p.title} onChange={(e) => setTitle(e.target.value)} placeholder="Post title" aria-label="Post title" />
          <div className="ed-slug"><span>/blogs/</span><input value={p.slug} onChange={(e) => { setSlugTouched(true); set('slug', slugify(e.target.value)); }} placeholder="url-slug" aria-label="URL slug" /></div>

          <div className="ed-box">
            <div className="ed-tools" role="toolbar" aria-label="Formatting">
              {tools.map((t) => <button key={t.run} type="button" title={t.tip} aria-label={t.tip} onMouseDown={(e) => e.preventDefault()} onClick={() => run(t.run)}><Icon name={t.icon} size={17} /></button>)}
              <span className="ed-sp" />
              {(['write', 'split', 'preview'] as const).map((v) => <button key={v} type="button" className={`ed-v${view === v ? ' on' : ''}`} onClick={() => setView(v)}>{v}</button>)}
            </div>
            <div className={`ed-panes ${view}`}>
              {view !== 'preview' && <textarea ref={ta} value={p.body} onChange={(e) => set('body', e.target.value)} onKeyDown={onKey} spellCheck placeholder={'Start writing…\n\n## Use H2 headings for sections\nWrite short paragraphs. Use **bold**, lists and [links](/services/seo).'}
                onDrop={(e) => { const f = e.dataTransfer.files?.[0]; if (f?.type.startsWith('image/')) { e.preventDefault(); uploadInline(f); } }} aria-label="Post content (Markdown)" />}
              {view !== 'write' && <div className="ed-prev bp-body">{p.body.trim() ? <PostBody blocks={blocks} /> : <p className="ad-muted">Your preview appears here.</p>}</div>}
            </div>
            <div className="ed-foot"><span>{words} words</span><span>{readTime(p.body)} min read</span><span>{blocks.filter((b) => b.type === 'h2').length} sections</span><span className="ed-hint">Tip: drag an image into the editor to upload it</span></div>
            <input ref={fileRef} type="file" hidden accept="image/png,image/jpeg,image/webp,image/gif" onChange={(e) => { const f = e.target.files?.[0]; if (f) uploadInline(f); e.target.value = ''; }} />
          </div>

          <Field label="Excerpt" hint={<><Count n={p.excerpt.length} max={200} /> Shown on blog cards and used as the default description.</>}>
            <textarea rows={3} value={p.excerpt} onChange={(e) => set('excerpt', e.target.value)} />
          </Field>
        </div>

        <aside className="ed-side">
          <div className="ed-tabs">{(['publish', 'seo', 'media'] as const).map((t) => <button key={t} className={tab === t ? 'on' : ''} onClick={() => setTab(t)}>{t === 'seo' ? <>SEO <b className={`ed-score ${tone}`}>{seo.score}</b></> : t === 'media' ? 'Cover' : 'Post'}</button>)}</div>

          {tab === 'publish' && (
            <div className="ed-pane">
              {p.status === 'scheduled' && <Field label="Publish on"><input type="datetime-local" value={toLocal(p.date)} onChange={(e) => set('date', e.target.value ? new Date(e.target.value).toISOString() : '')} /></Field>}
              {p.status === 'published' && <Field label="Published date"><input type="datetime-local" value={toLocal(p.date)} onChange={(e) => set('date', e.target.value ? new Date(e.target.value).toISOString() : '')} /></Field>}
              <Field label="Category"><input list="cats" value={p.category} onChange={(e) => set('category', e.target.value)} /><datalist id="cats">{categories.map((c) => <option key={c} value={c} />)}</datalist></Field>
              <Field label="Tags"><ListField value={p.tags} onChange={(v) => set('tags', v)} placeholder="Add a tag" max={12} /></Field>
              <Field label="Author"><input value={p.author} onChange={(e) => set('author', e.target.value)} /></Field>
              <label className="ad-check"><input type="checkbox" checked={p.featured} onChange={(e) => set('featured', e.target.checked)} /> Feature this post on the blog page</label>
            </div>
          )}

          {tab === 'media' && (
            <div className="ed-pane">
              <Field label="Cover image" hint="Shown at the top of the post and in cards. 1200×675 works well."><ImageField value={p.image} onChange={(v) => set('image', v)} label="Cover image" /></Field>
              <Field label="Image alt text" hint="Describe the image for accessibility and SEO."><input value={p.imageAlt} onChange={(e) => set('imageAlt', e.target.value)} /></Field>
            </div>
          )}

          {tab === 'seo' && (
            <div className="ed-pane">
              <div className="serp"><small>gtechdigital.co.uk › blogs › {p.slug || 'url-slug'}</small><b>{serpTitle.length > 60 ? serpTitle.slice(0, 58) + '…' : serpTitle}</b><p>{serpDesc.length > 160 ? serpDesc.slice(0, 157) + '…' : serpDesc}</p></div>
              <Field label="Focus keyword" hint="The phrase this post should rank for."><input value={p.focusKeyword} onChange={(e) => set('focusKeyword', e.target.value)} placeholder="e.g. local seo for plumbers" /></Field>
              <Field label="SEO title" hint={<><Count n={(p.metaTitle || p.title).length} max={60} /> Leave blank to use the post title.</>}><input value={p.metaTitle} onChange={(e) => set('metaTitle', e.target.value)} placeholder={p.title} /></Field>
              <Field label="Meta description" hint={<><Count n={(p.metaDescription || p.excerpt).length} max={160} /></>}><textarea rows={3} value={p.metaDescription} onChange={(e) => set('metaDescription', e.target.value)} placeholder={p.excerpt} /></Field>
              <Field label="Canonical URL" hint="Only if this content was first published elsewhere."><input value={p.canonical} onChange={(e) => set('canonical', e.target.value)} placeholder="https://" /></Field>
              <label className="ad-check"><input type="checkbox" checked={p.noindex} onChange={(e) => set('noindex', e.target.checked)} /> Hide from search engines (noindex)</label>
              <div className="seo-list"><div className={`seo-score ${tone}`}><strong>{seo.score}</strong><span>SEO score</span></div>
                <ul>{seo.checks.map((c) => <li key={c.label} className={c.ok === true ? 'ok' : c.ok === 'warn' ? 'warn' : 'bad'}><Icon name={c.ok === true ? 'lucide:circle-check' : c.ok === 'warn' ? 'lucide:circle-alert' : 'lucide:circle-x'} size={16} /><span>{c.label}{c.ok !== true && c.hint && <small>{c.hint}</small>}</span></li>)}</ul></div>
            </div>
          )}
        </aside>
      </div>
    </div>
  );
}
