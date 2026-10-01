import { industry } from './build';

// Entities: restaurants, bars, pubs, hotels, B&Bs, venues, direct bookings, OTA commission
// (Booking.com, Expedia), Google Hotel Ads, Google Business Profile, TripAdvisor, menus, events.
export default industry({
  slug: 'hospitality-hotels',
  name: 'Hospitality',
  kw: 'Hospitality Marketing',
  metaTitle: 'Hospitality & Hotel Marketing Agency UK | Direct Bookings | GTech Digital',
  metaDescription: 'UK hospitality marketing agency for hotels, restaurants and venues: local SEO, Google Hotel Ads, social media and booking websites that grow direct bookings.',
  hero: {
    title: 'Hospitality Marketing That',
    highlight: 'Fills Tables',
    lead: 'We help hotels, restaurants and venues win more direct bookings, cut commission and keep guests coming back.',
    points: ['Free booking audit', 'More direct bookings', 'Less OTA commission'],
  },
  what: {
    heading: 'Winning Guests Before They Book Elsewhere',
    para: 'Guests discover venues on Google Maps, Instagram and TikTok, then often book through apps that take a large commission. Strong local visibility and a great direct booking experience keep more of that revenue with you.',
    bullets: ['Top of Google Maps for your area and cuisine', 'Social content that makes people want to visit', 'Direct booking websites that beat the OTAs'],
  },
  impact: { heading: 'More Bookings, Less Commission', stats: [['+64%', 'Average growth in direct bookings'], ['£22k', 'Average commission saved a year'], ['8.2x', 'Average hotel ads ROAS'], ['4.8', 'Average Google rating']] },
  media: [
    { nav: 'Bookings', topic: 'Direct bookings', heading: 'Book Direct and Keep the Margin', para: 'Every booking moved from an OTA to your own website saves commission. We make booking direct the easy choice.', bullets: ['Direct booking campaigns', 'Google Hotel and Maps ads', 'Best-rate messaging', 'Revenue tracking'], alt: 'Direct bookings revenue growing with OTA commission saved and Google rating' },
    { nav: 'Channels', topic: 'Channels', heading: 'Be Seen Where Guests Decide', para: 'From Google Maps to Instagram Reels, we put your venue in front of people planning a meal, stay or event.', bullets: ['Google Business Profile', 'Instagram and TikTok content', 'Local SEO for cuisine and area', 'Email for repeat guests'], alt: 'How guests find you: Google Maps, Instagram, hotel ads, TikTok, SEO and email' },
    { nav: 'Website', topic: 'Booking journey', heading: 'A Website That Takes Bookings', para: 'Beautiful photos, clear menus and room details and a fast booking engine turn browsers into guests.', bullets: ['Booking engine integration', 'Menus, rooms and events pages', 'Mobile-first design', 'Gift vouchers online'], alt: 'Guest booking journey from website visits to direct bookings with booking rate' },
    { nav: 'Reputation', topic: 'Reviews & seasons', heading: 'Great Reviews, Every Season', para: 'We manage reviews across Google and TripAdvisor and plan campaigns for every busy period.', bullets: ['Review replies within 24 hours', 'Seasonal campaign calendar', 'Events and Christmas promotions', 'Loyalty and email offers'], alt: 'Hospitality checklist with booking engine, Google listings, review replies and seasonal calendar' },
  ],
  cards: [
    ['lucide:map-pin', 'Local SEO', 'Top of Maps for your area.'], ['simple-icons:googleads', 'Hotel & Search Ads', 'More direct bookings.'],
    ['simple-icons:instagram', 'Instagram', 'Food, rooms and atmosphere.'], ['simple-icons:tiktok', 'TikTok', 'Venue videos that travel.'],
    ['lucide:star', 'Reviews', 'Google and TripAdvisor.'], ['lucide:monitor', 'Booking Websites', 'Fast, beautiful and direct.'],
    ['lucide:mail', 'Email & Loyalty', 'Bring guests back.'], ['lucide:chart-column-increasing', 'Reporting', 'Bookings and revenue.'],
  ],
  reviews: [['Sam T', 'Owner, Restaurant', 'Weekend bookings from Instagram and Google have doubled.'], ['Claire M', 'GM, Boutique Hotel', 'Direct bookings are up 60% and we pay far less commission.'], ['Amara O', 'Owner, Bar and Kitchen', 'Guests recognise our brand and we are always top of Maps nearby.']],
  faqs: [
    ['How can a hotel get more direct bookings?', 'Run Google Hotel Ads, offer a clear best-rate guarantee, make your booking engine fast on mobile, and use email and social media to stay in touch with past guests.'],
    ['How do restaurants get more bookings online?', 'Keep your Google Business Profile complete with menus and photos, post regularly on Instagram and TikTok, collect reviews and make online booking easy.'],
    ['Do you work with independent venues?', 'Yes. We work with independent restaurants, pubs, cafés, hotels and event venues as well as groups.'],
    ['Can you manage our reviews?', 'Yes. We monitor and respond to reviews on Google, TripAdvisor and booking sites.'],
    ['Can you integrate our booking system?', 'Yes. We work with most table and hotel booking engines and track bookings in GA4.'],
    ['Do you need a contract?', 'No. We work on rolling monthly terms.'],
  ],
  related: ['local-seo', 'instagram-marketing', 'google-ads', 'reputation-management', 'website-design', 'tiktok-marketing'],
});
