'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useEffect, useMemo, useState, type ReactNode } from 'react';
import Icon from '@/components/Icon';
import HtmlBody from '@/components/blog/HtmlBody';
import VisualEditor from '@/components/admin/VisualEditor';
import { api } from '@/components/admin/api';
import { Count, MediaModal, confirmDelete, useToast } from '@/components/admin/ui';
import { htmlToPseudoMd, markdownToHtml } from '@/lib/blog-utils';
import { analyse } from '@/lib/seo-analysis';

type Post = {
  title: string; slug: string; excerpt: string; body: string; categories: string[]; tags: string[]; image: string; imageAlt: string; author: string; featured: boolean;
  status: 'draft' | 'published' | 'scheduled'; date: string; visibility: 'public' | 'private'; postFormat: string; allowComments: boolean; allowPingbacks: boolean;
  customFields: { name: string; value: string }[]; metaTitle: string; metaDescription: string; focusKeyword: string; canonical: string; noindex: boolean;
};
const blank: Post = { title: '', slug: '', excerpt: '', body: '', categories: [], tags: [], image: '', imageAlt: '', author: 'GTech Editorial Team', featured: false, status: 'draft', date: '', visibility: 'public', postFormat: 'standard', allowComments: true, allowPingbacks: true, customFields: [], metaTitle: '', metaDescription: '', focusKeyword: '', canonical: '', noindex: false };
const defaultCats = ['SEO', 'Paid Media', 'Social Media', 'Web Design', 'Software', 'Branding', 'Insights'];
const slugify = (s: string) => s.toLowerCase().replace(/&/g, ' and ').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '').slice(0, 80);
const toLocal = (iso: string) => { if (!iso) return ''; const d = new Date(iso); return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 16); };
const fmt = (iso: string) => new Date(iso).toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

// The slug sits under the title; the Format and Discussion boxes are gone (the site has no post formats or
// comments). Saved layouts drop removed boxes and pick up moved ones automatically.
const mainDefault = ['excerpt', 'seo', 'fields'];
const sideDefault = ['publish', 'categories', 'tags', 'featured'];

/** A WordPress-style meta box: title bar with move up/down and collapse. */
function Box({ id, title, children, first, last, move, closed, toggle }: { id: string; title: string; children: ReactNode; first: boolean; last: boolean; move: (id: string, d: -1 | 1) => void; closed: boolean; toggle: (id: string) => void }) {
  return (
    <section className={`wpx-box${closed ? ' closed' : ''}`}>
      <header>
        <h2 onClick={() => toggle(id)}>{title}</h2>
        <div>
          <button type="button" aria-label={`Move ${title} up`} disabled={first} onClick={() => move(id, -1)}><Icon name="lucide:chevron-up" size={16} /></button>
          <button type="button" aria-label={`Move ${title} down`} disabled={last} onClick={() => move(id, 1)}><Icon name="lucide:chevron-down" size={16} /></button>
          <button type="button" aria-label={closed ? `Open ${title}` : `Close ${title}`} aria-expanded={!closed} onClick={() => toggle(id)}><Icon name={closed ? 'lucide:triangle' : 'lucide:triangle'} size={10} className={closed ? 'rot' : ''} /></button>
        </div>
      </header>
      {!closed && <div className="wpx-in">{children}</div>}
    </section>
  );
}

