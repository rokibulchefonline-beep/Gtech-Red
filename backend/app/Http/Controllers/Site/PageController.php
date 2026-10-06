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
}
