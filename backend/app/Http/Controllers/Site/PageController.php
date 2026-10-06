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
        $p = Repo::page("service~$slug");
        abort_unless($p && $p->published, 404);
        $found = Repo::item($slug);
        $groupPage = Repo::group($slug);
        return view('site.pages.service', compact('p', 'found', 'groupPage'));
    }

    public function industry(string $slug): View
    {
        $p = Repo::page("industry~$slug");
        $ind = Repo::industry($slug);
        abort_unless($p && $p->published && $ind, 404);
        return view('site.pages.industry', compact('p', 'ind'));
    }

    public function legal(string $slug): View
    {
        $p = Repo::page("legal~$slug");
        abort_unless($p && $p->published, 404);
        return view('site.pages.legal', compact('p'));
    }

    /** Main pages stored as "page~<slug>": home, about, contact and the two hubs. */
    public function main(string $slug): View
    {
        $p = Repo::page("page~$slug");
        abort_unless($p && $p->published, 404);
        return view("site.pages.$slug", compact('p'));
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
}
