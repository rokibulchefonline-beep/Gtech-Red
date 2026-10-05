import LogoManager from '@/components/admin/LogoManager';

export default function Page() {
  return <LogoManager resource="partners" noun="badge" title="Partner badges" sub="Certifications and platform partnerships." where={[
    { page: 'Home', place: 'Platform Partners strip (all badges)', href: '/', rule: () => true },
    { page: 'Home', place: '“Who We Are” section (first 4 badges)', href: '/', rule: (i) => i < 4 },
    { page: 'About', place: 'partner strip under the hero (all badges)', href: '/about', rule: () => true },
    { page: 'Contact', place: 'partner strip (all badges)', href: '/contact', rule: () => true },
  ]} />;
}
