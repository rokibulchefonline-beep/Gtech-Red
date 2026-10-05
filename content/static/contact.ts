import type { ServiceContent } from '@/content/types';

// Editable copy of the Contact page (Admin > Pages > Contact).
export const contactContent: ServiceContent = {
  slug: 'contact',
  short: 'Contact',
  metaTitle: 'Contact GTech Digital | Get a Free Proposal | UK Digital Agency',
  metaDescription: 'Contact GTech Digital for a free audit and tailored proposal for SEO, Google Ads, social media, web design or custom software. We reply within one working day.',
  hero: {
    h1: 'Contact [[GTech Digital]] for a Free Marketing Audit and Proposal',
    lead: 'Contact GTech Digital, a UK digital marketing, web design and software agency, for a free audit and tailored proposal within 24 hours, with no obligation and no long contracts.',
    motion: '',
    points: ['Reply within one working day', 'Free audit and proposal', 'No long contracts'],
  },
  sections: [
    { type: 'steps', id: 'next', heading: 'What happens next', steps: [
  { title: 'We review your enquiry', text: 'A specialist looks at your website and goals.' },
  { title: 'Free strategy call', text: 'A 30-minute call to understand your needs.' },
  { title: 'Your proposal', text: 'A clear plan and fixed quote within 24 hours.' },
    ] },
  ],
  faqs: [
  { q: 'How quickly will you reply?', a: 'We reply to every enquiry within one working day, usually much sooner during UK business hours.' },
  { q: 'Is the audit really free?', a: 'Yes. The audit and proposal are free with no obligation. We review your website and marketing and show you the biggest opportunities.' },
  { q: 'Do I need to know which service I need?', a: 'No. Tell us your goals and we will recommend the right mix of services and budget.' },
  { q: 'Can we meet in person?', a: 'Yes. We meet clients in person or by video call, whichever suits you best.' },
  { q: 'Do you have minimum contract terms?', a: 'No. Most of our services run on rolling monthly terms, and projects are priced upfront.' },
  { q: 'What information should I include?', a: 'Your website, what you want to achieve and a rough monthly budget help us prepare a useful proposal.' },
  ],
  related: [],
};
