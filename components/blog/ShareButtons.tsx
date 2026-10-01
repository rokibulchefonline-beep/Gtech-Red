'use client';

import { useState } from 'react';
import Icon from '@/components/Icon';

export default function ShareButtons({ url, title }: { url: string; title: string }) {
  const [copied, setCopied] = useState(false);
  const u = encodeURIComponent(url), t = encodeURIComponent(title);
  const links = [
    { name: 'LinkedIn', icon: 'simple-icons:linkedin', href: `https://www.linkedin.com/sharing/share-offsite/?url=${u}` },
    { name: 'Facebook', icon: 'simple-icons:facebook', href: `https://www.facebook.com/sharer/sharer.php?u=${u}` },
    { name: 'X', icon: 'simple-icons:x', href: `https://x.com/intent/post?url=${u}&text=${t}` },
    { name: 'WhatsApp', icon: 'simple-icons:whatsapp', href: `https://wa.me/?text=${t}%20${u}` },
  ];
  const copy = async () => {
    try { await navigator.clipboard.writeText(url); setCopied(true); setTimeout(() => setCopied(false), 2000); } catch { /* ignore */ }
  };
  return (
    <div className="bl-share">
      <span>Share:</span>
      {links.map((l) => (
        <a key={l.name} href={l.href} target="_blank" rel="noopener noreferrer" aria-label={`Share on ${l.name}`}><Icon name={l.icon} size={16} /></a>
      ))}
      <button type="button" onClick={copy} aria-label="Copy link">{copied ? <Icon name="lucide:check" size={16} /> : <Icon name="lucide:link" size={16} />}</button>
    </div>
  );
}
