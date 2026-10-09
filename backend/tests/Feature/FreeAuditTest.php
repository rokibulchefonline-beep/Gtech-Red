<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\AuditRequestResource\Pages\EditAuditRequest;
use App\Filament\Admin\Resources\AuditRequestResource\Pages\ListAuditRequests;
use App\Filament\Admin\Resources\LeadResource\Pages\ListLead;
use App\Models\Lead;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class FreeAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Artisan::call('gtech:seed-content');
        \App\Support\Site\Repo::flush();
    }

    public function test_page_header_and_footer_link_to_it(): void
    {
        $this->get('/free-audit')->assertOk()->assertSee('data-form="audit"', false)->assertSee('What the')
            ->assertSee('<a class="btn sm line" href="/free-audit">Free Audit</a>', false)->assertSee('<a href="/free-audit">Free Audit</a>', false);
        $this->get('/sitemap.xml')->assertSee('/free-audit');
    }

    public function test_request_is_saved_as_an_audit_lead_with_its_answers(): void
    {
        $form = ['source' => 'audit', 'name' => 'Jo', 'business' => 'Co', 'email' => 'jo@example.com', 'phone' => '0700'];
        $this->postJson('/api/contact', $form)->assertStatus(400); // website is required
        $this->postJson('/api/contact', $form + ['website' => 'co.uk', 'goals' => ['More leads or enquiries', 'Lower ad costs'], 'competitors' => 'rival.co.uk'])->assertOk();
        $lead = Lead::query()->sole();
        $this->assertSame(['audit', 'Free audit'], [$lead->source, $lead->service]);
        $this->assertSame(['More leads or enquiries', 'Lower ad costs'], $lead->details['goals']);
        $this->assertSame('rival.co.uk', $lead->details['competitors']);
    }

    public function test_audits_have_their_own_admin_list(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin', 'active' => true]));
        $base = ['business' => 'B', 'email' => 'a@b.co', 'phone' => '1', 'status' => 'new', 'notes' => '', 'assignee' => ''];
        $audit = Lead::create($base + ['name' => 'Audit Person', 'source' => 'audit', 'service' => 'Free audit', 'details' => ['goals' => ['More online sales']]]);
        $lead = Lead::create($base + ['name' => 'Contact Person', 'source' => 'contact', 'service' => 'SEO']);
        Livewire::test(ListAuditRequests::class)->assertCanSeeTableRecords([$audit])->assertCanNotSeeTableRecords([$lead]);
        Livewire::test(ListLead::class)->assertCanSeeTableRecords([$lead])->assertCanNotSeeTableRecords([$audit]);
        Livewire::test(EditAuditRequest::class, ['record' => $audit->getRouteKey()])->assertOk()->assertSee('More online sales');
    }
}
