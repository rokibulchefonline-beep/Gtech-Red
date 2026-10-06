"""Compares the SEO head of two saved pages: title, meta tags, canonical and the JSON-LD (parsed, key by key).
The deliberate Blade differences are allowed: absolute canonical and image URLs, extra Open Graph/Twitter tags.
Usage: python3 scripts/parity/compare-seo.py next.html blade.html"""
import sys,re,json,html
def meta(h):
    head=re.search(r'<head>(.*?)</head>',h,re.S).group(1)
    t=re.search(r'<title>(.*?)</title>',head,re.S); out={'title':html.unescape(t.group(1)) if t else None}
    for m in re.finditer(r'<meta ([^>]*?)/?>',head):
        a=dict((k,html.unescape(v)) for k,v in re.findall(r'([\w:-]+)="([^"]*)"',m.group(1)))
        k=a.get('name') or a.get('property')
        if k and k not in('viewport',) and 'content' in a: out[k]=a['content']
    c=re.search(r'<link rel="canonical" href="([^"]*)"',head); out['canonical']=c and c.group(1)
    out['ld']=[json.loads(x) for x in re.findall(r'<script type="application/ld\+json">(.*?)</script>',h,re.S)]
    return out
a=meta(open(sys.argv[1]).read()); b=meta(open(sys.argv[2]).read())
BASE='https://www.gtechdigital.co.uk'
# Expected, deliberate differences: absolute canonical and images; extra Open Graph/Twitter tags on Blade.
def fix(v): return v if not isinstance(v,str) else (BASE+v if v.startswith('/') else v.replace('http://localhost:3000',BASE))
diffs=[]
for k in sorted(set(a)|set(b)):
    if k=='ld': continue
    va,vb=a.get(k),b.get(k)
    if k in('canonical','og:image','twitter:image'): va=fix(va)
    if va is None and k.startswith(('og:','twitter:')): continue  # added on Blade
    if k=='canonical' and va is None: continue
    if va!=vb: diffs.append(f'{k}: next={va!r} blade={vb!r}')
def walk(x,y,p=''):
    if type(x)!=type(y): diffs.append(f'ld{p}: next={json.dumps(x)[:150]} blade={json.dumps(y)[:150]}'); return
    if isinstance(x,dict):
        for k in list(x)+[k for k in y if k not in x]:
            if k not in y or k not in x: diffs.append(f'ld{p}.{k}: next={json.dumps(x.get(k))[:150]} blade={json.dumps(y.get(k))[:150]}')
            else: walk(x[k],y[k],f'{p}.{k}')
        if list(x)!=list(y) and set(x)==set(y): diffs.append(f'ld{p}: key order differs')
    elif isinstance(x,list):
        if len(x)!=len(y): diffs.append(f'ld{p}: length {len(x)} vs {len(y)}')
        for i,(u,v) in enumerate(zip(x,y)): walk(u,v,f'{p}[{i}]')
    elif x!=y: diffs.append(f'ld{p}: next={x!r} blade={y!r}')
walk(a['ld'],b['ld'])
print('SAME' if not diffs else 'DIFF\n   '+'\n   '.join(diffs[:12]))
