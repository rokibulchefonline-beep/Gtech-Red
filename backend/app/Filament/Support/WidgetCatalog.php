<?php

namespace App\Filament\Support;

/**
 * What each page builder widget is for, which fields it has and how to use it. Shown in the admin under
 * Website content > Page builder widgets. Keep this in step with PageBlocks::blocks().
 */
class WidgetCatalog
{
    /** @return array<string, array{name:string, group:string, use:string, best:string, fields:string[], tips:string[], example:string}> */
    public static function all(): array
    {
        return [
            'text' => [
                'name' => 'Text', 'group' => 'Writing',
                'use' => 'Headline with paragraphs and an optional bullet list. The simplest way to explain something.',
                'best' => 'Explaining a service, what is included, how it works.',
                'fields' => ['Heading', 'Paragraphs (rich text)', 'Bullet points (optional)', 'Label for the "On this page" bar'],
                'tips' => ['Put [[words in double brackets]] to colour them red.', 'Keep paragraphs short: two to four lines on phones.'],
                'example' => 'Heading "What is Local SEO?", two paragraphs, three bullets: Google Business Profile, reviews, citations.',
            ],
            'media' => [
                'name' => 'Image and text', 'group' => 'Writing',
                'use' => 'Text beside a picture. Switch the image to the left, and choose a white or light grey background.',
                'best' => 'Showing a result, a dashboard, a team photo or a product next to what it means.',
                'fields' => ['Heading', 'Paragraphs', 'Bullet points', 'Image', 'Image alt text (required)', 'Image on the left', 'Background colour'],
                'tips' => ['Always write the alt text: it describes the picture for search engines and screen readers.', 'Alternate left and right images down a long page.'],
                'example' => 'Image of a rankings chart, heading "Rank higher in your town", the text beside it.',
            ],
            'cards' => [
                'name' => 'Cards', 'group' => 'Layout',
                'use' => 'A grid of small cards, each with an icon, title and text. Five or more cards switch to a dark background.',
                'best' => 'Listing what you offer, reasons to choose you, the parts of a package.',
                'fields' => ['Heading', 'Intro', 'Cards: icon, title, text', 'Label for the "On this page" bar'],
                'tips' => ['Two to six cards look best.', 'Keep each title to a few words.'],
                'example' => 'Four cards: Keyword research, Technical SEO, Content, Monthly reporting.',
            ],
            'features' => [
                'name' => 'Features', 'group' => 'Layout',
                'use' => 'Introduction paragraphs on the left and feature cards on the right, with a "Talk to our team" button.',
                'best' => 'Selling a service with a short story and a set of features.',
                'fields' => ['Heading', 'Intro', 'Paragraphs (left column)', 'Feature cards: icon, title, text'],
                'tips' => ['Use three or four feature cards.'],
                'example' => 'Left: why our Google Ads management works. Right: four feature cards.',
            ],
            'steps' => [
                'name' => 'Steps', 'group' => 'Layout',
                'use' => 'Numbered steps in order, for a process people go through.',
                'best' => 'How the project works, onboarding, what happens after an enquiry.',
                'fields' => ['Heading', 'Intro', 'Steps: title, text'],
                'tips' => ['Three to five steps is ideal.', 'Start each title with a verb: "Audit", "Build", "Measure".'],
                'example' => '1 Free audit, 2 Proposal, 3 Launch, 4 Monthly review.',
            ],
            'table' => [
                'name' => 'Table', 'group' => 'Data',
                'use' => 'A comparison table with column headings and rows. The first cell of each row is the row heading.',
                'best' => 'Comparing packages, or what each option includes.',
                'fields' => ['Heading', 'Intro', 'Column headings (separated by |)', 'Rows (one per line, cells separated by |)', 'Tip under the table'],
                'tips' => ['Separate cells with |, one row per line.', 'Keep to four columns on phones.'],
                'example' => 'Columns: Feature | Basic | Pro. Rows: Keyword research | Yes | Yes.',
            ],
            'metrics' => [
                'name' => 'Results in numbers', 'group' => 'Proof',
                'use' => 'Result numbers in a red band, each with a label and a short note.',
                'best' => 'Showing measurable results for a service.',
                'fields' => ['Heading', 'Intro', 'Numbers: value, label, note'],
                'tips' => ['Use real, verifiable figures and say what they measure.'],
                'example' => '+180% organic traffic in 12 months.',
            ],
            'impact' => [
                'name' => 'Impact (headline numbers)', 'group' => 'Proof',
                'use' => 'A large statement with a short text and a column of big headline numbers.',
                'best' => 'A strong result or a claim you want people to remember.',
                'fields' => ['Heading', 'Text', 'Headline numbers: value, label'],
                'tips' => ['Two to four headline numbers read best.'],
                'example' => 'Heading "Our clients grow faster", numbers: 3.4x ROAS, 2.1M reach.',
            ],
            'reviews' => [
                'name' => 'Reviews', 'group' => 'Proof',
                'use' => 'Customer quotes with the name, role and company, on a dark background.',
                'best' => 'Social proof close to a call to action.',
                'fields' => ['Heading', 'Intro', 'Reviews: name, role and company, text'],
                'tips' => ['Use real quotes with permission. Two or three reviews are enough.'],
                'example' => '"They doubled our enquiries in six months." Sarah, Managing Director.',
            ],
            'cases' => [
                'name' => 'Case studies', 'group' => 'Proof',
                'use' => 'A slider of case studies. By default it shows the ones tagged with this page\'s service.',
                'best' => 'Showing the work behind a service.',
                'fields' => ['Heading', 'Intro', 'Which case studies (this service, or the latest)'],
                'tips' => ['Hidden automatically when no published case study matches.', 'Tag case studies with the service in Case studies.'],
                'example' => 'Heading "Results from our SEO clients", the SEO case studies.',
            ],
            'industries' => [
                'name' => 'Industries', 'group' => 'Links',
                'use' => 'Cards for industries you serve, each with a short note and a link to the industry page.',
                'best' => 'Showing you understand a sector, and linking to its page.',
                'fields' => ['Heading', 'Intro', 'Industries: pick one, short note'],
                'tips' => ['Only industries that exist in Industries are listed.'],
                'example' => 'Hospitality & Hotels: "Booking-led campaigns that fill rooms."',
            ],
            'logos' => [
                'name' => 'Client logos', 'group' => 'Proof',
                'use' => 'A row of six client logos from Website content > Client logos. Nothing to fill in here.',
                'best' => 'Quick trust near the top of a page.',
                'fields' => ['None: the logos come from Client logos'],
                'tips' => ['Only logos you own the rights to, and that are switched on in Client logos.'],
                'example' => 'Shows automatically under "Trusted by growing UK brands".',
            ],
            'cta' => [
                'name' => 'Call to action banner', 'group' => 'Conversion',
                'use' => 'A full-width coloured band with a heading, a short line and one button. Red or dark.',
                'best' => 'Between two sections, or at the end of a page, to ask for the next step.',
                'fields' => ['Heading', 'Supporting line', 'Button text', 'Button link', 'Colour (red or dark)', 'Label for the "On this page" bar'],
                'tips' => ['One clear action per banner.', 'Link to #inquiry to scroll to the form, or to /contact.'],
                'example' => 'Heading "Ready for more enquiries?", button "Get a Free Audit" linking to #inquiry.',
            ],
            'faq' => [
                'name' => 'FAQ accordion', 'group' => 'Answers',
                'use' => 'Questions that open to show the answer. Works on any page, not only the FAQ at the bottom.',
                'best' => 'Answering objections next to the section they relate to.',
                'fields' => ['Heading', 'Intro', 'Questions and answers'],
                'tips' => ['Keep answers to 40–60 words.', 'Questions here are shown to people but are not added to Google\'s FAQ results. Use the FAQs tab for those.'],
                'example' => '"How long does an SEO project take?" with a 3-sentence answer.',
            ],
            'video' => [
                'name' => 'Video', 'group' => 'Media',
                'use' => 'A YouTube or Vimeo video that plays inside the page. Uses the privacy-friendly YouTube player.',
                'best' => 'Explaining a service in two minutes, or a client story.',
                'fields' => ['Heading', 'Intro', 'YouTube or Vimeo link', 'Caption'],
                'tips' => ['Only YouTube and Vimeo links are accepted.', 'Videos load when the visitor reaches them, so they do not slow the page.'],
                'example' => 'Paste the normal video link from YouTube.',
            ],
            'pricing' => [
                'name' => 'Pricing plans', 'group' => 'Conversion',
                'use' => 'Plans side by side with price, period, what is included and a button. One plan can be highlighted.',
                'best' => 'Showing packages and prices clearly.',
                'fields' => ['Heading', 'Intro', 'Plans: name, price, period, description, included items, highlight, button, link'],
                'tips' => ['Two or three plans are easiest to compare.', 'Highlight the plan you most want people to choose.'],
                'example' => 'Starter £299 per month, Growth £599 per month (highlighted), Scale on request.',
            ],
            'form' => [
                'name' => 'Enquiry form', 'group' => 'Conversion',
                'use' => 'The site\'s enquiry form, in a compact band, with this page\'s service already chosen.',
                'best' => 'Landing pages and service pages where people should enquire straight away.',
                'fields' => ['Heading', 'Intro'],
                'tips' => ['Replies go to the leads list, the same as every other form.', 'Only one enquiry form per page is needed: the page already ends with one.'],
                'example' => 'Heading "Get a free Local SEO audit", the form underneath.',
            ],
        ];
    }

    /** Groups in the order they appear in the admin. */
    public const GROUPS = ['Writing', 'Layout', 'Data', 'Proof', 'Media', 'Links', 'Conversion', 'Answers'];
}
