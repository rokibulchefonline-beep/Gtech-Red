import { industry } from './build';

// Entities: private clinics, dentists, aesthetics, physiotherapy, opticians, CQC, GMC/GDC, ASA/CAP
// healthcare advertising rules, patient acquisition, online booking, Google reviews, YMYL content.
export default industry({
  slug: 'healthcare',
  name: 'Healthcare',
  kw: 'Healthcare Marketing',
  metaTitle: 'Healthcare Marketing Agency UK | Clinic & Dental Marketing | GTech Digital',
  metaDescription: 'UK healthcare marketing agency for private clinics, dentists and aesthetics: compliant SEO, Google Ads, reviews and booking websites that attract new patients.',
  hero: {
    title: 'Healthcare Marketing for',
    highlight: 'More Patients',
    lead: 'We help private clinics, dentists and healthcare providers attract the right patients with compliant, trustworthy marketing and easy online booking.',
    points: ['Free clinic audit', 'ASA and CQC-aware', 'Bookings tracked'],
  },
  what: {
    heading: 'Marketing Patients Can Trust',
    para: 'Patients choose healthcare providers carefully, reading reviews and comparing clinics before they book. Your marketing must build trust, follow strict advertising rules and make booking simple.',
    bullets: ['Local visibility for treatments and conditions', 'Compliant ads that follow ASA and CAP rules', 'Calm, accessible websites with online booking'],
  },
  impact: { heading: 'More Patients, Fully Compliant', stats: [['1,480', 'Average new patients a year'], ['+72%', 'Average growth in online bookings'], ['£28', 'Average cost per new patient'], ['4.9', 'Average Google rating']] },
  media: [
    { nav: 'Patients', topic: 'Patient acquisition', heading: 'A Steady Flow of New Patients', para: 'We focus on the treatments that matter most to your clinic and track every booking back to its source.', bullets: ['Treatment-focused campaigns', 'Online booking integration', 'Call tracking', 'Cost per patient reporting'], alt: 'New patient bookings growing with online bookings, calls and cost per patient' },
    { nav: 'Channels', topic: 'Channels', heading: 'Be the Clinic Patients Find First', para: 'Most patients start with Google. We put your clinic at the top of local results, Maps and AI answers.', bullets: ['Local SEO and Google Maps', 'Treatment and condition content', 'Google and Meta ads', 'Review generation'], alt: 'How patients find you: Google Maps, SEO, Google Ads, Meta ads, reviews and recall emails' },
    { nav: 'Booking', topic: 'Patient journey', heading: 'Booking That Takes a Minute', para: 'Clear treatment pages, transparent pricing and 24/7 online booking turn interest into appointments.', bullets: ['Treatment and price pages', '24/7 online booking', 'Reminder and recall emails', 'Reduced no-shows'], alt: 'Patient journey from treatment page visits to bookings and attended appointments' },
    { nav: 'Compliance', topic: 'Compliance', heading: 'Marketing That Stays Within the Rules', para: 'Healthcare advertising is tightly regulated. We follow ASA, CAP and professional body guidance and protect patient data.', bullets: ['ASA and CAP code compliance', 'CQC and GMC-aware content', 'Clinician-reviewed copy', 'GDPR-safe forms'], alt: 'Compliant healthcare marketing checklist with ASA, CQC, GDPR and clinician review' },
  ],
  cards: [
    ['lucide:map-pin', 'Local SEO', 'Top of Maps for treatments.'], ['lucide:search', 'Healthcare SEO', 'Trustworthy treatment content.'],
    ['simple-icons:googleads', 'Google Ads', 'Compliant patient campaigns.'], ['simple-icons:instagram', 'Social Media', 'Educational, on-brand content.'],
    ['lucide:star', 'Reviews', 'More patient reviews.'], ['lucide:calendar-check', 'Online Booking', 'Integrated booking systems.'],
    ['lucide:monitor', 'Clinic Websites', 'Calm and accessible.'], ['lucide:chart-column-increasing', 'Reporting', 'Patients and revenue.'],
  ],
  reviews: [['Anita G', 'Practice Manager, Dental Clinic', 'New patient calls from Google have more than doubled in six months.'], ['Fatima Z', 'Director, Aesthetics Clinic', 'Patients now book online at any time, and our diary stays full.'], ['Dr James L', 'Owner, Physiotherapy Practice', 'Professional, compliant marketing that our team is proud of.']],
  faqs: [
    ['How can a private clinic attract more patients?', 'Rank in local search and Google Maps, collect genuine reviews, publish clear treatment and price information and offer easy online booking. Targeted ads speed up results.'],
    ['Are there rules for healthcare advertising?', 'Yes. UK healthcare ads must follow ASA and CAP codes, and some treatments, such as prescription medicines, cannot be advertised to the public. We keep campaigns compliant.'],
    ['Do you work with dentists?', 'Yes. We work with dental practices, aesthetics clinics, physiotherapists, opticians and other private healthcare providers.'],
    ['Can you integrate our booking system?', 'Yes. We connect most clinic booking systems to your website and track bookings in GA4.'],
    ['How do you handle patient data?', 'We use secure, GDPR-compliant forms and never put sensitive health data into ad platforms.'],
    ['Do you need a contract?', 'No. We work on rolling monthly terms.'],
  ],
  related: ['local-seo', 'search-engine-optimization', 'google-ads', 'reputation-management', 'website-design', 'facebook-marketing'],
});
