import { redirect } from 'next/navigation';
import AuthForm from '@/components/admin/AuthForm';
import { count } from '@/lib/store';
import { scoped } from '@/lib/mongo';

export default async function Setup() {
  return scoped(async () => {
  let dbError = '';
  const n = await count('users').catch((e: Error) => { dbError = e.message; return 0; });
  if (n > 0) redirect('/admin/login');
  return <AuthForm mode="setup" needsKey={!!process.env.SETUP_KEY} dbError={dbError} />;
});
}
