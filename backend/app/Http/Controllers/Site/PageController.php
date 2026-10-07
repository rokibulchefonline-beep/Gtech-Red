<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\Site\Repo;
use Illuminate\View\View;

/** Public pages rendered with Blade (the same HTML as the Next.js website). */
class PageController extends Controller
{
    public function service(string $slug): View
    {
        return $this->show("service~$slug");
    }

    public function industry(string $slug): View
    {
        return $this->show("industry~$slug");
    }

    public function legal(string $slug): View
    {
        return $this->show("legal~$slug");
    }

    /** Main pages stored as "page~<slug>": home, about, contact and the two hubs. */
    public function main(string $slug): View
    {
        return $this->show("page~$slug");
    }

    /** Landing pages made in the panel, at /<slug>. */
    public function landing(\Illuminate\Http\Request $request): View
    {
        $slug = trim($request->path(), '/');
        abort_unless($request->isMethod('GET') && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug), 404);
        return $this->show("landing~$slug");
    }

    private function show(string $key): View
    {
        $p = Repo::page($key);
        abort_unless($p && $p->published, 404);
        return self::render($p) ?? abort(404);
    }

    /** The view for a page, also used by the panel's preview with an unsaved copy of the page. */
    public static function render(\App\Models\Page $p): ?View
    {
        Repo::put($p);
        switch ($p->kind) {
            case 'service':
                $found = Repo::item($p->slug);
                $groupPage = Repo::group($p->slug);
                return view('site.pages.service', compact('p', 'found', 'groupPage'));
            case 'industry':
                $ind = Repo::industry($p->slug);
                return $ind ? view('site.pages.industry', compact('p', 'ind')) : null;
            case 'legal':
                return view('site.pages.legal', compact('p'));
            case 'landing':
                return view('site.pages.landing', compact('p'));
            case 'page':
                return view()->exists("site.pages.{$p->slug}") ? view("site.pages.{$p->slug}", compact('p')) : null;
        }
        return null;
    }

    public function caseStudies(): View
    {
        return view('site.pages.case-studies');
    }

    public function caseStudy(string $slug): View
    {
        $d = Repo::caseStudy($slug);
        abort_unless($d, 404);
        return view('site.pages.case-study', compact('d'));
    }

    public function blogs(): View
    {
        return view('site.pages.blogs');
    }

    public function post(string $slug): View
    {
        $p = Repo::posts()->firstWhere('slug', $slug);
        abort_unless($p, 404);
        return view('site.pages.post', compact('p'));
    }
}
