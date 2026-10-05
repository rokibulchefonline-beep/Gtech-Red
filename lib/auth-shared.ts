// Roles and permissions: browser-safe, shared by the admin UI and the server.
export type Role = 'super_admin' | 'admin' | 'editor' | 'sales';
export type Perm = 'content' | 'leads' | 'settings' | 'users';

export const roleInfo: Record<Role, { label: string; perms: Perm[]; about: string }> = {
  super_admin: { label: 'Super admin', perms: ['content', 'leads', 'settings', 'users'], about: 'Full access, including users and roles.' },
  admin: { label: 'Admin', perms: ['content', 'leads', 'settings'], about: 'Content, leads and site settings.' },
  editor: { label: 'Editor', perms: ['content'], about: 'Pages, blog, case studies, logos and SEO.' },
  sales: { label: 'Sales', perms: ['leads'], about: 'Lead management only.' },
};
export const roleList = Object.keys(roleInfo) as Role[];
export const can = (role: string | undefined, p: Perm) => !!role && roleInfo[role as Role]?.perms.includes(p);


export type SessionUser = { id: string; name: string; email: string; role: Role };
