import Link from 'next/link';
import Icon from '@/components/Icon';
import { industries } from '@/lib/data';
import { industryIcons } from '@/lib/icons';

export default function IndustriesSection() {
  return (
    <section className="ind">
      <div className="wrap ind-grid">
        <div className="ind-copy">
          <p className="ind-eyebrow">Expertise</p>
          <h2>Industries We Serve</h2>
          <span className="ind-rule" />
          <p>
            We work with a wide range of industries, from e-commerce brands and restaurants to schools,
            healthcare providers and finance firms. Good marketing and software adapt to every niche, but we
            go the extra mile: we take the time to understand your business, your customers and your values.
            It is this personalised approach that sets us apart from the competition.
          </p>
          <Link className="btn" href="/contact">Speak to our experts</Link>
        </div>
        <div className="ind-cards">
          {industries.map((i) => (
            <Link key={i.slug} href={`/industries/${i.slug}`} className="ind-card">
              <span className="ind-ico"><Icon name={industryIcons[i.slug]} size={26} /></span>
              <b>{i.name}</b>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
}
