import LogoManager from '@/components/admin/LogoManager';

export default function Page() {
  return <LogoManager resource="clients" noun="logo" title="Client logos" sub="Brands we have worked with." where={[
    { page: 'Home', place: '“Industry Leading Brands” scrolling strip (all logos)', href: '/', rule: () => true },
    { page: 'Service pages', place: '“Trusted by growing UK brands” row (first 6 logos)', href: '/services/seo', rule: (i) => i < 6 },
  ]} />;
}
