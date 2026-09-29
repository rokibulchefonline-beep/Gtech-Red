import Link from 'next/link';

export default function PageHead({ title, sub, back }: { title: string; sub?: string; back?: { href: string; label: string } }) {
  return (
    <section className="page-hd">
      <div className="wrap">
        {back && <Link href={back.href}>&larr; {back.label}</Link>}
        <h1>{title}</h1>
        {sub && <p>{sub}</p>}
      </div>
    </section>
  );
}
