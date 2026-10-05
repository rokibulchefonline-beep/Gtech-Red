import ProfileForm from '@/components/admin/ProfileForm';
import { currentUser } from '@/lib/auth';

export default async function Profile() {
  const u = (await currentUser())!;
  return <ProfileForm name={u.name} email={u.email} role={u.role} />;
}
