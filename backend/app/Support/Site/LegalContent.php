<?php

namespace App\Support\Site;

use App\Models\Page;
use App\Models\Setting;

/**
 * Terms and Privacy Policy wording. Company details come from Site settings (Company and Contact), written into the
 * text when the page is shown: {company}, {email}, {phone}, {address}, {ico}. Details not filled in are left out.
 */
class LegalContent
{
    /** Placeholder values from the first set-up ("[Company legal name] Ltd") count as not filled in. */
    private static function real(?string $v): string
    {
        $v = trim((string) $v);
        return $v === '' || str_contains($v, '[') ? '' : $v;
    }

    public static function fill(string $text): string
    {
        $co = Setting::group('company');
        $c = Contact::get();
        $legal = self::real($co['legalName'] ?? '') ?: 'Global Tech Digital';
        $number = self::real($co['number'] ?? '');
        $office = self::real($co['address'] ?? '') ?: implode(', ', $c['address']);
        $company = "GTech Digital is the trading name of $legal".($number ? ", a company registered in England and Wales (company number $number)" : '').($office ? ", of $office" : '');
        $ico = self::real($co['ico'] ?? '');
        return strtr($text, [
            '{company}' => $company, '{email}' => $c['email'] ?: 'info@gtechdigital.co.uk',
            '{phone}' => $c['phones'][0]['label'] ?? '', '{address}' => implode(', ', $c['address']),
            '{ico}' => $ico ? " We are registered with the Information Commissioner's Office (registration number $ico)." : '',
        ]);
    }

