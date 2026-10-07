<?php

namespace Tests\Feature;

use App\Models\CaseStudy;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/** The fixes from the SEO and crawl report. */
class SeoFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('gtech:seed-content');
        \App\Support\Site\Repo::flush();
    }

    public function test_no_borrowed_logos_or_placeholder_case_studies(): void
    {
        $this->assertSame(0, Client::query()->where('logo', 'like', '%growmemarketing%')->count());
        $this->assertSame(0, CaseStudy::query()->where('slug', 'like', 'demo-brand-%')->count());
        // With no client logos the logo strips are left out instead of showing empty.
        Client::query()->update(['visible' => false]);
        $this->get('/')->assertOk()->assertDontSee('class="brands"', false);
        $this->get('/services/local-seo')->assertOk()->assertDontSee('sp-logo-row', false);
    }
}
