'use client';

import { usePathname } from 'next/navigation';
import type { ReactNode } from 'react';
import SiteMotion from '@/components/SiteMotion';

// The public header, footer, cookie banner and contact popup are not shown inside /admin.
export default function SiteChrome({ header, footer, children }: { header: ReactNode; footer: ReactNode; children: ReactNode }) {
  if (usePathname().startsWith('/admin')) return <>{children}</>;
  return <><SiteMotion />{header}<main>{children}</main>{footer}</>;
}
