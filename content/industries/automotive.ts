import { industry } from './build';

// Entities: car dealerships, used car dealers, garages, MOT and servicing, stock feeds, finance
// (FCA), test drives, part exchange, Google Vehicle Ads, Meta automotive inventory ads, AutoTrader.
export default industry({
  slug: 'automotive',
  name: 'Automotive',
  kw: 'Automotive Marketing',
  metaTitle: 'Automotive Marketing Agency UK | Car Dealer & Garage Marketing | GTech Digital',
  metaDescription: 'UK automotive marketing agency for car dealers and garages: local SEO, Google and Meta vehicle ads, stock-led websites and FCA-compliant finance promotions.',
  hero: {
    title: 'Automotive Marketing That',
    highlight: 'Sells Cars',
    lead: 'GTech Digital provides automotive marketing services for UK car dealers and garages, using Google Vehicle Ads, local SEO, Meta inventory ads and dealer websites to drive vehicle enquiries, test drives and service bookings.',
    points: ['Free dealer audit', 'Stock-led campaigns', 'Calls and test drives tracked'],
  },
  what: {
    heading: 'Selling Cars and Services Online',
    para: 'Most car buyers research online for weeks before visiting a forecourt. Dealers and garages that show live stock, strong reviews and easy booking win the visit, the test drive and the sale.',
    bullets: ['Local SEO and Google Maps for showrooms and garages', 'Vehicle ads driven by your live stock', 'Websites with finance, part exchange and booking'],
  },
  impact: { heading: 'More Enquiries From Every Vehicle', stats: [['2,960', 'Average vehicle enquiries a year'], ['45%', 'Average test drive to sale'], ['£18', 'Average cost per lead'], ['+36%', 'Average service bookings growth']] },
  media: [
    { nav: 'Enquiries', topic: 'Vehicle enquiries and test drive bookings', heading: 'More Calls, Enquiries and Test Drives', para: 'We track every call, form and chat back to the campaign and vehicle that drove it.', bullets: ['Call tracking by channel', 'Test drive booking forms', 'Finance and part-exchange leads', 'Lead reporting by model'], alt: 'Vehicle enquiries growing with test drives booked, service bookings and cost per lead' },
    { nav: 'Channels', topic: 'Stock ads and local search channels', heading: 'Your Stock in Front of Local Buyers', para: 'Google Vehicle Ads and Meta inventory ads show your real stock to people searching nearby.', bullets: ['Google Vehicle and search ads', 'Meta automotive inventory ads', 'YouTube walkaround videos', 'Google Maps and reviews'], alt: 'How drivers find you: Google Maps, Google Ads, Meta ads, SEO, YouTube and reviews' },
    { nav: 'Website', topic: 'Dealer website and online showroom', heading: 'A Showroom That Never Closes', para: 'Fast stock pages with clear prices, finance examples and easy booking turn online interest into forecourt visits.', bullets: ['Live stock search and filters', 'Finance calculator', 'Part-exchange valuation', 'Online service booking'], alt: 'Dealer funnel from stock page views to enquiries, test drives and vehicles sold' },
    { nav: 'Compliance', topic: 'Stock feeds and FCA-compliant finance ads', heading: 'Accurate Stock and Compliant Finance', para: 'We keep your stock feeds accurate everywhere and make sure finance promotions follow FCA rules.', bullets: ['Stock feed management', 'FCA-compliant finance ads', 'Representative APR examples', 'Review management'], alt: 'Dealer checklist with live stock feed, finance calculator, FCA-compliant ads and service booking' },
  ],
  cards: [
    ['lucide:map-pin', 'Local SEO', 'Top of Maps for your area.'], ['simple-icons:googleads', 'Vehicle Ads', 'Google stock-led campaigns.'],
    ['simple-icons:meta', 'Inventory Ads', 'Meta automotive ads.'], ['simple-icons:youtube', 'Video', 'Walkarounds and reviews.'],
    ['lucide:monitor', 'Dealer Websites', 'Stock, finance and booking.'], ['lucide:wrench', 'Service Marketing', 'Fill the workshop.'],
    ['lucide:star', 'Reviews', 'More five-star ratings.'], ['lucide:chart-column-increasing', 'Reporting', 'Leads and sales by channel.'],
  ],
  reviews: [['Gary F', 'Owner, Used Car Dealer', 'Enquiries doubled and our cost per sale dropped by a third.'], ['Sandeep B', 'Service Manager, Garage', 'Online MOT and service bookings now fill most of our workshop.'], ['Neil R', 'Dealer Principal', 'Every call is tracked, so we know exactly which ads sell cars.']],
  faqs: [
    ['How can a car dealer get more leads online?', 'Show live stock in Google and Meta vehicle ads, rank in local search and Maps, collect reviews and make finance, part exchange and test drive booking easy on your website.'],
    ['Do you work with independent garages?', 'Yes. We help garages grow MOT, servicing and repair bookings with local SEO, Google Ads and online booking.'],
    ['Are finance ads regulated?', 'Yes. Finance promotions must follow FCA rules, including representative examples. We build campaigns to stay compliant.'],
    ['Can you use our stock feed?', 'Yes. We connect your stock feed to your website and ad platforms so ads always show what is available.'],
    ['How do you track sales?', 'We track calls, forms and test drives by channel, and can match them to sales in your DMS or CRM.'],
    ['Do you need a contract?', 'No. We work on rolling monthly terms.'],
  ],
  related: ['local-seo', 'google-ads', 'facebook-marketing', 'website-design', 'reputation-management', 'web-application-development'],
});
