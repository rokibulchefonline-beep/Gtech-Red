<?php

namespace App\Filament\Admin\Pages;

use App\Models\Setting;
use App\Support\Site\Crawl;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

/** SEO > Search engines: indexing on/off, robots.txt, the robots meta tag and the sitemap. */
class SearchEngines extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationGroup = 'SEO';
    protected static ?string $navigationLabel = 'Search engines';
    protected static ?string $title = 'Search engines: robots.txt, robots meta and sitemap';
    protected static ?string $slug = 'seo/search-engines';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.admin.pages.settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasPerm('seo.view');
    }

    public static function canEdit(): bool
    {
        return (bool) auth()->user()?->hasPerm('seo.edit');
    }

    public function mount(): void
    {
        $this->form->fill(Setting::group('crawl'));
    }

    public function form(Form $form): Form
    {
        $env = strtolower((string) config('gtech.indexing.mode'));
        $envLocked = in_array($env, ['on', 'off', 'true', 'false', '1', '0'], true);

        return $form->statePath('data')->disabled(fn () => ! self::canEdit())->schema([
            Forms\Components\Tabs::make()->persistTabInQueryString()->tabs([
                Forms\Components\Tabs\Tab::make('Indexing')->icon('heroicon-o-eye')->schema([
                    Forms\Components\Placeholder::make('now')->hiddenLabel()->content(fn () => new HtmlString(Crawl::indexable(request()->getHost())
                        ? '<b style="color:#16a34a">This domain ('.e(request()->getHost()).') can be indexed by search engines.</b>'
                        : '<b style="color:#dc2626">This domain ('.e(request()->getHost()).') is hidden from search engines</b>: every page and file says "noindex".')),
                    Forms\Components\Placeholder::make('envnote')->hiddenLabel()->visible($envLocked)
                        ->content("SITE_INDEXING=$env is set in the server's .env file, so it wins over the choice below. Remove it to control indexing from here."),
                    Forms\Components\Radio::make('indexing')->label('Let search engines index the site')->required()->options([
                        'auto' => 'Only on the live domains below (recommended: test and staging copies stay hidden)',
                        'on' => 'Yes, on every domain',
                        'off' => 'No, hide the whole site from search engines (noindex everywhere)',
                    ]),
                    Forms\Components\Textarea::make('hosts')->label('Live domains')->rows(2)->helperText('Separate with commas or new lines, e.g. gtechdigital.co.uk, www.gtechdigital.co.uk'),
                    Forms\Components\Placeholder::make('tip')->hiddenLabel()->content('To get pages removed from Google, keep robots.txt allowing crawling: Google has to visit a page to see its "noindex". Search Console > Removals hides them faster.'),
                ]),
                Forms\Components\Tabs\Tab::make('robots.txt')->icon('heroicon-o-document-text')->schema([
                    Forms\Components\Textarea::make('robotsTxt')->label('robots.txt')->rows(14)->extraInputAttributes(['style' => 'font-family:monospace'])
                        ->placeholder(Crawl::defaultRobotsTxt())
                        ->helperText('Leave empty to use the default shown in grey. {sitemap} is replaced by the sitemap address.'),
                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('useDefault')->label('Fill in the default to edit it')->icon('heroicon-o-document-duplicate')->color('gray')
                            ->action(fn (Forms\Set $set) => $set('robotsTxt', str_replace(rtrim((string) config('gtech.public_url'), '/').'/sitemap.xml', '{sitemap}', Crawl::defaultRobotsTxt()))),
                        Forms\Components\Actions\Action::make('blockAi')->label('Add rules blocking AI training bots')->icon('heroicon-o-no-symbol')->color('gray')
                            ->action(fn (Forms\Get $get, Forms\Set $set) => $set('robotsTxt', rtrim((string) ($get('robotsTxt') ?: str_replace(rtrim((string) config('gtech.public_url'), '/').'/sitemap.xml', '{sitemap}', Crawl::defaultRobotsTxt())))
                                ."\n\n".implode("\n\n", array_map(fn ($b) => "User-agent: $b\nDisallow: /", ['GPTBot', 'CCBot', 'Google-Extended', 'anthropic-ai', 'Bytespider'])))),
                    ]),
                ]),
                Forms\Components\Tabs\Tab::make('Robots meta tag')->icon('heroicon-o-code-bracket')->schema([
                    Forms\Components\Placeholder::make('metahelp')->hiddenLabel()->content('The <meta name="robots"> tag on every page. To hide a single page, use SEO overrides (or the "Hide from search engines" switch on a post or landing page).'),
                    Forms\Components\Select::make('metaIndex')->label('Default for all pages')->selectablePlaceholder(false)->options([
                        'index, follow' => 'index, follow (normal)', 'index, nofollow' => 'index, nofollow (do not follow links)',
                        'noindex, follow' => 'noindex, follow (hide pages, follow links)', 'noindex, nofollow' => 'noindex, nofollow (hide everything)',
                    ]),
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('maxImagePreview')->label('Image preview size')->options(['' => 'Not set', 'large' => 'Large (recommended)', 'standard' => 'Standard', 'none' => 'None']),
                        Forms\Components\TextInput::make('maxSnippet')->label('Max snippet length')->helperText('-1 = no limit, 0 = no text snippet')->maxLength(5),
                        Forms\Components\TextInput::make('maxVideoPreview')->label('Max video preview (seconds)')->helperText('-1 = no limit')->maxLength(5),
                    ]),
                    Forms\Components\Toggle::make('noarchive')->label('noarchive: no cached copy of pages'),
                    Forms\Components\Toggle::make('notranslate')->label('notranslate: no "Translate this page" link in Google'),
                    Forms\Components\Fieldset::make('Hide these kinds of pages (noindex, follow)')->columns(1)->schema([
                        Forms\Components\Toggle::make('noindexBlogPages')->label('Blog page 2, 3... (the first page stays indexed)'),
                        Forms\Components\Toggle::make('noindexAuthors')->label('Author pages'),
                        Forms\Components\Toggle::make('noindexCaseStudies')->label('Case studies'),
                    ]),
                    Forms\Components\Placeholder::make('preview')->label('Tag on a normal page')->content(fn (Forms\Get $get) => new HtmlString('<code>'.e(self::previewMeta($get)).'</code>')),
                ]),
                Forms\Components\Tabs\Tab::make('Sitemap')->icon('heroicon-o-map')->schema([
                    Forms\Components\CheckboxList::make('sitemapSections')->label('Include in sitemap.xml')->options(Crawl::SITEMAP_SECTIONS)->columns(2)->bulkToggleable(),
                    Forms\Components\Textarea::make('sitemapExclude')->label('Leave these addresses out')->rows(4)->placeholder("/terms\n/cookie-policy\n/case-studies/*")
                        ->helperText('One per line. End with * to leave out everything under an address. Pages hidden from search engines are always left out.'),
                    Forms\Components\Textarea::make('sitemapExtra')->label('Add these addresses')->rows(3)->placeholder("/some-page\nhttps://www.gtechdigital.co.uk/other")->helperText('One per line.'),
                    Forms\Components\Toggle::make('sitemapLastmod')->label('Show the last-modified date (lastmod)'),
                    Forms\Components\Toggle::make('sitemapPriority')->label('Show change frequency and priority (Google ignores these; Bing reads them)'),
                ]),
            ]),
        ]);
    }

    private static function previewMeta(Forms\Get $get): string
    {
        $parts = [(string) $get('metaIndex')];
        foreach (['max-snippet' => 'maxSnippet', 'max-image-preview' => 'maxImagePreview', 'max-video-preview' => 'maxVideoPreview'] as $k => $f) if ((string) $get($f) !== '') $parts[] = "$k:".$get($f);
        if ($get('noarchive')) $parts[] = 'noarchive';
        if ($get('notranslate')) $parts[] = 'notranslate';

        return '<meta name="robots" content="'.implode(', ', $parts).'">';
    }

    public function save(): void
    {
        abort_unless(self::canEdit(), 403);
        $d = $this->form->getState();
        $d['sitemapSections'] = array_values(array_intersect((array) ($d['sitemapSections'] ?? []), array_keys(Crawl::SITEMAP_SECTIONS)));
        foreach (['robotsTxt', 'hosts', 'sitemapExclude', 'sitemapExtra', 'maxSnippet', 'maxVideoPreview', 'maxImagePreview'] as $k) $d[$k] = trim((string) ($d[$k] ?? ''));
        Setting::put('crawl', array_merge(Setting::DEFAULTS['crawl'], $d));
        \App\Support\Site\PageCache::flush();
        Notification::make()->title('Saved. Search engines see the change on their next visit.')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        $site = fn (string $p) => \App\Filament\Support\SiteLink::to($p);

        return [
            Action::make('robots')->label('View robots.txt')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')->url(fn () => $site('/robots.txt'), shouldOpenInNewTab: true),
            Action::make('sitemap')->label('View sitemap.xml')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')->url(fn () => $site('/sitemap.xml'), shouldOpenInNewTab: true),
        ];
    }
}
