import Link from 'next/link';
import InquiryForm from '@/components/InquiryForm';

import { homeSection } from '@/lib/content';
import { Rt } from '@/components/Rt';
import Hl from '@/components/Hl';

export default async function InquirySection() {
  const h = await homeSection('inquiry');
  return (
    <section className="iq" id="inquiry">
      <div className="wrap iq-grid">
        <div className="iq-copy">
          <h2>{h.heading.includes('[[') ? <Hl>{h.heading}</Hl> : h.heading}</h2>
          <span className="iq-rule" />
          <Rt as="p" html={h.paras?.[0] ?? ''} />
          <Link className="btn" href="/contact">Schedule a meeting</Link>
        </div>
        <InquiryForm />
      </div>
    </section>
  );
}
