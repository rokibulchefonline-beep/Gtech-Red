import type { LegalDoc } from './types';
import { company } from './company';

const terms: LegalDoc = {
  title: 'Terms and Conditions',
  intro: 'The terms that apply when you use the GTech Digital website and when you engage us for services.',
  updated: '1 October 2026',
  sections: [
    { h: 'About These Terms', p: [`These terms apply to your use of this website and to services provided by GTech Digital, a trading name of ${company.legalName}, registered in England and Wales (company number ${company.number}), registered office ${company.address}. By using this website you agree to these terms.`] },
    { h: 'Our Services', p: ['The scope, deliverables, fees and timescales for each engagement are set out in a proposal or statement of work. If anything in a signed proposal conflicts with these terms, the proposal takes priority.'] },
    { h: 'Quotes and Proposals', p: ['Quotes are valid for 30 days unless stated otherwise. Work begins once you accept the proposal in writing and any agreed deposit has been paid.'] },
    { h: 'Fees and Payment', ul: ['Ongoing services are billed monthly in advance', 'Project fees are billed in stages as set out in the proposal', 'Invoices are payable within 14 days unless agreed otherwise', 'Advertising spend is paid directly to the platform or invoiced separately', 'We may pause work on overdue accounts and charge statutory late-payment interest'] },
    { h: 'Your Responsibilities', ul: ['Provide accurate information, content and approvals on time', 'Give us the access we need to accounts and systems', 'Make sure content you supply does not infringe anyone else’s rights', 'Review work and give feedback within the agreed timescales'] },
    { h: 'Term and Cancellation', p: ['Ongoing services run on rolling monthly terms unless your proposal says otherwise. Either party may cancel with 30 days’ written notice. Project work may be cancelled in writing; you will pay for work completed up to the cancellation date.'] },
    { h: 'Intellectual Property', p: ['Once paid in full, you own the final deliverables created specifically for you, such as designs, content and custom code. We keep ownership of our pre-existing tools, know-how and third-party components, which are licensed to you for use with the deliverables. We may show completed work in our portfolio unless you ask us not to.'] },
    { h: 'Accounts and Data', p: ['Advertising, analytics and other accounts created for your business belong to you. We process personal data on your behalf in line with UK data protection law and, where required, a data processing agreement.'] },
    { h: 'Results', p: ['We use our expertise and best efforts, but rankings, traffic, leads and sales depend on factors outside our control, such as search engine and platform changes and competitor activity. We do not guarantee specific results unless agreed in writing.'] },
    { h: 'Liability', p: ['Nothing in these terms limits liability that cannot be limited by law. Otherwise, our total liability under any engagement is limited to the fees you paid us in the 12 months before the claim, and we are not liable for indirect or consequential loss, including loss of profit.'] },
    { h: 'Use of This Website', p: ['Website content is for general information and does not constitute professional advice. You may not copy or reuse our content without permission, or use the website in a way that could damage it or affect other users.'] },
    { h: 'Governing Law', p: ['These terms are governed by the laws of England and Wales, and the courts of England and Wales have exclusive jurisdiction.'] },
    { h: 'Contact', p: [`Questions about these terms can be sent to ${company.email} or through our contact page.`] },
  ],
};

export default terms;
