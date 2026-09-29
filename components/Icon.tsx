import { iconData } from '@/lib/icon-data';

/** Inline SVG from the Iconify ids listed in lib/icons.ts. Inherits text colour. */
export default function Icon({ name, size = 20, className }: { name?: string; size?: number; className?: string }) {
  const d = name ? iconData[name] : undefined;
  if (!d) return null;
  return (
    <svg
      className={className}
      width={size}
      height={size}
      viewBox={`0 0 ${d.w} ${d.h}`}
      fill="currentColor"
      aria-hidden="true"
      dangerouslySetInnerHTML={{ __html: d.body }}
    />
  );
}
