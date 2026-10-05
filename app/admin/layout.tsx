import type { Metadata } from 'next';
import { ToastProvider } from '@/components/admin/ui';
import './admin.css';

export const metadata: Metadata = { title: 'Admin | GTech Digital', robots: { index: false, follow: false } };
export const dynamic = 'force-dynamic';

export default function AdminRoot({ children }: { children: React.ReactNode }) {
  return <div className="ad-root"><ToastProvider>{children}</ToastProvider></div>;
}
