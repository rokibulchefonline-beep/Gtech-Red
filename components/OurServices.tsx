import ServiceStack from '@/components/ServiceStack';
import Hl from '@/components/Hl';

export default function OurServices() {
  return (
    <section className="ourservices">
      <div className="wrap">
        <h2><Hl>Digital Marketing, Web and Software Services</Hl></h2>
        <p className="os-sub">
          Everything you need to grow online, from one team. Strategy, creative and engineering that
          work together and are measured on real business results.
        </p>
        <ServiceStack />
      </div>
    </section>
  );
}
