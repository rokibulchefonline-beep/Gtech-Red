import type { LegalDoc } from './types';
import { company } from './company';

const privacy: LegalDoc = {
  title: 'Privacy Policy',
  intro: 'How GTech Digital collects, uses and protects your personal data, and the rights you have under UK data protection law.',
  updated: '1 October 2026',
  sections: [
    { h: 'Who We Are', p: [`GTech Digital is a trading name of ${company.legalName}, a company registered in England and Wales (company number ${company.number}) with its registered office at ${company.address}. We are the data controller for the personal data described in this policy and are registered with the Information Commissioner’s Office (ICO) under number ${company.ico}.`] },
    { h: 'The Data We Collect', p: ['We only collect the personal data we need. This may include:'], ul: ['Contact details such as your name, business name, email address and phone number', 'Details you share about your project, services needed and budget', 'Newsletter sign-ups (your email address)', 'Technical data such as IP address, browser type and pages visited, collected through cookies and analytics', 'Records of our communications with you'] },
    { h: 'How We Use Your Data', ul: ['To respond to enquiries and send proposals you request', 'To deliver services under a contract with you', 'To send our newsletter, where you have subscribed', 'To improve our website and understand how it is used', 'To meet our legal, accounting and regulatory obligations'] },
    { h: 'Our Lawful Bases', p: ['Under the UK GDPR we rely on the following lawful bases:'], ul: ['Contract: to take steps at your request before entering a contract, and to perform it', 'Legitimate interests: to respond to business enquiries, run and improve our business and keep our website secure', 'Consent: for our newsletter and non-essential cookies, which you can withdraw at any time', 'Legal obligation: to keep financial and tax records'] },
    { h: 'Sharing Your Data', p: ['We never sell your personal data. We share it only with trusted providers who help us run our business, such as hosting, email, CRM, analytics and accounting providers, under contracts that require them to protect it. We may also disclose data where required by law.'] },
    { h: 'International Transfers', p: ['Some of our providers may process data outside the UK. Where they do, we make sure appropriate safeguards are in place, such as UK adequacy regulations or the International Data Transfer Agreement.'] },
    { h: 'How Long We Keep Data', ul: ['Enquiries that do not become clients: up to 24 months', 'Client records: for the length of our contract plus 6 years', 'Newsletter subscribers: until you unsubscribe', 'Analytics data: up to 14 months'] },
    { h: 'Your Rights', p: ['Under UK data protection law you have the right to:'], ul: ['Access the personal data we hold about you', 'Ask us to correct inaccurate data', 'Ask us to delete your data', 'Object to or restrict how we use your data', 'Ask for your data to be transferred to you or another organisation', 'Withdraw consent at any time, where we rely on consent'], after: [`To exercise any of these rights, email ${company.email}. We will respond within one month.`] },
    { h: 'Cookies', p: ['We use cookies to run our website and, with your consent, to measure how it is used. See our Cookie Policy for details and how to manage your preferences.'] },
    { h: 'Security', p: ['We use appropriate technical and organisational measures to protect your data, including encryption in transit, access controls and regular reviews of our systems and suppliers.'] },
    { h: 'Complaints', p: [`If you have concerns about how we use your data, please contact us first at ${company.email}. You also have the right to complain to the Information Commissioner’s Office (ico.org.uk, 0303 123 1113).`] },
    { h: 'Changes to This Policy', p: ['We may update this policy from time to time. The latest version will always be on this page, with the date it was last updated.'] },
  ],
};

export default privacy;
