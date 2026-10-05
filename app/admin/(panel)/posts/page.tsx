import PostsList from '@/components/admin/PostsList';

export default async function Posts({ searchParams }: { searchParams: Promise<{ status?: string }> }) {
  return <PostsList initialStatus={(await searchParams).status} />;
}
