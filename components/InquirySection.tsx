import Link from 'next/link';
import InquiryForm from '@/components/InquiryForm';

export default function InquirySection() {
  return (
    <section className="iq" id="inquiry">
      <div className="wrap iq-grid">
        <div className="iq-copy">
          <p className="iq-eyebrow">Contact Us</p>
          <h2>Make An Inquiry</h2>
          <span className="iq-rule" />
          <p>
            Now you know about us, we would love to get to know you better. Why not drop us a message today and
            introduce yourself? It could be the beginning of a beautiful relationship.
          </p>
          <Link className="btn" href="/contact">Schedule a meeting</Link>
        </div>
        <InquiryForm />
      </div>
    </section>
  );
}
