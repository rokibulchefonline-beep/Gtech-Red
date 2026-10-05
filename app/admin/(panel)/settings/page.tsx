import { notFound } from 'next/navigation';
import SettingsForm from '@/components/admin/SettingsForm';
import { can, currentUser } from '@/lib/auth';

export default async function SettingsPage() {
  const u = await currentUser();
  if (!u || !can(u.role, 'settings')) notFound();
  return <SettingsForm />;
}