    public static function apply(): void
    {
        // The first set-up filled Site settings > Company with "[...]" placeholders; clear them so the fields show empty.
        $co = (array) (Setting::query()->find('company')?->value ?? []);
        if ($co && array_filter($co, fn ($v) => str_contains((string) $v, '['))) Setting::put('company', array_map(fn ($v) => str_contains((string) $v, '[') ? '' : $v, $co));
        $date = now()->format('j F Y');
        $pages = [
            'legal~terms' => ['lead' => 'The terms that apply when you use the GTech Digital website and when you engage us for digital marketing, web design or software services.', 'sections' => [
                ['About These Terms', ['{company}. These terms apply to your use of this website and to the services we provide. By using the website or accepting a proposal, you agree to them.']],
                ['Our Services', ['Each engagement is set out in a proposal or statement of work, which describes the scope, deliverables, fees and timescales. If a signed proposal conflicts with these terms, the proposal takes priority.']],
                ['Quotes and Proposals', ['Quotes are valid for 30 days unless stated otherwise. Work starts once you accept the proposal in writing and any agreed deposit has been paid.']],
                ['Fees and Payment', ['Fees are set out in your proposal. Unless agreed otherwise:'], ['Ongoing services are billed monthly in advance', 'Project fees are billed in stages, as set out in the proposal', 'Invoices are payable within 14 days', 'Advertising spend is paid directly to the platform or invoiced separately', 'We may pause work on overdue accounts and charge statutory late-payment interest']],
                ['Your Responsibilities', ['To deliver on time, we need you to:'], ['Provide accurate information, content and approvals on time', 'Give us access to the accounts and systems we need', 'Make sure content you supply does not infringe anyone else’s rights', 'Review work and give feedback within the agreed timescales']],
                ['Term and Cancellation', ['Ongoing services run month to month after any initial period in your proposal. Either party may cancel with 30 days’ written notice. Project work can be cancelled in writing at any time; you pay for work completed up to that date.']],
                ['Intellectual Property', ['Once paid in full, you own the final deliverables made for you, such as designs, content and custom code. We keep ownership of our existing tools, know-how and third-party components, which are licensed to you for use with the deliverables. We may show completed work in our portfolio unless you ask us not to.']],
                ['Accounts and Data', ['Advertising, analytics and other accounts created for your business belong to you. Where we handle personal data on your behalf, we do so under UK data protection law and, where needed, a data processing agreement.']],
                ['Results', ['We use our expertise and best efforts, but rankings, AI visibility, traffic, leads and sales depend on things outside our control, such as search engine and platform changes and competitor activity. We do not guarantee specific results unless agreed in writing.']],
                ['Liability', ['Nothing in these terms limits liability that cannot be limited by law. Otherwise, our total liability for any engagement is limited to the fees you paid us in the 12 months before the claim, and we are not liable for indirect or consequential loss, including loss of profit.']],
                ['Use of This Website', ['Website content is general information, not professional advice. Do not copy or reuse our content without permission, or use the website in a way that could damage it or affect other users.']],
                ['Changes to These Terms', ['We may update these terms. The version on this page applies from the date shown at the top. Changes do not affect proposals already signed.']],
                ['Governing Law', ['These terms are governed by the laws of England and Wales, and the courts of England and Wales have exclusive jurisdiction.']],
                ['Contact', ['Questions about these terms: email {email}, call {phone}, or write to us at {address}.']],
            ]],
            'legal~privacy-policy' => ['lead' => 'How GTech Digital collects, uses and protects your personal data, how long we keep it, and the rights you have under UK data protection law.', 'sections' => [
                ['Who We Are', ['{company}. We are the data controller for the personal data described in this policy.{ico}', 'Contact us about your data at {email} or {phone}.']],
                ['The Data We Collect', ['We only collect the personal data we need:'], ['Contact details you give us, such as your name, business name, email address and phone number', 'Details about your project, the service you need and your budget', 'How you found us: the page you used, the website or advert that sent you, and campaign codes', 'Your email address if you join our newsletter', 'Technical data such as IP address, browser type and pages visited, through cookies and analytics where you allow them', 'Records of our calls, emails and meetings with you']],
                ['How We Use Your Data', [], ['To reply to enquiries and send the audits and proposals you ask for', 'To deliver services under a contract with you', 'To send our newsletter, if you subscribed', 'To keep our forms free of spam and abuse', 'To improve our website and understand how it is used', 'To meet our legal, accounting and tax obligations']],
                ['Our Lawful Bases', ['Under the UK GDPR we rely on:'], ['Contract: to take steps you ask for before a contract, and to perform it', 'Legitimate interests: to respond to business enquiries, run and improve our business and keep our website secure', 'Consent: for our newsletter and non-essential cookies, which you can withdraw at any time', 'Legal obligation: to keep financial and tax records']],
                ['Website Forms and Spam Protection', ['Our enquiry forms may use Google reCAPTCHA to check that a real person is sending the form. It processes technical data such as your IP address and browser details for this purpose only. We record the privacy notice shown when you submit a form.']],
                ['Sharing Your Data', ['We never sell your personal data. We share it only with providers who help us run our business, such as hosting, email, CRM, analytics, spam protection and accounting, under contracts that require them to protect it. We may also disclose data where the law requires.']],
                ['International Transfers', ['Some providers may process data outside the UK. Where they do, we use appropriate safeguards, such as UK adequacy regulations or the International Data Transfer Agreement.']],
                ['How Long We Keep Data', [], ['Enquiries that do not become clients: up to 24 months, then deleted', 'Client records: for the length of our contract plus 6 years', 'Newsletter subscribers: until you unsubscribe', 'Analytics data: up to 14 months']],
                ['Your Rights', ['Under UK data protection law you can:'], ['Ask for a copy of the personal data we hold about you', 'Ask us to correct inaccurate data', 'Ask us to delete your data', 'Object to or restrict how we use your data', 'Ask for your data to be transferred to you or another organisation', 'Withdraw consent at any time, where we rely on consent'], ['To make a request, email {email}. We reply within one month and do not charge a fee.']],
                ['Cookies', ['We use cookies to run our website and, with your consent, to measure how it is used and to show relevant adverts. See our Cookie Policy for details and to change your choices.']],
                ['Security', ['We protect your data with technical and organisational measures, including encryption in transit, access controls, two-factor sign-in for our team and regular reviews of our systems and suppliers.']],
                ['Complaints', ['If you are unhappy with how we use your data, please contact us first at {email}. You can also complain to the Information Commissioner’s Office (ico.org.uk, 0303 123 1113).']],
                ['Changes to This Policy', ['We may update this policy. The latest version is always on this page, with the date it was last updated at the top.']],
            ]],
        ];
        foreach ($pages as $key => $d) {
            $p = Page::query()->find($key);
            if (! $p) continue;
            $sections = array_map(fn ($s) => array_filter(['type' => 'legal', 'heading' => $s[0], 'paras' => $s[1], 'bullets' => $s[2] ?? null, 'after' => $s[3] ?? null], fn ($v) => $v !== null), $d['sections']);
            $p->forceFill(['hero' => array_merge((array) $p->hero, ['lead' => $d['lead']]), 'sections' => $sections, 'data' => array_merge((array) $p->data, ['updated' => $date])])->save();
        }
        PageCache::flush();
    }
}
