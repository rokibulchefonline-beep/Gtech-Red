<?php
declare(strict_types=1);

// Site structure. Edit here to change menus, pages and copy.
return [
    'name' => 'Gtech Red',
    'tagline' => 'Digital marketing, web and software agency',
    'email' => 'hello@gtechred.com',
    'phone' => '+971 00 000 0000',

    'services' => [
        'digital-marketing' => [
            'title' => 'Digital Marketing',
            'intro' => 'Get found, get clicks, get customers. Data-led search and paid campaigns.',
            'items' => [
                'Search Engine Optimization' => 'Rank higher on Google with technical, on-page and content SEO.',
                'Google Ads' => 'Paid search and display campaigns tuned for lead cost and ROAS.',
                'Reputation Management' => 'Monitor, protect and improve your brand reviews and search results.',
                'Content Marketing' => 'Articles, guides and assets that pull qualified traffic.',
                'SEO Backlinks' => 'Quality link building that lifts domain authority.',
                'Digital Advertising' => 'Multi-channel ad campaigns with clear tracking.',
                'Paid Media' => 'Media buying and budget optimisation across platforms.',
            ],
        ],
        'social-media-marketing' => [
            'title' => 'Social Media Marketing',
            'intro' => 'Grow audience and sales on the platforms your customers use.',
            'items' => [
                'Facebook Marketing' => 'Page growth, ads and community management.',
                'Instagram Marketing' => 'Reels, stories and ads that convert followers.',
                'LinkedIn Marketing' => 'B2B lead generation and employer branding.',
                'TikTok Marketing' => 'Short-video content and paid campaigns.',
                'Pinterest Marketing' => 'Visual discovery campaigns that drive traffic.',
            ],
        ],
        'web-design-development' => [
            'title' => 'Web Design & Development',
            'intro' => 'Fast, secure, conversion-focused websites and stores.',
            'items' => [
                'WordPress Development' => 'Custom themes and plugins on WordPress.',
                'PHP Development' => 'Custom PHP applications and APIs.',
                'CMS Development' => 'Content platforms your team can edit without code.',
                'Laravel Development' => 'Scalable Laravel web apps and back offices.',
                'Website Maintenance' => 'Updates, backups, security and uptime monitoring.',
                'Ecommerce Development' => 'Online stores with payments, catalog and checkout.',
                'Website Design' => 'UX and UI design built around your goals.',
            ],
        ],
        'custom-software-development' => [
            'title' => 'Custom Software Development',
            'intro' => 'Software built around your process, not the other way round.',
            'items' => [
                'Web Application Development' => 'Portals, dashboards and internal tools.',
                'Mobile App Development' => 'iOS and Android apps.',
                'API & System Integration' => 'Connect your tools, data and third-party services.',
                'CRM & ERP Development' => 'Custom business management systems.',
                'SaaS Product Development' => 'From idea to launched subscription product.',
                'MVP Development' => 'Ship a first version fast and validate demand.',
            ],
        ],
        'branding-strategy' => [
            'title' => 'Branding & Strategy',
            'intro' => 'Clear brand, clear plan, better conversion.',
            'items' => [
                'Branding' => 'Identity, positioning and brand guidelines.',
                'Marketing Advisory' => 'Senior guidance on channels, budget and growth plan.',
                'Conversion Rate Optimization' => 'Test and improve pages to turn more visits into leads.',
            ],
        ],
    ],

    'budgets' => [
        'Under £500', '£500 - £1,000', '£1,000 - £2,500', '£2,500 - £5,000',
        '£5,000 - £10,000', '£10,000+', 'Not sure yet',
    ],

    'industries' => [
        'E-commerce', 'Education', 'B2B Marketing', 'Automotive', 'Healthcare',
        'Hospitality & Hotels', 'Travel', 'Real Estate', 'Finance',
    ],

    'nav' => [
        ['Home', '/'],
        ['About Us', '/about'],
        ['Case Studies', '/case-studies'],
        ['Blog', '/blogs'],
        ['Contact Us', '/contact'],
    ],
];
