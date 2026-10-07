<?php

namespace Tests\Feature;

use App\Filament\Auth\Login;
use App\Models\Post;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\InviteUser;
use App\Support\Permissions;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class UsersRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role, 'active' => true]);
    }

    public function test_default_roles_are_seeded_and_super_admin_has_everything(): void
    {
        $this->assertEqualsCanonicalizing(array_keys(Permissions::defaultRoles()), Role::query()->pluck('key')->all());
        $this->assertSame(Permissions::all(), array_values(array_intersect(Permissions::all(), $this->user('super_admin')->permissions())));
    }

    public function test_an_author_only_reaches_their_sections(): void
    {
        $this->actingAs($this->user('author'));
        $this->get('/admin')->assertOk();
        $this->get('/admin/posts')->assertOk();
        foreach (['/admin/leads', '/admin/settings', '/admin/users', '/admin/analytics', '/admin/roles'] as $url) {
            $this->get($url)->assertForbidden();
        }
    }

    public function test_an_author_cannot_edit_someone_elses_post_or_publish(): void
    {
        $author = $this->user('author');
        $other = Post::query()->create(['slug' => 'theirs', 'title' => 'Theirs', 'status' => 'draft', 'created_by' => $this->user('editor')->id]);
        $own = Post::query()->create(['slug' => 'mine', 'title' => 'Mine', 'status' => 'draft', 'created_by' => $author->id]);
        $this->actingAs($author);
        $this->get("/admin/posts/{$other->getKey()}/edit")->assertForbidden();
        $this->get("/admin/posts/{$own->getKey()}/edit")->assertOk();
        $this->assertFalse($author->hasPerm('posts.publish'));
        $this->assertTrue($this->user('editor')->hasPerm('posts.publish'));
    }

    public function test_a_custom_role_controls_access(): void
    {
        Role::query()->create(['key' => 'leads_only', 'name' => 'Leads only', 'perms' => ['leads.view']]);
        $u = $this->user('leads_only');
        $this->actingAs($u);
        $this->get('/admin/leads')->assertOk();
        $this->get('/admin/posts')->assertForbidden();
        $this->assertFalse($u->hasPerm('leads.delete'));
    }

    public function test_a_user_with_an_unknown_role_cannot_sign_in(): void
    {
        $this->actingAs($this->user('ghost'));
        $this->get('/admin')->assertForbidden();
    }

    public function test_settings_are_read_only_without_edit_permission(): void
    {
        Role::query()->create(['key' => 'settings_viewer', 'name' => 'Settings viewer', 'perms' => ['settings.view']]);
        $this->actingAs($this->user('settings_viewer'));
        $this->get('/admin/settings')->assertOk();
        $before = Setting::all_()['general']['siteName'];
        Livewire::test(\App\Filament\Admin\Pages\Settings::class)->set('data.general.siteName', 'Hacked')->call('save');
        $this->assertSame($before, Setting::all_()['general']['siteName']);
    }

    public function test_inviting_a_user_emails_a_set_password_link(): void
    {
        Notification::fake();
        $this->actingAs($this->user('super_admin'));
        Livewire::test(\App\Filament\Admin\Resources\UserResource\Pages\CreateUser::class)
            ->fillForm(['name' => 'New Person', 'email' => 'new@example.com', 'role' => 'editor', 'invite' => true])
            ->call('create')->assertHasNoFormErrors();
        $u = User::query()->where('email', 'new@example.com')->firstOrFail();
        $this->assertNotNull($u->invited_at);
        Notification::assertSentTo($u, InviteUser::class);
    }

    public function test_eight_wrong_passwords_lock_the_account(): void
    {
        $u = User::factory()->create(['role' => 'admin', 'password' => bcrypt('Right-pass-123')]);
        for ($i = 0; $i < Login::MAX_FAILURES; $i++) {
            $this->travel(61)->seconds(); // past Filament's per-IP limit of 5 a minute
            Livewire::test(Login::class)->set('data.email', $u->email)->set('data.password', 'wrong')->call('authenticate')->assertHasErrors('data.email');
        }
        $this->travel(61)->seconds();
        Livewire::test(Login::class)->set('data.email', $u->email)->set('data.password', 'Right-pass-123')->call('authenticate')->assertHasErrors('data.email');
        $this->assertGuest();
        $this->assertSame(1, DB::table('login_events')->where('email', $u->email)->where('event', 'locked')->count());

        $this->travel(16)->minutes();
        Livewire::test(Login::class)->set('data.email', $u->email)->set('data.password', 'Right-pass-123')->call('authenticate')->assertHasNoErrors();
        $this->assertAuthenticatedAs($u);
        $this->assertNotNull($u->fresh()->last_login_at);
    }

    public function test_my_account_actions_work_on_the_update_route(): void
    {
        $u = $this->user('editor');
        $this->actingAs($u);
        Livewire::test(\Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo::class)->set('data.name', 'Renamed')->call('submit')->assertHasNoErrors();
        $this->assertSame('Renamed', $u->fresh()->name);
    }

    public function test_two_factor_can_be_required_by_setting(): void
    {
        $u = $this->user('editor');
        $this->assertFalse(\App\Support\Security::requiresTwoFactor($u));
        Setting::put('security', ['require2fa' => 'everyone']);
        $this->assertTrue(\App\Support\Security::requiresTwoFactor($u));
    }
}
