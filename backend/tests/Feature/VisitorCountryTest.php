<?php

namespace Tests\Feature;

use App\Models\AnalyticsVisit;
use App\Support\Analytics\Geo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitorCountryTest extends TestCase
{
    use RefreshDatabase;

    public function test_country_comes_from_the_hosting_header_and_is_saved_on_the_visit(): void
    {
        $sid = str_repeat('a', 32);
        $this->withHeaders(['X-Country-Code' => 'gb', 'User-Agent' => 'Mozilla/5.0 Chrome/120'])
            ->postJson('/api/t', ['t' => 'pv', 'sid' => $sid, 'p' => '/', 'r' => '', 'q' => ''])->assertOk();
        $this->assertSame('GB', AnalyticsVisit::query()->where('sid', $sid)->value('country'));
    }

    public function test_unknown_or_private_addresses_get_no_country(): void
    {
        $this->assertSame('', Geo::lookup('127.0.0.1'));
        $this->assertSame('', Geo::lookup('not-an-ip'));
    }

    public function test_country_labels_have_a_flag_and_a_name(): void
    {
        $this->assertStringContainsString('United Kingdom', Geo::label('GB'));
        $this->assertStringStartsWith("\u{1F1EC}\u{1F1E7}", Geo::label('GB'));
    }
}
