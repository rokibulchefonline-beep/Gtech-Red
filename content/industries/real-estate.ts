import { industry } from './build';

// Entities: estate agents, letting agents, property developers, valuations, instructions, vendors,
// landlords, Rightmove, Zoopla, OnTheMarket, material information (NTSELAT), area guides, new homes.
export default industry({
  slug: 'real-estate',
  name: 'Real Estate',
  kw: 'Property Marketing',
  metaTitle: 'Property Marketing Agency UK | Estate Agents',
  metaDescription: 'UK property marketing agency for estate agents, letting agents and developers: local SEO, valuation lead generation, social media and property websites.',
  hero: {
    title: 'Property Marketing That',
    highlight: 'Wins Instructions',
    lead: 'GTech Digital provides property marketing services for UK estate agents, letting agents and developers, using local SEO, valuation ads, area guides and portal integrations to generate valuation leads and instructions.',
    points: ['Free branch audit', 'Valuation lead generation', 'Leads tracked by branch'],
  },
  what: {
    heading: 'Winning Vendors and Landlords Online',
    para: 'Property portals bring buyers, but vendors and landlords choose agents based on local reputation, reviews and visibility. Agents who own their local search results win more instructions.',
    bullets: ['Local SEO and Maps for every branch', 'Valuation campaigns that reach homeowners', 'Websites with instant valuations and area guides'],
  },
  impact: { heading: 'More Valuations, More Instructions', stats: [['1,860', 'Average valuation requests a year'], ['17%', 'Average valuation to instruction'], ['£24', 'Average cost per valuation lead'], ['4.9', 'Average Google rating']] },
  media: [
    { nav: 'Valuations', topic: 'Valuation leads and new instructions', heading: 'A Pipeline of Valuation Requests', para: 'We target homeowners in your patch who are thinking of selling or letting, and track every lead to instruction.', bullets: ['Instant valuation campaigns', 'Landlord lead generation', 'Lead tracking by branch', 'Follow-up within minutes'], alt: 'Valuation requests growing with new instructions, viewings and cost per lead' },
    { nav: 'Channels', topic: 'Local visibility for every branch', heading: 'The Agent Every Local Owner Knows', para: 'We put each branch at the top of local search and keep your brand in front of homeowners on social.', bullets: ['Google Maps for every branch', 'Area guide SEO', 'Meta and Google valuation ads', 'Video property tours'], alt: 'How homeowners find you: Google Maps, area SEO, Meta ads, Google Ads, reviews and video tours' },
    { nav: 'Website', topic: 'Vendor journey from area guide to valuation', heading: 'From Area Guide to Signed Instruction', para: 'Useful area guides, instant valuations and fast follow-up turn curious owners into booked appointments.', bullets: ['Instant valuation tool', 'Area and street guides', 'Branch and team pages', 'Booked valuation tracking'], alt: 'Vendor journey from area guide visits to instant valuations, valuations booked and instructions' },
    { nav: 'Property tech', topic: 'Portal feeds, CRM and property technology', heading: 'Portals and Systems in Sync', para: 'We connect your CRM and portal feeds and make sure listings show the information buyers and regulators expect.', bullets: ['Rightmove and Zoopla feeds', 'Material information on listings', 'Landlord and tenant portals', 'New homes microsites'], alt: 'Property checklist with portal feeds, valuation tool, branch pages and material information' },
  ],
  cards: [
    ['lucide:map-pin', 'Local SEO', 'Every branch on Maps.'], ['lucide:house', 'Valuation Campaigns', 'Leads from local owners.'],
    ['simple-icons:meta', 'Meta Ads', 'Reach homeowners locally.'], ['simple-icons:googleads', 'Google Ads', 'Capture sell and let intent.'],
    ['lucide:star', 'Reviews', 'Branch reputation.'], ['simple-icons:youtube', 'Video Tours', 'Properties that stand out.'],
    ['lucide:monitor', 'Agency Websites', 'Search, valuations and guides.'], ['lucide:app-window', 'Portals', 'Landlord and tenant systems.'],
  ],
  reviews: [['Rob H', 'Director, Estate Agent', 'Each branch now ranks in its own town, with reviews to match.'], ['Ayesha R', 'Founder, Lettings Agency', 'Landlords love the portal and we win more managed properties.'], ['Paul D', 'Sales Director, Developer', 'Our new homes campaign sold the first phase off-plan.']],
  faqs: [
    ['How do estate agents get more valuation leads?', 'Rank each branch in local search and Maps, collect reviews, run targeted valuation ads to homeowners and offer an instant valuation tool with fast follow-up.'],
    ['Do you work with letting agents?', 'Yes. Our letting agent marketing helps win landlords, grow your rental listings and build landlord and tenant portals, with local SEO and ads aimed at property owners in your area.'],
    ['Can you help property developers?', 'Yes. We run new homes campaigns, microsites and video to sell developments faster, with reservation tracking and viewing bookings, so you can see which marketing reaches buyers.'],
    ['Do you integrate with property CRMs and portals?', 'Yes. We connect most agency CRMs and portal feeds such as Rightmove and Zoopla, so listings stay accurate and enquiries from every source flow into one place for fast follow-up.'],
    ['How do you measure results?', 'We track valuation requests, booked valuations and instructions by branch and channel, so you can see which ads, searches and area guides lead to listings won, not just enquiries received.'],
    ['Do you need a contract?', 'No. GTech Digital runs property marketing on rolling monthly terms. Local rankings take a few months to build, so we recommend at least four, but there is no lock-in.'],
    ['How do you market estate agents locally?', 'Estate agent marketing starts with each branch ranking in Google Maps and local search, supported by area guides, reviews and valuation ads aimed at homeowners nearby. A fast, simple instant valuation tool then turns interest into booked appointments.'],
  ],
  related: ['local-seo', 'facebook-marketing', 'google-ads', 'reputation-management', 'website-design', 'web-application-development'],
});
