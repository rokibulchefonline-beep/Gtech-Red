import { redirect } from 'next/navigation';
import AuthForm from '@/components/admin/AuthForm';
import { currentUser } from '@/lib/auth';
import { count } from '@/lib/store';

export default async function Login() {
  if (await currentUser()) redirect('/admin');
  if ((await count('users').catch(() => 1)) === 0) redirect('/admin/setup');
  return <AuthForm mode="login" />;
}
