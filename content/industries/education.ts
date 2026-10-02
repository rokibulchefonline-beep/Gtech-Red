import { industry } from './build';

// Entities: schools, colleges, universities, training providers, student recruitment, course pages,
// open days, prospectus, UCAS/clearing, parents, enquiry-to-enrolment, CRM, accessibility (WCAG).
export default industry({
  slug: 'education',
  name: 'Education',
  kw: 'Education Marketing',
  metaTitle: 'Education Marketing Agency UK | Student Recruitment | GTech Digital',
  metaDescription: 'UK education marketing agency helping schools, colleges, universities and training providers recruit students with SEO, paid ads, social media and accessible websites.',
  hero: {
    title: 'Education Marketing That',
    highlight: 'Fills Courses',
    lead: 'GTech Digital provides education marketing services for UK schools, colleges, universities and training providers, using course SEO, Google Ads, TikTok and Instagram campaigns and accessible websites to turn enquiries into enrolments.',
    points: ['Free recruitment audit', 'Student and parent journeys', 'Enquiries tracked to enrolment'],
  },
  what: {
    heading: 'Recruiting Students in a Digital World',
    para: 'Students research courses on Google, TikTok and Instagram, while parents compare options carefully. Winning both means being visible where they search and making it easy to enquire, visit and apply.',
    bullets: ['Course search visibility and local SEO', 'Social campaigns that speak to students', 'Accessible websites and enquiry journeys'],
  },
  impact: { heading: 'More Enquiries, More Enrolments', stats: [['+58%', 'Average growth in open day bookings'], ['£46', 'Average cost per enquiry'], ['29%', 'Average enquiry to enrolment'], ['98%', 'Average site accessibility score']] },
  media: [
    { nav: 'Enquiries', topic: 'Student recruitment and course enquiries', heading: 'A Steady Flow of Course Enquiries', para: 'We plan campaigns around your intake calendar so enquiries peak when you need them most.', bullets: ['Course and subject SEO', 'Open day and event campaigns', 'Clearing and late-intake ads', 'Enrolment tracking'], alt: 'Course enquiries growing with enrolments, cost per enquiry and open day bookings' },
    { nav: 'Channels', topic: 'Channels that reach students and parents', heading: 'Reach Students Where They Scroll', para: 'Students live on TikTok and Instagram, parents on Google and Facebook. We reach both with the right message.', bullets: ['TikTok and Instagram for students', 'Google and Facebook for parents', 'Student-led content', 'Email nurture to enrolment'], alt: 'How students find you: course search, Google Ads, TikTok, Instagram, email and local search' },
    { nav: 'Journey', topic: 'Student journey from open day to enrolment', heading: 'From First Visit to Enrolment', para: 'We remove friction from course pages, prospectus requests and applications so more interest turns into places filled.', bullets: ['Course finder and filters', 'Prospectus and open day forms', 'Fast follow-up and CRM', 'Application tracking'], alt: 'Student journey from course page visits to enquiries, open days and enrolments' },
    { nav: 'Accessibility', topic: 'Accessible and safeguarding-aware websites', heading: 'Websites Built for Every Learner', para: 'Education websites must be accessible and trustworthy. We design to WCAG 2.2 AA and handle content with care.', bullets: ['WCAG 2.2 AA accessibility', 'Safeguarding-aware content', 'Easy editing for staff', 'Secure forms and GDPR'], alt: 'Education website checklist with accessibility, course finder, CRM and safeguarding' },
  ],
  cards: [
    ['lucide:search', 'Course SEO', 'Rank for subjects and courses.'], ['simple-icons:googleads', 'Google Ads', 'Open day and clearing campaigns.'],
    ['simple-icons:tiktok', 'TikTok & Instagram', 'Content students watch.'], ['simple-icons:facebook', 'Parent Campaigns', 'Facebook and search for parents.'],
    ['lucide:monitor', 'Education Websites', 'Accessible and easy to edit.'], ['lucide:database', 'CRM & Enquiries', 'Every lead followed up.'],
    ['lucide:smartphone', 'Learning Apps', 'Portals and student apps.'], ['lucide:chart-column-increasing', 'Reporting', 'Enquiries to enrolments.'],
  ],
  reviews: [['Karen S', 'Director, Training Provider', 'Course enquiries are up 60% and our cost per enrolment has dropped.'], ['Mark P', 'Marketing Lead, College', 'Our open days were fully booked for the first time in years.'], ['Laura F', 'Content Lead, University', 'The new site is accessible, fast and easy for 40 editors to update.']],
  faqs: [
    ['How do you recruit more students online?', 'By combining course SEO, targeted ads around key intake dates, social content students engage with and fast, simple enquiry journeys tracked through to enrolment.'],
    ['Do you work with schools and universities?', 'Yes. We work with independent schools, colleges, universities and private training providers.'],
    ['Can you target parents and students separately?', 'Yes. We create separate messages and channels for students and parents, as each decides differently.'],
    ['Is our website accessible?', 'We audit and build to WCAG 2.2 AA, which is required for many public sector education bodies.'],
    ['Do you help with clearing?', 'Yes. We run fast, flexible clearing campaigns with landing pages and call tracking.'],
    ['Do you need a contract?', 'No. We work on rolling monthly terms.'],
  ],
  related: ['search-engine-optimization', 'google-ads', 'tiktok-marketing', 'instagram-marketing', 'website-design', 'cms-development'],
});
