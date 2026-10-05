import { redirect } from 'next/navigation';
import Shell from '@/components/admin/Shell';
import { currentUser } from '@/lib/auth';
import { count } from '@/lib/store';

export default async function PanelLayout({ children }: { children: React.ReactNode }) {
  const user = await currentUser();
  if (!user) redirect((await count('users').catch(() => 0)) === 0 ? '/admin/setup' : '/admin/login');
  return <Shell user={user}>{children}</Shell>;
}
