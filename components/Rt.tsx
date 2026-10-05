import { sanitizeHtml } from '@/lib/sanitize-html';

/** Text edited in the admin: bold, italic and links are kept, everything unsafe is removed. */
export function Rt({ html, as: Tag = 'span', className }: { html: string; as?: 'span' | 'p' | 'div'; className?: string }) {
  return <Tag className={className} dangerouslySetInnerHTML={{ __html: sanitizeHtml(html ?? '') }} />;
}
