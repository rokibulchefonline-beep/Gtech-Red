import { sanitizeHtml } from '@/lib/sanitize-html';

/** Renders a post body written in the visual editor. Sanitized again here as defence in depth. */
export default function HtmlBody({ html }: { html: string }) {
  return <div className="bp-html" dangerouslySetInnerHTML={{ __html: sanitizeHtml(html) }} />;
}
