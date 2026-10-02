// Browser-safe blog helpers (no database imports), shared by server and client components.
export type Post = {
  slug: string;
  title: string;
  excerpt: string;
  category: string;
  date: string;
  image: string;
  featured: boolean;
  body: string;
};

export const readTime = (body: string) => Math.max(1, Math.round(body.split(/\s+/).length / 220));

export const formatDate = (iso: string) =>
  new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
