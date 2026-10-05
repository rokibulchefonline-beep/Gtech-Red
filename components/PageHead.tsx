import Icon from '@/components/Icon';
import Link from 'next/link';
import Hl from '@/components/Hl';

export default function PageHead({ title, sub, back, icon }: { title: string; sub?: string; back?: { href: string; label: string }; icon?: string }) {
  return (
    <section className="page-hd">
      <div className="wrap">
        {back && <Link href={back.href}>&larr; {back.label}</Link>}
        <h1>{icon && <Icon className="hd-ico" name={icon} size={36} />}<Hl>{title}</Hl></h1>
        {sub && <p>{sub}</p>}
      </div>
    </section>
  );
}
