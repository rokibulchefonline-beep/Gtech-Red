<?php

namespace Tests\Feature;

use App\Auth\LegacyAwareUserProvider;
use App\Filament\Admin\Resources;
use App\Models\Lead;
use App\Models\Page;
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
        Artisan::call('gtech:seed-content');
        Post::create(['title' => 'Hello', 'slug' => 'hello', 'status' => 'published', 'body' => '<p>x</p>']);
        Lead::create(['name' => 'L', 'email' => 'l@example.com']);
        $this->actingAs($this->admin());
        foreach (['/admin', '/admin/settings'] as $url) $this->get($url)->assertOk();
        foreach ([Resources\PostResource::class, Resources\CaseStudyResource::class, Resources\CategoryResource::class, Resources\PartnerResource::class,
            Resources\ClientResource::class, Resources\PageResource::class, Resources\ServiceGroupResource::class, Resources\IndustryResource::class,
            Resources\CoreServiceResource::class, Resources\StatResource::class, Resources\TestimonialResource::class, Resources\SeoKeywordResource::class, Resources\SeoEntryResource::class, Resources\LeadResource::class,
            Resources\SubscriberResource::class, Resources\MediaResource::class, Resources\UserResource::class] as $r) {
            $this->get($r::getUrl("index"))->assertOk();
        }
        $this->get(Resources\PostResource::getUrl('create'))->assertOk();
        $this->get(Resources\PostResource::getUrl('edit', ['record' => Post::first()]))->assertOk();
        $this->get(Resources\LeadResource::getUrl('edit', ['record' => Lead::first()]))->assertOk();
        foreach (['page~home', 'service~search-engine-optimization', 'industry~healthcare', 'page~about', 'legal~terms'] as $key) {
            $this->get(Resources\PageResource::getUrl('edit', ['record' => Page::find($key)]))->assertOk()->assertSee('Main heading');
        }
        $this->get(Resources\ServiceGroupResource::getUrl('edit', ['record' => \App\Models\ServiceGroup::first()]))->assertOk()->assertSee('Services in this category');
    }

    public function test_roles_limit_what_people_see(): void
    {
        $this->actingAs($this->admin('sales'));
        $this->get(Resources\LeadResource::getUrl('index'))->assertOk();
        $this->get(Resources\PostResource::getUrl('index'))->assertForbidden();
        $this->get(Resources\UserResource::getUrl('index'))->assertForbidden();
        $this->get('/admin/settings')->assertForbidden();
    }

    public function test_seed_loads_all_content_once(): void
    {
        Artisan::call('gtech:seed-content');
        $this->assertSame(53, Page::count());
        $this->assertSame(5, \App\Models\ServiceGroup::count());
        $this->assertSame(30, \App\Models\ServiceItem::count());
        $this->assertSame(10, \App\Models\Industry::count());
        $this->assertSame(6, Post::count());
        Page::find('page~home')->update(['meta_title' => 'Mine']);
        Artisan::call('gtech:seed-content');
        $this->assertSame('Mine', Page::find('page~home')->meta_title, 'a second run must not overwrite edits');
        $this->assertSame(6, Post::count());
    }

    public function test_page_builder_round_trip_keeps_every_page_unchanged(): void
    {
        Artisan::call('gtech:seed-content');
        foreach (Page::query()->whereIn('kind', Page::BUILDER_KINDS)->get() as $rec) {
            $state = Resources\PageResource::beforeFill($rec->attributesToArray());
            $out = Resources\PageResource::beforeSave($state, $rec);
            $this->assertEquals($rec->sections, $out['sections'], "Sections of {$rec->key} changed on a save without edits");
            $this->assertEquals($rec->hero, $out['hero'], "Hero of {$rec->key} changed");
            $this->assertEquals($rec->faqs, $out['faqs'], "FAQs of {$rec->key} changed");
            $this->assertEquals((array) $rec->related, $out['related']);
        }

        // An edit lands where it should, and a moved section keeps its id.
        $rec = Page::find('service~local-seo');
        $state = Resources\PageResource::beforeFill($rec->attributesToArray());
        $keys = array_keys($state['sections']);
        $state['sections'][$keys[1]]['data']['heading'] = 'New [[heading]]';
        $state['sections'] = [$keys[1] => $state['sections'][$keys[1]]] + $state['sections'];
        $out = Resources\PageResource::beforeSave($state, $rec);
        $this->assertSame('New [[heading]]', $out['sections'][0]['heading']);
        $this->assertSame($rec->sections[1]['id'], $out['sections'][0]['id']);
        $this->assertCount(count($rec->sections), $out['sections']);
    }

    public function test_fixed_layout_editor_round_trip_keeps_layout_and_saves_edits(): void
    {
        Artisan::call('gtech:seed-content');
        $rec = Page::find('page~about');
        $before = $rec->sections;
        $state = Resources\PageResource::beforeFill($rec->toArray());
        $this->assertNotEmpty($state['hero']['h1']);
        $state['hero']['h1'] = 'About [[Us]]';
        $state['secs'][0]['heading'] = 'New [[heading]]';
        $out = Resources\PageResource::beforeSave($state, $rec);
        $this->assertSame('About [[Us]]', $out['hero']['h1']);
        $i = $state['secs'][0]['idx'];
        $this->assertSame('New [[heading]]', $out['sections'][$i]['heading']);
        $this->assertSame($before[$i]['type'] ?? null, $out['sections'][$i]['type'] ?? null);
        $this->assertCount(count($before), $out['sections']);
        $this->assertTrue(Resources\PageResource::restore($rec));
        $this->assertTrue($rec->fresh()->hasDraft());
    }

    public function test_old_page_edits_are_merged_into_pages_and_served_to_the_website(): void
    {
        config(['gtech.api_token' => 'secret-token-1']);
        Artisan::call('gtech:seed-content');
        $p = Page::find('service~local-seo');
        $sid = $p->sections[0]['id'];
        $merged = \App\Support\Content::mergeOverride($p->only(['meta_title', 'meta_description', 'focus_keyword', 'hero', 'sections', 'faqs']),
            ['hero' => ['h1' => 'Edited [[H1]]'], 'sections' => [$sid => ['heading' => 'Edited heading']], 'faqs' => []]);
        $p->fill($merged)->save();
        $doc = $this->withHeader('X-Api-Key', 'secret-token-1')->postJson('/api/v1/query', ['coll' => 'page_content', 'filter' => ['_id' => 'service~local-seo'], 'limit' => 1])->json('rows.0');
        $this->assertSame('Edited [[H1]]', $doc['hero']['h1']);
        $this->assertSame('Edited heading', $doc['sections'][$sid]['heading']);
        $this->assertNotEmpty($doc['faqs']);
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
