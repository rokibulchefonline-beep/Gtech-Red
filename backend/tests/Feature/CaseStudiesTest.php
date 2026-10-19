<?php

namespace Tests\Feature;

use App\Models\CaseStudy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseStudiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_real_case_studies_load_once_with_two_images_each(): void
    {
        $this->artisan('gtech:load-case-studies')->assertSuccessful();
        $this->artisan('gtech:load-case-studies')->assertSuccessful();

        $this->assertSame(16, CaseStudy::count());
        foreach (CaseStudy::all() as $c) {
            $this->assertArrayHasKey($c->category, CaseStudy::CATEGORIES);
            $this->assertFileExists(public_path($c->image));
            $this->assertFileExists(public_path($c->gallery[0]['src']));
            $this->assertSame([], $c->metrics);
        }
        $this->assertSame(['app-development', 'digital-marketing', 'seo', 'web-development'], CaseStudy::where('client', 'ChefOnline')->orderBy('category')->pluck('category')->all());
    }

    public function test_demo_case_studies_are_replaced_and_redirect(): void
    {
        foreach (\App\Support\Content::get('demo.caseStudies') as $d) CaseStudy::create(['title' => $d['title'], 'slug' => $d['slug'], 'status' => 'published']);
        CaseStudy::where('slug', 'arta')->update(['title' => 'Edited']);

        $this->artisan('gtech:load-case-studies')->assertSuccessful();

        $this->assertSame(['arta'], CaseStudy::whereIn('slug', ['chefonline', 'arta', 'salik-and-co', 'table-booking'])->pluck('slug')->all());
        $this->get('/case-studies/chefonline')->assertRedirect('/case-studies/chefonline-app-development');
    }

    public function test_listing_filters_by_category_and_page_shows_gallery(): void
    {
        $this->artisan('gtech:load-case-studies');

        $this->get('/case-studies')->assertOk()->assertSee('data-cs-filter', false)->assertSee('App Development')->assertSee('Salik &amp; Co Custom Software', false);
        $html = $this->get('/case-studies?category=seo')->assertOk()->getContent();
        $this->assertSame(5, substr_count($html, 'data-cat="seo" >'));
        $this->assertSame(11, substr_count($html, ' hidden >'));

        $this->get('/case-studies/chefonline-app-development')->assertOk()
            ->assertSee('<h1>ChefOnline App Development Case Study', false)
            ->assertSee('cs-gallery', false)->assertSee('/case/chefonline-app-development-2.webp', false)
            ->assertSee('App Store');
    }
}
