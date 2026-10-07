<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\Site\PagePreview;
use Illuminate\Http\Response;

/** The panel's page preview: a frame with desktop, tablet and phone widths around the page as it would look. */
class PreviewController extends Controller
{
    private function page(string $token)
    {
        abort_unless(auth()->user()?->hasPerm('pages.view'), 403);
        return PagePreview::page($token) ?? abort(410, 'This preview has expired. Click Preview again in the editor.');
    }

    public function show(string $token): Response
    {
        $page = $this->page($token);
        return response()->view('site.preview-frame', ['page' => $page, 'frame' => url("/preview/page/$token/frame")])
            ->header('X-Robots-Tag', 'noindex, nofollow')->header('Cache-Control', 'no-store');
    }

    public function frame(string $token): Response
    {
        $view = PageController::render($this->page($token)) ?? abort(404);
        return response($view->render())->header('X-Robots-Tag', 'noindex, nofollow')->header('Cache-Control', 'no-store');
    }
}
