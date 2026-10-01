import type { LegalDoc } from './types';
import { company } from './company';

const cookies: LegalDoc = {
  title: 'Cookie Policy',
  intro: 'What cookies are, which ones the GTech Digital website uses and how you can control them.',
  updated: '1 October 2026',
  sections: [
    { h: 'What Are Cookies?', p: ['Cookies are small text files stored on your device when you visit a website. They help the site work, remember your preferences and show how it is used.'] },
    { h: 'How We Use Cookies', p: ['Under the UK Privacy and Electronic Communications Regulations (PECR), we only set non-essential cookies with your consent. We use the following types:'], ul: ['Strictly necessary: needed for the website to work, such as security and form submission. These cannot be switched off.', 'Analytics: help us understand how visitors use the site, for example Google Analytics. Set only with your consent.', 'Marketing: used to measure and improve our advertising, for example Google Ads and Meta Pixel. Set only with your consent.'] },
    { h: 'Third-Party Cookies', p: ['Some cookies are set by third parties such as Google and Meta when their services are used on our site. These providers have their own privacy and cookie policies.'] },
    { h: 'Managing Cookies', p: ['You can change your cookie preferences at any time. You can also block or delete cookies through your browser settings, although some parts of the site may not work as intended.'], ul: ['Google Chrome: Settings, Privacy and security, Cookies', 'Safari: Settings, Privacy, Manage website data', 'Firefox: Settings, Privacy and Security, Cookies and Site Data', 'Microsoft Edge: Settings, Cookies and site permissions'] },
    { h: 'How Long Cookies Last', p: ['Session cookies are deleted when you close your browser. Persistent cookies remain for a set period, usually between 30 days and 2 years, or until you delete them.'] },
    { h: 'Changes and Contact', p: [`We may update this policy as our website changes. For questions, email ${company.email}.`] },
  ],
};

export default cookies;
