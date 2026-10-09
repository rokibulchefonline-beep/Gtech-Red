<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\Pipeline;
use App\Filament\Admin\Resources\ContactResource\Pages\ViewContact;
use App\Filament\Admin\Resources\LeadResource\Pages\EditLead;
use App\Models\Contact;
use App\Models\EmailTemplate;
use App\Models\Erasure;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Setting;
use App\Models\Subscriber;
use App\Models\User;
use App\Support\Crm\Gdpr;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class CrmStep5Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Notification::fake();
    }

    private function user(string $role = 'sales_manager'): User
    {
        $u = User::factory()->create(['role' => $role, 'active' => true, 'name' => 'Sam Seller']);
        $this->actingAs($u);
        return $u;
    }

    private function submit(array $extra = [])
    {
        return $this->postJson('/api/contact', ['name' => 'Jane Doe', 'business' => 'Acme', 'email' => 'jane@example.com', 'phone' => '07700900000', 'service' => 'SEO', 'budget' => '£500', ...$extra]);
    }

    public function test_repeat_enquiries_join_one_contact_and_record_the_notice(): void
    {
        $this->submit()->assertOk();
        $this->submit(['email' => 'JANE@example.com', 'service' => 'Google Ads'])->assertOk();
        $this->assertSame(1, Contact::query()->count());
        $c = Contact::query()->first();
        $this->assertSame(2, $c->leads()->count());
        $lead = Lead::query()->first();
        $this->assertStringContainsString('Privacy Policy (/privacy-policy)', $lead->consent_text);
        $this->assertNotSame('', $lead->ip);
    }

    public function test_the_forms_show_the_privacy_notice(): void
    {
        \Illuminate\Support\Facades\Artisan::call('gtech:seed-content');
        $this->get('/contact')->assertOk()->assertSee('<p class="form-notice">We use your details only to reply to your enquiry. See our <a href="/privacy-policy">Privacy Policy</a>.</p>', false)
            ->assertDontSee('cf-turnstile');
    }

    public function test_too_many_enquiries_from_one_network_are_refused(): void
    {
        foreach (range(1, 5) as $i) $this->submit(['email' => "p$i@example.com"])->assertOk();
        $this->submit(['email' => 'p6@example.com'])->assertStatus(429);
    }

    public function test_stages_can_be_renamed_and_the_board_moves_leads(): void
    {
        $this->user();
        Setting::put('pipeline', ['stages' => [['key' => 'new', 'label' => 'Fresh'], ['key' => 'meeting', 'label' => 'Meeting booked'], ['key' => 'won', 'label' => 'Won'], ['key' => 'lost', 'label' => 'Lost']]]);
        $lead = Lead::create(['name' => 'Jane', 'email' => 'j@example.com', 'service' => 'SEO', 'status' => 'new']);
        Livewire::test(Pipeline::class)->assertSee('Fresh')->assertSee('Meeting booked')->call('move', $lead->id, 'meeting');
        $this->assertSame('meeting', $lead->fresh()->status);
        $this->assertStringContainsString('Fresh → Meeting booked', $lead->activities()->where('type', 'status')->value('body'));
        Livewire::test(Pipeline::class)->call('move', $lead->id, 'nonsense')->assertForbidden();
    }

    public function test_sales_reps_cannot_move_others_leads_on_the_board(): void
    {
        $lead = Lead::create(['name' => 'Jane', 'email' => 'j@example.com', 'service' => 'SEO', 'status' => 'new']);
        $this->user('sales');
        Livewire::test(Pipeline::class)->assertDontSee('Jane')->call('move', $lead->id, 'won');
        $this->assertSame('new', $lead->fresh()->status);
    }

    public function test_sending_a_template_email_logs_it_and_moves_the_lead_on(): void
    {
        Mail::fake();
        Setting::put('smtp', ['host' => 'smtp.example.com', 'port' => 587, 'user' => 'u', 'pass' => '', 'fromEmail' => 'hello@example.com', 'fromName' => 'GTech', 'notifyTo' => '', 'autoReply' => false]);
        $me = $this->user();
        $lead = Lead::create(['name' => 'Jane Doe', 'business' => 'Acme', 'email' => 'jane@example.com', 'service' => 'Local SEO', 'status' => 'new']);
        $t = EmailTemplate::query()->create(['name' => 'Hi', 'subject' => 'About {service}', 'body' => '<p>Hi {first_name}, {my_name} here.</p>']);
        $this->assertSame('<p>Hi Jane, Sam Seller here.</p>', EmailTemplate::render($t->body, $lead, $me));

        Livewire::test(EditLead::class, ['record' => $lead->getKey()])
            ->callAction('sendEmail', ['subject' => 'About Local SEO', 'body' => '<p>Hi Jane</p>'])->assertHasNoActionErrors();
        $lead->refresh();
        $this->assertSame('contacted', $lead->status);
        $this->assertNotNull($lead->first_contacted_at);
        $this->assertStringContainsString('About Local SEO', $lead->activities()->where('type', 'email')->value('body'));
    }

    public function test_a_persons_data_can_be_exported_and_erased(): void
    {
        $this->user('admin');
        $this->submit()->assertOk();
        $this->submit(['service' => 'Google Ads'])->assertOk();
        Subscriber::query()->create(['email' => 'jane@example.com', 'source' => 'blog']);
        $c = Contact::query()->firstOrFail();

        $data = Gdpr::export($c);
        $this->assertCount(2, $data['enquiries']);
        $this->assertNotNull($data['newsletter']);
        $this->assertNotEmpty($data['enquiries'][0]['contact_history']);

        Livewire::test(ViewContact::class, ['record' => $c->getKey()])
            ->callAction('erase', ['confirm' => 'wrong@example.com'])->assertHasActionErrors(['confirm']);
        Livewire::test(ViewContact::class, ['record' => $c->getKey()])
            ->callAction('erase', ['confirm' => 'jane@example.com', 'reason' => 'Request by email'])->assertHasNoActionErrors();
        $this->assertSame(0, Lead::query()->count());
        $this->assertSame(0, LeadActivity::query()->count());
        $this->assertSame(0, Subscriber::query()->count());
        $this->assertSame(0, Contact::query()->count());
        $this->assertSame(Erasure::hash('jane@example.com'), Erasure::query()->value('email_hash'));
    }

    public function test_old_lost_leads_are_deleted_after_the_retention_period(): void
    {
        Setting::put('leads', ['retainMonths' => 12]);
        $old = Lead::create(['name' => 'Old', 'email' => 'old@example.com', 'service' => 'SEO', 'status' => 'lost']);
        $won = Lead::create(['name' => 'Won', 'email' => 'won@example.com', 'service' => 'SEO', 'status' => 'won']);
        Lead::query()->whereKey([$old->id, $won->id])->update(['updated_at' => now()->subMonths(13)]);
        $recent = Lead::create(['name' => 'Recent', 'email' => 'r@example.com', 'service' => 'SEO', 'status' => 'lost']);
        $this->assertSame(1, Gdpr::purgeOld());
        $this->assertNull(Lead::query()->find($old->id));
        $this->assertNotNull(Lead::query()->find($won->id));
        $this->assertNotNull(Lead::query()->find($recent->id));
        $this->assertNull(Contact::query()->where('email', 'old@example.com')->first());
    }

    public function test_merging_contacts(): void
    {
        $a = Lead::create(['name' => 'Jane', 'email' => 'jane@work.com', 'service' => 'SEO', 'status' => 'new']);
        $b = Lead::create(['name' => 'Jane', 'email' => 'jane@home.com', 'service' => 'Ads', 'status' => 'new']);
        Gdpr::merge($a->contact, $b->contact);
        $this->assertSame(1, Contact::query()->count());
        $this->assertSame(2, $a->contact->leads()->count());
    }
}