export default function PostEditor({ id }: { id: string }) {
  const router = useRouter();
  const toast = useToast();
  const isNew = id === 'new';
  const [p, setP] = useState<Post>(blank);
  const [loaded, setLoaded] = useState(isNew);
  const [dirty, setDirty] = useState(false);
  const [saving, setSaving] = useState(false);
  const [slugTouched, setSlugTouched] = useState(!isNew);
  const [cats, setCats] = useState<string[]>(defaultCats);
  const [catTab, setCatTab] = useState<'all' | 'used'>('all');
  const [catAdd, setCatAdd] = useState<string | null>(null);
  const [counts, setCounts] = useState<{ cats: Record<string, number>; tags: Record<string, number> }>({ cats: {}, tags: {} });
  const [tagIn, setTagIn] = useState('');
  const [showTags, setShowTags] = useState(false);
  const [edit, setEdit] = useState<'' | 'status' | 'vis' | 'date'>('');
  const [media, setMedia] = useState(false);
  const [preview, setPreview] = useState(false);
  const [slugEdit, setSlugEdit] = useState(false);
  const [order, setOrder] = useState({ main: mainDefault, side: sideDefault });
  const [closed, setClosed] = useState<string[]>([]);
  const draftKey = `gt-post-draft-${id}`;

  useEffect(() => {
    try { const s = JSON.parse(localStorage.getItem('gt-post-boxes') ?? 'null'); if (s) { const keep = (a: string[], d: string[]) => [...a.filter((x) => d.includes(x)), ...d.filter((x) => !a.includes(x))]; setOrder({ main: keep(s.main ?? [], mainDefault), side: keep(s.side ?? [], sideDefault) }); setClosed(s.closed ?? []); } } catch { /* ignore */ }
  }, []);
  const saveLayout = (o: typeof order, c: string[]) => { try { localStorage.setItem('gt-post-boxes', JSON.stringify({ ...o, closed: c })); } catch { /* ignore */ } };
  const move = (col: 'main' | 'side') => (bid: string, d: -1 | 1) => setOrder((o) => { const a = [...o[col]], i = a.indexOf(bid); [a[i], a[i + d]] = [a[i + d], a[i]]; const n = { ...o, [col]: a }; saveLayout(n, closed); return n; });
  const toggle = (bid: string) => setClosed((c) => { const n = c.includes(bid) ? c.filter((x) => x !== bid) : [...c, bid]; saveLayout(order, n); return n; });

  useEffect(() => {
    api('/api/admin/categories?size=200').then((d) => setCats((c) => [...new Set([...c, ...d.rows.map((r: { name: string }) => r.name)])])).catch(() => {});
    api('/api/admin/posts?size=200').then((d) => {
      const cc: Record<string, number> = {}, tt: Record<string, number> = {};
      for (const r of d.rows) { for (const c of r.categories ?? [r.category]) if (c) cc[c] = (cc[c] ?? 0) + 1; for (const t of r.tags ?? []) tt[t] = (tt[t] ?? 0) + 1; }
      setCounts({ cats: cc, tags: tt }); setCats((c) => [...new Set([...c, ...Object.keys(cc)])]);
    }).catch(() => {});
  }, []);

  useEffect(() => {
    if (isNew) return;
    api(`/api/admin/posts/${id}`).then((d) => {
      const doc = d.doc;
      setP({ ...blank, ...doc, categories: doc.categories?.length ? doc.categories : doc.category ? [doc.category] : [], body: doc.format === 'html' ? doc.body : markdownToHtml(doc.body ?? '') });
      setLoaded(true);
    }).catch((e) => { toast(e.message, true); setLoaded(true); });
  }, [id, isNew, toast]);

  useEffect(() => {
    if (!loaded) return;
    try { const d = localStorage.getItem(draftKey); if (d && window.confirm('Restore your unsaved changes from the last session?')) { setP({ ...blank, ...JSON.parse(d) }); setDirty(true); } else localStorage.removeItem(draftKey); } catch { /* storage blocked */ }
  }, [loaded, draftKey]);
  useEffect(() => {
    if (!dirty) return;
    const t = setTimeout(() => { try { localStorage.setItem(draftKey, JSON.stringify(p)); } catch { /* ignore */ } }, 2000);
    return () => clearTimeout(t);
  }, [p, dirty, draftKey]);
  useEffect(() => {
    const warn = (e: BeforeUnloadEvent) => { if (dirty) e.preventDefault(); };
    window.addEventListener('beforeunload', warn); return () => window.removeEventListener('beforeunload', warn);
  }, [dirty]);

  const set = <K extends keyof Post>(k: K, v: Post[K]) => { setP((x) => ({ ...x, [k]: v })); setDirty(true); };
  const setTitle = (v: string) => { setP((x) => ({ ...x, title: v, slug: slugTouched ? x.slug : slugify(v) })); setDirty(true); };
  const seo = useMemo(() => analyse({ title: p.title, metaTitle: p.metaTitle, metaDescription: p.metaDescription || p.excerpt, slug: p.slug, keyword: p.focusKeyword, body: htmlToPseudoMd(p.body), image: p.image, imageAlt: p.imageAlt }), [p]);
  const tone = seo.score >= 75 ? 'good' : seo.score >= 45 ? 'mid' : 'low';

  async function save(kind: 'draft' | 'publish' | 'keep') {
    const body: Post = { ...p };
    if (kind === 'draft') body.status = 'draft';
    else if (kind === 'publish') body.status = body.date && new Date(body.date).getTime() > Date.now() ? 'scheduled' : 'published';
    if (body.status === 'published' && !body.date) body.date = new Date().toISOString();
    if (body.status === 'scheduled' && !body.date) { toast('Pick a date to schedule this post.', true); setEdit('date'); return; }
    setSaving(true);
    try {
      const d = isNew ? await api('/api/admin/posts', { body }) : await api(`/api/admin/posts/${id}`, { method: 'PUT', body });
      setP({ ...blank, ...d.doc, categories: d.doc.categories ?? [], body: d.doc.body }); setDirty(false);
      try { localStorage.removeItem(draftKey); } catch { /* ignore */ }
      toast(body.status === 'draft' ? 'Draft saved' : body.status === 'scheduled' ? 'Post scheduled' : 'Post saved. Use Publish site to rebuild.');
      if (isNew) router.replace(`/admin/posts/${d.doc._id}`);
    } catch (e) { toast(e instanceof Error ? e.message : 'Save failed', true); }
    setSaving(false);
  }
  async function trash() {
    if (isNew || !confirmDelete(`“${p.title || 'this post'}”`)) return;
    try { await api(`/api/admin/posts/${id}`, { method: 'DELETE' }); setDirty(false); router.push('/admin/posts'); } catch (e) { toast(e instanceof Error ? e.message : 'Failed', true); }
  }
  async function addCategory() {
    const name = (catAdd ?? '').trim(); if (!name) return;
    try { await api('/api/admin/categories', { body: { name } }); setCats((c) => [...new Set([...c, name])]); set('categories', [...p.categories, name]); setCatAdd(null); } catch (e) { toast(e instanceof Error ? e.message : 'Failed', true); }
  }
  const addTags = (raw: string) => { const t = raw.split(',').map((x) => x.trim()).filter((x) => x && !p.tags.includes(x)); if (t.length) set('tags', [...p.tags, ...t].slice(0, 12)); setTagIn(''); };

  if (!loaded) return <p className="ad-empty">Loading…</p>;
  const live = p.status === 'published';
  const catList = catTab === 'all' ? [...cats].sort() : [...cats].sort((a, b) => (counts.cats[b] ?? 0) - (counts.cats[a] ?? 0)).slice(0, 8);
  const statusLabel = { draft: 'Draft', published: 'Published', scheduled: 'Scheduled' }[p.status];
  const when = p.status === 'published' ? `Published on: ${fmt(p.date)}` : p.date ? `Schedule for: ${fmt(p.date)}` : 'Publish immediately';

  const boxes: Record<string, { title: string; body: ReactNode }> = {
    publish: { title: 'Publish', body: (
      <div className="wpx-pub">
        <div className="wpx-pub-top"><button type="button" className="wp-btn" onClick={() => save('draft')} disabled={saving}>{live ? 'Switch to Draft' : 'Save Draft'}</button><button type="button" className="wp-btn" onClick={() => setPreview(true)}>Preview</button></div>
        <ul className="wpx-meta">
          <li><Icon name="lucide:map-pin" size={15} /> Status: <b>{statusLabel}</b> {edit === 'status' ? <span className="wpx-edit"><select value={p.status} onChange={(e) => set('status', e.target.value as Post['status'])}><option value="draft">Draft</option><option value="published">Published</option><option value="scheduled">Scheduled</option></select><button type="button" className="wp-btn sm" onClick={() => setEdit('')}>OK</button></span> : <button type="button" className="wpx-link" onClick={() => setEdit('status')}>Edit</button>}</li>
          <li><Icon name="lucide:eye" size={15} /> Visibility: <b>{p.visibility === 'public' ? 'Public' : 'Private'}</b> {edit === 'vis' ? <span className="wpx-edit"><select value={p.visibility} onChange={(e) => set('visibility', e.target.value as Post['visibility'])}><option value="public">Public</option><option value="private">Private (hidden)</option></select><button type="button" className="wp-btn sm" onClick={() => setEdit('')}>OK</button></span> : <button type="button" className="wpx-link" onClick={() => setEdit('vis')}>Edit</button>}</li>
          <li><Icon name="lucide:calendar" size={15} /> {when} {edit === 'date' ? <span className="wpx-edit"><input type="datetime-local" value={toLocal(p.date)} onChange={(e) => set('date', e.target.value ? new Date(e.target.value).toISOString() : '')} /><button type="button" className="wp-btn sm" onClick={() => setEdit('')}>OK</button>{p.date && p.status !== 'published' && <button type="button" className="wpx-link" onClick={() => { set('date', ''); setEdit(''); }}>Now</button>}</span> : <button type="button" className="wpx-link" onClick={() => setEdit('date')}>Edit</button>}</li>
        </ul>
        <label className="wpx-check"><input type="checkbox" checked={p.featured} onChange={(e) => set('featured', e.target.checked)} /> Feature this post at the top of the blog page</label>
        <label className="wpx-label">Author<input value={p.author} onChange={(e) => set('author', e.target.value)} placeholder="GTech Editorial Team" /></label>
        <div className="wpx-pub-foot">{!isNew ? <button type="button" className="wpx-trash" onClick={trash}>Move to Trash</button> : <span />}<button type="button" className="wp-btn primary" onClick={() => save(live ? 'keep' : 'publish')} disabled={saving}>{live ? 'Update' : p.date && new Date(p.date).getTime() > Date.now() ? 'Schedule' : 'Publish'}</button></div>
      </div>) },
    categories: { title: 'Categories', body: (
      <div>
        <div className="wpx-tabs"><button type="button" className={catTab === 'all' ? 'on' : ''} onClick={() => setCatTab('all')}>All Categories</button><button type="button" className={catTab === 'used' ? 'on' : ''} onClick={() => setCatTab('used')}>Most Used</button></div>
        <div className="wpx-cats">{catList.map((c) => <label key={c}><input type="checkbox" checked={p.categories.includes(c)} onChange={(e) => set('categories', e.target.checked ? [...p.categories, c] : p.categories.filter((x) => x !== c))} /> {c}</label>)}</div>
        {catAdd === null ? <button type="button" className="wpx-link plus" onClick={() => setCatAdd('')}>+ Add Category</button> : <div className="wpx-addcat"><input autoFocus value={catAdd} onChange={(e) => setCatAdd(e.target.value)} onKeyDown={(e) => { if (e.key === 'Enter') { e.preventDefault(); addCategory(); } }} placeholder="New category name" /><button type="button" className="wp-btn sm" onClick={addCategory}>Add</button></div>}
      </div>) },
    featured: { title: 'Featured image', body: (
      <div>
        {p.image && <>{/* eslint-disable-next-line @next/next/no-img-element */}<img className="wpx-feat" src={p.image} alt={p.imageAlt} /></>}
        <button type="button" className="wpx-link" onClick={() => setMedia(true)}>{p.image ? 'Replace featured image' : 'Set featured image'}</button>
        {p.image && <><label className="wpx-label">Alt text<input value={p.imageAlt} onChange={(e) => set('imageAlt', e.target.value)} /></label><button type="button" className="wpx-trash" onClick={() => { set('image', ''); set('imageAlt', ''); }}>Remove featured image</button></>}
      </div>) },
    excerpt: { title: 'Excerpt', body: <><textarea rows={3} value={p.excerpt} onChange={(e) => set('excerpt', e.target.value)} /><p className="wpx-help"><Count n={p.excerpt.length} max={200} /> A short summary shown on blog cards and used as the default description.</p></> },
    seo: { title: 'SEO Settings', body: (
      <div>
        <div className="serp"><small>gtechdigital.co.uk › blogs › {p.slug || 'url-slug'}</small><b>{(p.metaTitle || p.title || 'Post title').slice(0, 60)}</b><p>{(p.metaDescription || p.excerpt || 'Your meta description appears here.').slice(0, 160)}</p></div>
        <label className="wpx-label">Meta Title<input value={p.metaTitle} onChange={(e) => set('metaTitle', e.target.value)} placeholder="Custom SEO Title (max 60 chars)" /></label>
        <p className="wpx-help"><Count n={(p.metaTitle || p.title).length} max={60} /> Recommended: 50–60 characters.</p>
        <label className="wpx-label">Meta Description<textarea rows={3} value={p.metaDescription} onChange={(e) => set('metaDescription', e.target.value)} placeholder="Custom SEO Description (max 160 chars)" /></label>
        <p className="wpx-help"><Count n={(p.metaDescription || p.excerpt).length} max={160} /> Recommended: 120–160 characters.</p>
        <label className="wpx-label">Focus keyword<input value={p.focusKeyword} onChange={(e) => set('focusKeyword', e.target.value)} placeholder="e.g. technical seo checklist" /></label>
        <label className="wpx-label">Canonical URL<input value={p.canonical} onChange={(e) => set('canonical', e.target.value)} placeholder="Only if first published elsewhere" /></label>
        <label className="wpx-check"><input type="checkbox" checked={p.noindex} onChange={(e) => set('noindex', e.target.checked)} /> Ask search engines not to index this post (noindex)</label>
        <div className="seo-list"><div className={`seo-score ${tone}`}><strong>{seo.score}</strong><span>SEO score</span></div>
          <ul>{seo.checks.map((c) => <li key={c.label} className={c.ok === true ? 'ok' : c.ok === 'warn' ? 'warn' : 'bad'}><Icon name={c.ok === true ? 'lucide:circle-check' : c.ok === 'warn' ? 'lucide:circle-alert' : 'lucide:circle-x'} size={16} /><span>{c.label}{c.ok !== true && c.hint && <small>{c.hint}</small>}</span></li>)}</ul></div>
      </div>) },
    fields: { title: 'Custom Fields', body: (
      <div>
        {p.customFields.length > 0 && <table className="wpx-cf"><thead><tr><th>Name</th><th>Value</th><th /></tr></thead><tbody>{p.customFields.map((f, i) => (
          <tr key={i}><td><input value={f.name} onChange={(e) => set('customFields', p.customFields.map((x, n) => (n === i ? { ...x, name: e.target.value } : x)))} /></td><td><textarea rows={2} value={f.value} onChange={(e) => set('customFields', p.customFields.map((x, n) => (n === i ? { ...x, value: e.target.value } : x)))} /></td><td><button type="button" className="ad-ico" aria-label="Delete field" onClick={() => set('customFields', p.customFields.filter((_, n) => n !== i))}><Icon name="lucide:trash-2" size={15} /></button></td></tr>
        ))}</tbody></table>}
        <button type="button" className="wp-btn" onClick={() => set('customFields', [...p.customFields, { name: '', value: '' }])}>Add Custom Field</button>
        <p className="wpx-help">Extra information stored with the post (for example “reviewed-by”). Developers can use it in templates.</p>
      </div>) },
    tags: { title: 'Tags', body: (
      <div>
        <div className="wpx-tagin"><input value={tagIn} onChange={(e) => setTagIn(e.target.value)} onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); addTags(tagIn); } }} aria-label="Add tag" /><button type="button" className="wp-btn" onClick={() => addTags(tagIn)}>Add</button></div>
        <p className="wpx-help">Separate tags with commas</p>
        {p.tags.length > 0 && <ul className="wpx-chips">{p.tags.map((t) => <li key={t}><button type="button" aria-label={`Remove ${t}`} onClick={() => set('tags', p.tags.filter((x) => x !== t))}><Icon name="lucide:x" size={12} /></button>{t}</li>)}</ul>}
        <button type="button" className="wpx-link" onClick={() => setShowTags(!showTags)}>Choose from the most used tags</button>
        {showTags && <p className="wpx-used">{Object.entries(counts.tags).sort((a, b) => b[1] - a[1]).slice(0, 20).map(([t, n]) => <button key={t} type="button" onClick={() => addTags(t)}>{t} <small>({n})</small></button>)}{Object.keys(counts.tags).length === 0 && <em>No tags used yet.</em>}</p>}
      </div>) },
  };
  const col = (key: 'main' | 'side') => order[key].map((bid, i, a) => <Box key={bid} id={bid} title={boxes[bid].title} first={i === 0} last={i === a.length - 1} move={move(key)} closed={closed.includes(bid)} toggle={toggle}>{boxes[bid].body}</Box>);

  return (
    <div className="wpx" onKeyDown={(e) => { if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); save(live ? 'keep' : 'draft'); } }}>
      <div className="wpx-bar"><Link href="/admin/posts" className="ad-btn ghost small"><Icon name="lucide:arrow-left" size={15} /> All Posts</Link><span className="ed-state">{saving ? 'Saving…' : dirty ? 'Unsaved changes' : 'All changes saved'}</span>
        <span className="wpx-bar-grow" />
        <button type="button" className="wp-btn" onClick={() => save(live ? 'keep' : 'draft')} disabled={saving}>{live ? 'Save' : 'Save Draft'}</button>
        <button type="button" className="wp-btn primary" onClick={() => save(live ? 'keep' : 'publish')} disabled={saving}>{live ? 'Update' : p.date && new Date(p.date).getTime() > Date.now() ? 'Schedule' : 'Publish'}</button></div>
      <h1 className="wpx-h1">{isNew ? 'Add Post' : 'Edit Post'}</h1>
      <div className="wpx-grid">
        <div className="wpx-main">
          <input className="wpx-title" value={p.title} onChange={(e) => setTitle(e.target.value)} placeholder="Add title" aria-label="Post title" />
          <div className="wpx-permalink">
            <span>Permalink:</span>
            {slugEdit ? (
              <><span className="wpx-plbase">gtechdigital.co.uk/blogs/</span><input autoFocus value={p.slug} onChange={(e) => { setSlugTouched(true); set('slug', slugify(e.target.value)); }} onKeyDown={(e) => { if (e.key === 'Enter' || e.key === 'Escape') { e.preventDefault(); setSlugEdit(false); } }} aria-label="Slug" placeholder="url-slug" />
                <button type="button" className="wp-btn sm" onClick={() => setSlugEdit(false)}>OK</button>
                {slugTouched && <button type="button" className="wpx-link" onClick={() => { setSlugTouched(false); set('slug', slugify(p.title)); }}>Reset from title</button>}</>
            ) : (
              <>{live && p.slug ? <a href={`/blogs/${p.slug}`} target="_blank" rel="noopener noreferrer">gtechdigital.co.uk/blogs/<b>{p.slug}</b></a> : <span>gtechdigital.co.uk/blogs/<b>{p.slug || 'url-slug'}</b></span>}
                <button type="button" className="wp-btn sm" onClick={() => setSlugEdit(true)}>Edit</button></>
            )}
          </div>
          <VisualEditor value={p.body} onChange={(html) => set('body', html)} />
          {col('main')}
        </div>
        <aside className="wpx-side">{col('side')}</aside>
      </div>

      {media && <MediaModal title="Set featured image" onClose={() => setMedia(false)} onPick={(m) => { set('image', m.url); setMedia(false); }} />}
      {preview && (
        <div className="ad-modal" role="dialog" aria-modal="true" aria-label="Preview" onMouseDown={(e) => { if (e.target === e.currentTarget) setPreview(false); }}>
          <div className="ad-modal-box wpx-prev">
            <header><h2>Preview</h2><button className="ad-ico" onClick={() => setPreview(false)} aria-label="Close"><Icon name="lucide:x" size={18} /></button></header>
            {p.image && <>{/* eslint-disable-next-line @next/next/no-img-element */}<img src={p.image} alt={p.imageAlt} className="wpx-prev-img" /></>}
            <h1 className="wpx-prev-h">{p.title || 'Untitled'}</h1>
            <article className="bp-body"><HtmlBody html={p.body} /></article>
          </div>
        </div>
      )}
    </div>
  );
}
