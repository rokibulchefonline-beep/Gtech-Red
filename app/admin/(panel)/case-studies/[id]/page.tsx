import CaseEditor from '@/components/admin/CaseEditor';

export default async function CasePage({ params }: { params: Promise<{ id: string }> }) {
  return <CaseEditor id={(await params).id} />;
}
