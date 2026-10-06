"""Compares the <main> of two HTML pages after normalising them (attribute order, whitespace, entities, data-*
attributes, hidden elements, scripts and templates, Next.js image URLs).
Usage: python3 scripts/parity/compare-html.py next.html blade.html"""
import sys,re,html,urllib.parse
from html.parser import HTMLParser
VOID={'br','hr','img','input','meta','link','path','circle','rect','line','polyline','polygon','ellipse','source'}
class P(HTMLParser):
    def __init__(s): super().__init__(convert_charrefs=True); s.out=[]; s.skip=0
    def handle_starttag(s,t,a):
        a=[(k,v) for k,v in a if not k.startswith('data-') and k not in('srcset','fetchpriority','decoding')]
        if s.skip or t in('script','template') or any(k=='hidden' for k,_ in a):
            if t not in VOID: s.skip+=1
            return
        if t=='img': a=[(k,re.sub(r'^/_next/image\?url=([^&]+).*',lambda m:html.unescape(urllib.parse.unquote(m.group(1))),v or '')) if k=='src' else (k,v) for k,v in a]
        s.out.append('<'+t+''.join(f' {k}="{" ".join((v or "").split())}"' for k,v in sorted(a))+'>')
    def handle_endtag(s,t):
        if t in VOID: return
        if s.skip: s.skip-=1; return
        s.out.append('</'+t+'>')
    def handle_startendtag(s,t,a):
        s.handle_starttag(t,a)
    def handle_data(s,d):
        if not s.skip and d.strip(): s.out.append(' '.join(d.split()))
def main(h):
    m=re.search(r'<main[^>]*>(.*)</main>',h,re.S); h=m.group(1) if m else h
    return re.sub(r'<!-- -->','',h)
def canon(h): p=P(); p.feed(main(h)); return p.out
a=canon(open(sys.argv[1]).read()); b=canon(open(sys.argv[2]).read())
if a==b: print('SAME',len(a)); sys.exit(0)
i=next((i for i in range(min(len(a),len(b))) if a[i]!=b[i]),min(len(a),len(b)))
print(f'DIFF at {i}/{len(a)} vs {len(b)}')
for j in range(max(0,i-3),i+4):
    print('  N:',a[j][:220] if j<len(a) else '-'); print('  B:',b[j][:220] if j<len(b) else '-')
sys.exit(1)
