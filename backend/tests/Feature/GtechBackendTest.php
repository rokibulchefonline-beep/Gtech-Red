<?php

namespace Tests\Feature;

use App\Auth\LegacyAwareUserProvider;
use App\Filament\Admin\Resources;
use App\Models\Lead;
use App\Models\PageContent;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class GtechBackendTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $role = 'super_admin'): User
    {
        return User::forceCreate(['name' => 'A', 'email' => "$role@example.com", 'role' => $role, 'active' => true, 'password' => bcrypt('Testpass123')]);
    }

    private function legacyHash(string $pw): string
    {
        $salt = random_bytes(16);
        return 'pbkdf2$100000$'.base64_encode($salt).'$'.base64_encode(hash_pbkdf2('sha256', $pw, $salt, 100000, 32, true));
    }

    public function test_old_pbkdf2_passwords_work_and_are_upgraded(): void
    {
        $u = User::forceCreate(['name' => 'Old', 'email' => 'old@example.com', 'role' => 'admin', 'active' => true, 'password' => $this->legacyHash('OldPassword123')]);
        $this->assertFalse(Auth::attempt(['email' => 'old@example.com', 'password' => 'wrong-pass-1']));
        $this->assertTrue(Auth::attempt(['email' => 'old@example.com', 'password' => 'OldPassword123']));
        $this->assertStringStartsWith('$2y$', $u->fresh()->password);
        Auth::logout();
        $this->assertTrue(Auth::attempt(['email' => 'old@example.com', 'password' => 'OldPassword123']));
        $this->assertTrue(LegacyAwareUserProvider::checkPbkdf2('x1y2z3', $this->legacyHash('x1y2z3')));
    }

    public function test_every_panel_screen_loads_for_a_super_admin(): void
    {
        Artisan::call('gtech:sync-pages');
        Post::create(['title' => 'Hello', 'slug' => 'hello', 'status' => 'published', 'body' => '<p>x</p>']);
        Lead::create(['name' => 'L', 'email' => 'l@example.com']);
        $this->actingAs($this->admin());
        foreach (['/admin', '/admin/settings'] as $url) $this->get($url)->assertOk();
        foreach ([Resources\PostResource::class, Resources\CaseStudyResource::class, Resources\CategoryResource::class, Resources\PartnerResource::class,
            Resources\ClientResource::class, Resources\PageContentResource::class, Resources\SeoEntryResource::class, Resources\LeadResource::class,
            Resources\SubscriberResource::class, Resources\MediaResource::class, Resources\UserResource::class] as $r) {
            $this->get($r::getUrl("index"))->assertOk();
        }
        $this->get(Resources\PostResource::getUrl('create'))->assertOk();
        $this->get(Resources\PostResource::getUrl('edit', ['record' => Post::first()]))->assertOk();
        $this->get(Resources\LeadResource::getUrl('edit', ['record' => Lead::first()]))->assertOk();
        foreach (['page~home', 'service~search-engine-optimization', 'industry~healthcare', 'page~about'] as $key) {
            $this->get(Resources\PageContentResource::getUrl('edit', ['record' => PageContent::find($key)]))->assertOk()->assertSee('Main heading');
        }
    }

    public function test_roles_limit_what_people_see(): void
    {
        $this->actingAs($this->admin('sales'));
        $this->get(Resources\LeadResource::getUrl('index'))->assertOk();
        $this->get(Resources\PostResource::getUrl('index'))->assertForbidden();
        $this->get(Resources\UserResource::getUrl('index'))->assertForbidden();
        $this->get('/admin/settings')->assertForbidden();
    }

    public function test_page_editor_stores_only_changes(): void
    {
        Artisan::call('gtech:sync-pages');
        $rec = PageContent::find('service~local-seo');
        $state = Resources\PageContentResource::beforeFill($rec->toArray());
        $this->assertNotEmpty($state['hero']['h1']);
        $this->assertSame([], Resources\PageContentResource::beforeSave($state, $rec)['sections']);
        $state['hero']['h1'] = 'Local SEO [[That Works]]';
        $state['secs'][1]['heading'] = 'New [[heading]]';
        $out = Resources\PageContentResource::beforeSave($state, $rec);
        $this->assertSame('Local SEO [[That Works]]', $out['hero']['h1']);
        $this->assertSame(['heading' => 'New [[heading]]'], $out['sections'][$state['secs'][1]['id']]);
        $this->assertArrayNotHasKey('lead', $out['hero']);
    }

    public function test_query_api_needs_the_token_and_returns_old_document_shape(): void
    {
        config(['gtech.api_token' => 'secret-token-1']);
        Post::create(['legacy_id' => 'abc123', 'title' => 'Hello', 'slug' => 'hello', 'status' => 'published', 'body' => '<p>x</p>', 'meta_title' => 'MT']);
        Post::create(['title' => 'Draft', 'slug' => 'draft', 'status' => 'draft']);
        $this->postJson('/api/v1/query', ['coll' => 'posts'])->assertUnauthorized();
        $res = $this->withHeader('X-Api-Key', 'secret-token-1')->postJson('/api/v1/query', ['coll' => 'posts', 'filter' => ['status' => ['$in' => ['published', 'scheduled']]], 'limit' => 10]);
        $res->assertOk()->assertJsonCount(1, 'rows')->assertJsonPath('rows.0._id', 'abc123')->assertJsonPath('rows.0.metaTitle', 'MT');
        $this->withHeader('X-Api-Key', 'secret-token-1')->postJson('/api/v1/query', ['coll' => 'posts', 'count' => true])->assertJsonPath('total', 2);
        $this->withHeader('X-Api-Key', 'secret-token-1')->postJson('/api/v1/query', ['coll' => 'users'])->assertStatus(422);
        $s = $this->withHeader('X-Api-Key', 'secret-token-1')->postJson('/api/v1/query', ['coll' => 'settings', 'filter' => ['_id' => 'site']])->json('rows.0');
        $this->assertArrayNotHasKey('smtp', $s);
    }

    public function test_contact_form_saves_a_lead(): void
    {
        $this->postJson('/api/v1/contact', ['name' => 'Jo', 'business' => 'Co', 'email' => 'jo@example.com', 'phone' => '0700', 'service' => 'SEO', 'budget' => '£1k'])->assertOk();
        $this->assertSame('new', Lead::first()->status);
        $this->postJson('/api/v1/contact', ['name' => 'Jo'])->assertStatus(400);
        $this->postJson('/api/v1/subscribe', ['email' => 'a@example.com'])->assertOk();
        $this->postJson('/api/v1/subscribe', ['email' => 'a@example.com'])->assertOk();
        $this->assertDatabaseCount('subscribers', 1);
    }
}
