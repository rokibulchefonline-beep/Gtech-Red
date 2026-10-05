import ServiceStack from '@/components/ServiceStack';
import Hl from '@/components/Hl';

import { homeSection } from '@/lib/content';
import { Rt } from '@/components/Rt';

export default async function OurServices() {
  const h = await homeSection('services');
  return (
    <section className="ourservices">
      <div className="wrap">
        <h2><Hl>{h.heading}</Hl></h2>
        <Rt as="p" className="os-sub" html={(h as { text?: string }).text ?? ''} />
        <ServiceStack />
      </div>
    </section>
  );
}
