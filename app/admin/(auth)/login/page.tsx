import { redirect } from 'next/navigation';
import AuthForm from '@/components/admin/AuthForm';
import { currentUser } from '@/lib/auth';
import { count } from '@/lib/store';

export default async function Login() {
  if (await currentUser()) redirect('/admin');
  let dbError = '';
  const n = await count('users').catch((e: Error) => { dbError = e.message; return 1; });
  if (n === 0) redirect('/admin/setup');
  return <AuthForm mode="login" dbError={dbError} />;
}
