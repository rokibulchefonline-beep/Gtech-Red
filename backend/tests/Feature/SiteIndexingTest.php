<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteIndexingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['gtech.blade_live' => true]); // as on the server
    }

    public function test_a_test_domain_is_kept_out_of_search_engines(): void
    {
        $this->get('http://www.testmyproject.co.uk/robots.txt')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('http://www.testmyproject.co.uk/case-studies')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow')->assertSee('content="noindex', false);
    }

    public function test_the_live_domain_can_be_indexed(): void
    {
        $this->get('http://www.gtechdigital.co.uk/case-studies')->assertOk()->assertHeaderMissing('X-Robots-Tag')->assertDontSee('content="noindex', false);
    }

    public function test_the_env_setting_overrides_the_domain_check(): void
    {
        config(['gtech.indexing.mode' => 'on']);
        $this->get('http://www.testmyproject.co.uk/robots.txt')->assertHeaderMissing('X-Robots-Tag');
    }
}
