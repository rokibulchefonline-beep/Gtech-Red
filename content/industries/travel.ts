import { industry } from './build';

// Entities: tour operators, travel agents, holiday companies, ATOL, ABTA, destination guides,
// seasonal demand, Google Ads, Pinterest, YouTube, booking engine, multi-currency, repeat travellers.
export default industry({
  slug: 'travel',
  name: 'Travel',
  metaTitle: 'Travel Marketing Agency UK | Tour Operator & Holiday Marketing | GTech Digital',
  metaDescription: 'UK travel marketing agency for tour operators, travel agents and holiday brands: destination SEO, Google Ads, social media and booking websites that sell trips.',
  hero: {
    eyebrow: 'Travel Marketing Agency UK',
    title: 'Travel Marketing That',
    highlight: 'Books Trips',
    lead: 'We help tour operators, travel agents and holiday brands inspire travellers, rank for destinations and convert planning into booked trips.',
    points: ['Free booking audit', 'Seasonal demand planning', 'Bookings and ROAS tracked'],
  },
  what: {
    heading: 'Reaching Travellers From Inspiration to Booking',
    para: 'Travellers spend weeks researching destinations, prices and reviews before booking. Brands that inspire early, rank for destination searches and make booking easy win the trip.',
    bullets: ['Destination SEO and AI search visibility', 'Inspiring social and video content', 'Booking journeys built for trust and speed'],
  },
  impact: { heading: 'More Trips Booked, Season After Season', stats: [['£1.4M', 'Average bookings value a year'], ['6.8x', 'Average Google Ads ROAS'], ['+212%', 'Average destination traffic growth'], ['34%', 'Average repeat travellers']] },
  media: [
    { nav: 'Bookings', eyebrow: 'Bookings growth', heading: 'More Bookings at a Lower Cost', para: 'We plan campaigns around booking windows and peak demand so budget works hardest when travellers decide.', bullets: ['Seasonal campaign planning', 'Google Ads and Performance Max', 'Early booking promotions', 'Revenue and ROAS tracking'], alt: 'Online bookings value growing with trips booked, average booking and ROAS' },
    { nav: 'Channels', eyebrow: 'Inspiration channels', heading: 'Inspire Before They Search', para: 'Pinterest, Instagram and YouTube shape where people want to go. Search then captures the decision.', bullets: ['Destination guides and SEO', 'Pinterest and Instagram content', 'YouTube destination videos', 'Email offers and newsletters'], alt: 'How travellers find you: destination SEO, Google Ads, Pinterest, Instagram, email and YouTube' },
    { nav: 'Journey', eyebrow: 'Booking journey', heading: 'From Research to Booked Trip', para: 'Clear prices, live availability and visible trust signals make travellers confident enough to book.', bullets: ['Live pricing and availability', 'Trip and itinerary pages', 'Reviews and trust badges', 'Abandoned enquiry follow-up'], alt: 'Traveller journey from destination research to trip pages, quotes and bookings' },
    { nav: 'Trust', eyebrow: 'Trust & operations', heading: 'Built for Travel Businesses', para: 'We make protection details clear and connect your booking systems so the experience feels seamless.', bullets: ['ATOL and ABTA details visible', 'Booking engine integration', 'Multi-currency checkout', 'Late deals and offers feeds'], alt: 'Travel website checklist with ATOL and ABTA details, live pricing and multi-currency checkout' },
  ],
  cards: [
    ['lucide:search', 'Destination SEO', 'Rank for where travellers go.'], ['simple-icons:googleads', 'Google Ads', 'Capture booking intent.'],
    ['simple-icons:pinterest', 'Pinterest', 'Inspire trips early.'], ['simple-icons:instagram', 'Instagram', 'Destination content.'],
    ['simple-icons:youtube', 'Video', 'Guides and experiences.'], ['lucide:mail', 'Email', 'Offers and repeat trips.'],
    ['lucide:monitor', 'Booking Websites', 'Fast, trusted booking.'], ['lucide:chart-column-increasing', 'Reporting', 'Bookings and ROAS.'],
  ],
  reviews: [['Hannah W', 'Marketing Manager, Tour Operator', 'Our destination guides now rank on page one and drive most of our bookings.'], ['Ravi S', 'Founder, Travel Agency', 'Google Ads ROAS went from 3x to almost 7x in one season.'], ['Emily J', 'Director, Holiday Company', 'Pinterest became a real booking channel for us.']],
  faqs: [
    ['How can a travel company get more bookings?', 'Combine destination SEO and inspiring content to reach people early, Google Ads to capture booking intent, and a trusted, easy booking experience to convert them.'],
    ['When should travel campaigns start?', 'Usually several months before peak booking windows. We plan around January peaks, summer and late deals.'],
    ['Do you work with small travel agents?', 'Yes. We work with independent agents, specialist tour operators and larger holiday brands.'],
    ['Can you show ATOL and ABTA details correctly?', 'Yes. We make protection information clear across your site and campaigns.'],
    ['Do you build booking websites?', 'Yes. We build travel websites and integrate booking engines and live pricing.'],
    ['Do you need a contract?', 'No. We work on rolling monthly terms.'],
  ],
  related: ['search-engine-optimization', 'google-ads', 'pinterest-marketing', 'instagram-marketing', 'content-marketing', 'web-application-development'],
});
