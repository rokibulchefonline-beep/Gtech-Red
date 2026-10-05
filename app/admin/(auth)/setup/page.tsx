import { redirect } from 'next/navigation';
import AuthForm from '@/components/admin/AuthForm';
import { count } from '@/lib/store';

export default async function Setup() {
  if ((await count('users').catch(() => 1)) > 0) redirect('/admin/login');
  return <AuthForm mode="setup" needsKey={!!process.env.SETUP_KEY} />;
}
