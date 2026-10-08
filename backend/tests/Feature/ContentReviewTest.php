<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Support\Site\Content\ContentReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ContentReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('gtech:seed-content');
        \App\Support\Site\Repo::flush();
    }

    public function test_industry_pages_get_intent_sections_and_natural_headings(): void
    {
        foreach (array_keys(\App\Support\Site\Content\IndustryCopy::all()) as $slug) {
            $p = Page::find("industry~$slug");
            $ids = array_column($p->sections, 'id');
            foreach (['who', 'compare', 'pricing'] as $id) $this->assertContains($id, $ids, "$slug $id");
            $headings = array_column($p->sections, 'heading');
            $this->assertEmpty(array_filter($headings, fn ($h) => preg_match('/^[A-Za-z0-9 ]+ Marketing: /', (string) $h)), "$slug still has 'X Marketing:' headings");
            $this->assertLessThanOrEqual(160, mb_strlen($p->meta_description), $slug);
            $this->assertLessThanOrEqual(10, count($p->faqs));
        }
        $this->get('/industries/travel')->assertOk()->assertSee('ATOL')->assertSee('How do I market my travel business?');
        $this->get('/industries')->assertOk()->assertSee('Which industries does GTech Digital work with?')->assertSee('"FAQPage"', false);
        $this->get('/about')->assertOk()->assertSee('since 2014');
    }

    public function test_panel_edits_are_kept_and_running_again_changes_nothing(): void
    {
        $p = Page::find('industry~automotive');
        $s = $p->sections;
        foreach ($s as $i => $sec) if ($sec['id'] === 'growth') $s[$i]['heading'] = 'My own heading';
        $p->sections = $s;
        $p->meta_title = 'My own title';
        $p->save();
        ContentReview::apply();
        ContentReview::apply();
        $p->refresh();
        $this->assertSame('My own title', $p->meta_title);
        $this->assertSame('My own heading', collect($p->sections)->firstWhere('id', 'growth')['heading']);
        $this->assertSame(1, collect($p->sections)->where('id', 'who')->count());
        $this->assertSame(count(array_unique(array_column($p->faqs, 'q'))), count($p->faqs));
    }
}
