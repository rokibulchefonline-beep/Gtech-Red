import { notFound } from 'next/navigation';
import UsersManager from '@/components/admin/UsersManager';
import { can, currentUser } from '@/lib/auth';

export default async function Users() {
  const u = await currentUser();
  if (!u || !can(u.role, 'users')) notFound();
  return <UsersManager meId={u.id} />;
}
