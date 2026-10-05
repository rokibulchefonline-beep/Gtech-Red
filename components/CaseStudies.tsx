import Link from 'next/link';
import CaseCarousel from '@/components/CaseCarousel';
import { listDocs } from '@/lib/mongo';
import Hl from '@/components/Hl';

export default async function CaseStudies() {
  const docs = await listDocs('case_studies', 6);
  return (
    <section className="cases">
      <div className="wrap">
        <h2><Hl>Digital Marketing Case Studies</Hl></h2>
        <p className="os-sub">See how we help brands grow with results you can measure.</p>
        <CaseCarousel docs={docs} />
        <p className="cases-all"><Link className="btn-dark" href="/case-studies">View All Case Studies</Link></p>
      </div>
    </section>
  );
}
