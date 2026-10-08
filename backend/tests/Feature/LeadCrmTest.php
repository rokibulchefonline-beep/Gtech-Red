<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\LeadResource;
use App\Filament\Admin\Resources\LeadResource\RelationManagers\ActivitiesRelationManager;
use App\Models\Lead;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\FollowUpReminder;
use App\Notifications\LeadAssigned;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class LeadCrmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Notification::fake();
        Http::fake();
    }

    private function user(string $role, string $name = 'Someone'): User
    {
        return User::factory()->create(['role' => $role, 'active' => true, 'name' => $name]);
    }

    private function lead(array $attrs = []): Lead
    {
        return Lead::create(['name' => 'Jane', 'business' => 'Acme', 'email' => 'jane@example.com', 'phone' => '07700 900000', 'service' => 'SEO', 'status' => 'new', ...$attrs]);
    }

    private function submit(array $extra = [])
    {
        return $this->postJson('/api/contact', ['name' => 'Jane', 'business' => 'Acme', 'email' => 'jane@example.com', 'phone' => '07700900000', 'service' => 'SEO', 'budget' => '£500', ...$extra]);
    }

    public function test_a_form_lead_records_its_source_and_starts_its_timeline(): void
    {
        $sid = str_repeat('a', 32);
        \App\Models\AnalyticsVisit::query()->create(['sid' => $sid, 'visitor' => 'v', 'started_at' => now(), 'landing_path' => '/services/seo', 'channel' => 'AI', 'source' => 'ChatGPT', 'utm_campaign' => 'x']);
        $this->submit(['sid' => $sid])->assertOk();
        $lead = Lead::query()->firstOrFail();
        $this->assertSame(['AI', 'ChatGPT', '/services/seo', 'x'], [$lead->channel, $lead->origin, $lead->landing_path, $lead->utm_campaign]);
        $this->assertSame('ChatGPT (AI assistant)', $lead->originLabel());
        $this->assertSame('created', $lead->activities()->first()->type);
    }

    public function test_round_robin_assigns_new_leads_in_turn_and_emails_the_owner(): void
    {
        $a = $this->user('sales', 'Ann'); $b = $this->user('sales', 'Ben'); $off = $this->user('sales', 'Off');
        $off->forceFill(['active' => false])->save();
        Setting::put('leads', ['autoAssign' => 'round_robin', 'assignees' => [$a->id, $off->id, $b->id], 'webhook' => 'https://hooks.slack.com/services/T/B/X', 'reminders' => true]);
        foreach (range(1, 3) as $i) $this->submit(['email' => "j$i@example.com"])->assertOk();
        $this->assertSame([$a->id, $b->id, $a->id], Lead::query()->orderBy('id')->pluck('assigned_to')->all());
        Notification::assertSentToTimes($a, LeadAssigned::class, 2);
        Notification::assertSentToTimes($b, LeadAssigned::class, 1);
        Notification::assertNothingSentTo($off);
        Http::assertSentCount(3);
        Http::assertSent(fn ($r) => str_contains($r['text'], 'New lead') && str_contains($r['text'], 'Assigned to'));
    }

    public function test_changes_are_logged_on_the_timeline(): void
    {
        $boss = $this->user('sales_manager', 'Boss'); $rep = $this->user('sales', 'Rep');
        $lead = $this->lead();
        $this->actingAs($boss);
        $lead->update(['assigned_to' => $rep->id, 'status' => 'contacted', 'next_action_at' => today()->addDay(), 'next_action' => 'Call back', 'value' => 1500]);
        $types = $lead->activities()->pluck('type')->all();
        foreach (['created', 'status', 'assigned', 'follow_up', 'value'] as $t) $this->assertContains($t, $types);
        $this->assertSame('Rep', $lead->fresh()->assignee);
        $this->assertNotNull($lead->fresh()->first_contacted_at);
        Notification::assertSentTo($rep, LeadAssigned::class);

        // Assigning to yourself sends nothing.
        $lead->update(['assigned_to' => $boss->id]);
        Notification::assertNotSentTo($boss, LeadAssigned::class);
    }

    public function test_sales_reps_only_see_their_own_leads(): void
    {
        $rep = $this->user('sales'); $other = $this->user('sales');
        $mine = $this->lead(['assigned_to' => $rep->id]); $theirs = $this->lead(['assigned_to' => $other->id]); $nobody = $this->lead();
        $this->actingAs($rep);
        $this->get('/admin/leads')->assertOk()->assertSee(LeadResource::getUrl('edit', ['record' => $mine]))->assertDontSee(LeadResource::getUrl('edit', ['record' => $theirs]));
        $this->get(LeadResource::getUrl('edit', ['record' => $theirs]))->assertNotFound();
        $this->get(LeadResource::getUrl('edit', ['record' => $nobody]))->assertNotFound();
        $this->get(LeadResource::getUrl('edit', ['record' => $mine]))->assertOk();
        $this->assertFalse($rep->hasPerm('leads.assign'));

        $this->actingAs($this->user('sales_manager'));
        $this->get(LeadResource::getUrl('edit', ['record' => $theirs]))->assertOk();
    }

    public function test_the_details_page_shows_the_summary_notes_to_everyone_who_can_see_leads(): void
    {
        $lead = $this->lead(['notes' => 'Wants a full SEO audit before March. Budget approved by the MD.']);
        $this->actingAs($this->user('viewer'));
        $this->get(LeadResource::getUrl('view', ['record' => $lead]))->assertOk()
            ->assertSee('Summary notes')->assertSee('Wants a full SEO audit before March.', false);
        $rep = $this->user('sales');
        $lead->update(['assigned_to' => $rep->id]);
        $this->actingAs($rep);
        $this->get(LeadResource::getUrl('view', ['record' => $lead]))->assertOk()->assertSee('Wants a full SEO audit', false);
    }

    public function test_a_viewer_can_open_leads_read_only(): void
    {
        $lead = $this->lead();
        $this->actingAs($this->user('viewer'));
        $this->get(LeadResource::getUrl('view', ['record' => $lead]))->assertOk();
        $this->get(LeadResource::getUrl('edit', ['record' => $lead]))->assertForbidden();
    }

    public function test_logging_a_call_moves_a_new_lead_on_and_sets_the_follow_up(): void
    {
        $rep = $this->user('sales');
        $lead = $this->lead(['assigned_to' => $rep->id]);
        $this->actingAs($rep);
        Livewire::test(ActivitiesRelationManager::class, ['ownerRecord' => $lead, 'pageClass' => LeadResource\Pages\EditLead::class])
            ->callTableAction('create', data: ['type' => 'call', 'body' => 'Spoke to Jane', 'next_action_at' => today()->addDays(2)->toDateString(), 'next_action' => 'Send proposal'])
            ->assertHasNoTableActionErrors();
        $lead->refresh();
        $this->assertSame('contacted', $lead->status);
        $this->assertSame('Send proposal', $lead->next_action);
        $this->assertTrue($lead->next_action_at->isSameDay(today()->addDays(2)));
        $this->assertNotNull($lead->first_contacted_at);
        $this->assertSame($rep->id, $lead->activities()->where('type', 'call')->value('user_id'));
    }

    public function test_morning_reminders_go_to_owners_with_due_follow_ups(): void
    {
        $a = $this->user('sales'); $b = $this->user('sales');
        $this->lead(['assigned_to' => $a->id, 'next_action_at' => today()->subDays(2)]);
        $this->lead(['assigned_to' => $a->id, 'next_action_at' => today()]);
        $this->lead(['assigned_to' => $a->id, 'next_action_at' => today()->subDay(), 'status' => 'won']);
        $this->lead(['assigned_to' => $b->id, 'next_action_at' => today()->addDay()]);
        $this->artisan('leads:remind')->assertSuccessful();
        Notification::assertSentTo($a, FollowUpReminder::class, fn ($n) => $n->leads->count() === 2);
        Notification::assertNotSentTo($b, FollowUpReminder::class);
    }

    public function test_the_dashboard_lists_my_follow_ups(): void
    {
        $rep = $this->user('sales', 'Rep');
        $this->lead(['name' => 'Overdue Olive', 'assigned_to' => $rep->id, 'status' => 'contacted', 'next_action_at' => today()->subDay()]);
        $this->lead(['name' => 'Later Larry', 'assigned_to' => $rep->id, 'status' => 'contacted', 'next_action_at' => today()->addWeek()]);
        $this->actingAs($rep);
        Livewire::test(\App\Filament\Admin\Widgets\MyFollowUps::class)->assertSee('Overdue Olive')->assertDontSee('Later Larry');
    }

    public function test_webhook_addresses_are_checked(): void
    {
        $this->assertTrue(\App\Support\Crm\LeadRouting::validWebhook('https://hooks.slack.com/services/T/B/X'));
        $this->assertFalse(\App\Support\Crm\LeadRouting::validWebhook('http://hooks.slack.com/services/T/B/X'));
        $this->assertFalse(\App\Support\Crm\LeadRouting::validWebhook('https://localhost/x'));
    }
}
