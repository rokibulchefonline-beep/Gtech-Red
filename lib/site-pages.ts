// Browser-safe helpers for the admin: the list of site pages and the id used for SEO overrides.
export const encodeSeoId = (path: string) => (path === '/' ? 'home' : path.replace(/^\//, '').replace(/\//g, '~'));

export const staticPages = [
  { path: '/', label: 'Home' }, { path: '/services', label: 'Services hub' }, { path: '/industries', label: 'Industries hub' },
  { path: '/about', label: 'About' }, { path: '/contact', label: 'Contact' }, { path: '/case-studies', label: 'Case studies' }, { path: '/blogs', label: 'Blog' },
  { path: '/privacy-policy', label: 'Privacy policy' }, { path: '/terms', label: 'Terms' }, { path: '/cookie-policy', label: 'Cookie policy' },
];
