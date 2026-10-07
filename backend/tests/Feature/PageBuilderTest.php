<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\VersionHistory;
use App\Filament\Admin\Resources\PageResource;
use App\Filament\Admin\Resources\PageResource\Pages\CreatePage;
use App\Filament\Admin\Resources\PageResource\Pages\EditPage;
use App\Models\Page;
use App\Models\Post;
use App\Models\Revision;
use App\Models\Role;
use App\Models\User;
use App\Support\RevisionDiff;
use App\Support\Site\PagePreview;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class PageBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Artisan::call('gtech:seed-content');
        $this->flush();
    }

    private function flush(): void
    {
        \App\Support\Site\Repo::flush();
        \App\View\SiteComposer::flush();
    }

    private function editor(string $role = 'editor'): User
    {
        $u = User::factory()->create(['role' => $role, 'active' => true]);
        $this->actingAs($u);
        return $u;
    }

    public function test_a_landing_page_is_created_as_a_copy_and_only_shows_once_published(): void
    {
        $this->editor();
        Livewire::test(CreatePage::class)
            ->fillForm(['name' => 'Free SEO Audit', 'slug' => 'free-seo-audit', 'from' => 'service~local-seo'])
            ->call('create')->assertHasNoFormErrors();
        $p = Page::query()->findOrFail('landing~free-seo-audit');
        $this->assertFalse($p->published);
        $this->assertEquals(Page::find('service~local-seo')->sections, $p->sections);
        $this->get('/free-seo-audit')->assertNotFound();

        $p->publish();
        $this->flush();
        $this->get('/free-seo-audit')->assertOk()->assertSee('Free SEO Audit')->assertSee('Google Map Pack');
        $this->get('/sitemap.xml')->assertSee('/free-seo-audit');
    }

    public function test_landing_addresses_cannot_clash(): void
    {
        $this->editor();
        foreach (['services', 'admin', 'Bad Address', 'contact'] as $slug) {
            Livewire::test(CreatePage::class)->fillForm(['name' => 'X', 'slug' => $slug])->call('create')->assertHasFormErrors(['slug']);
        }
    }

    public function test_drafts_stay_off_the_website_until_published(): void
    {
        $this->editor();
        $p = Page::find('service~local-seo');
        $component = Livewire::test(EditPage::class, ['record' => $p->getKey()]);
        $state = $component->get('data');
        $component->set('data.sections', array_slice($state['sections'], 1, null, true)); // remove the first section (client logos)
        $component->set('data.hero.h1', 'Draft [[Heading]]')->call('save')->assertHasNoErrors();

        $p->refresh();
        $this->assertTrue($p->hasDraft());
        $this->assertNotSame('Draft [[Heading]]', $p->hero['h1']);
        $this->flush();
        $this->get('/services/local-seo')->assertDontSee('Draft');

        // Reopening the editor shows the draft.
        $this->assertSame('Draft [[Heading]]', Livewire::test(EditPage::class, ['record' => $p->getKey()])->get('data.hero.h1'));

        $p->publish();
        $this->flush();
        $this->get('/services/local-seo')->assertSee('Draft <span', false)->assertDontSee('sp-logos');
        $this->assertSame('Published', $p->revisions()->first()->label);
    }

    public function test_people_without_publish_permission_only_save_drafts(): void
    {
        Role::query()->create(['key' => 'writer', 'name' => 'Writer', 'perms' => ['pages.view', 'pages.edit']]);
        $this->editor('writer');
        $p = Page::find('page~about');
        Livewire::test(EditPage::class, ['record' => $p->getKey()])
            ->assertActionHidden('publishNow')->assertActionHidden('schedule')
            ->set('data.hero.h1', 'Writer [[Edit]]')->call('save')->assertHasNoErrors();
        $this->assertTrue($p->fresh()->hasDraft());
        $this->assertNotSame('Writer [[Edit]]', $p->fresh()->hero['h1']);
    }

    public function test_scheduled_changes_go_live_on_time(): void
    {
        $this->editor();
        $p = Page::find('industry~finance') ?? Page::query()->where('kind', 'industry')->first();
        $p->saveDraft(array_merge($p->only(Page::DRAFTABLE), ['meta_description' => 'Scheduled description']), now()->addHour());
        $this->assertSame(0, Page::publishDue());
        $this->travel(61)->minutes();
        $this->assertSame(1, Page::publishDue());
        $p->refresh();
        $this->assertSame('Scheduled description', $p->meta_description);
        $this->assertNull($p->publish_at);
        $this->assertFalse($p->hasDraft());
        $this->assertSame('Scheduled publish', $p->revisions()->first()->label);
    }

    public function test_preview_shows_unsaved_changes_to_staff_only(): void
    {
        $p = Page::find('service~local-seo');
        $this->editor();
        $row = $p->only(Page::DRAFTABLE);
        $row['hero']['h1'] = 'Previewed [[Heading]]';
        $token = PagePreview::store($p, $row);
        $this->get("/preview/page/$token")->assertOk()->assertSee('/frame');
        $this->get("/preview/page/$token/frame")->assertOk()->assertSee('Previewed')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->assertNotSame('Previewed [[Heading]]', $p->fresh()->hero['h1']);

        auth()->logout();
        $this->get("/preview/page/$token/frame")->assertForbidden();
    }

    public function test_post_versions_can_be_compared_and_restored(): void
    {
        $this->editor();
        $post = Post::query()->firstOrFail();
        $old = $post->title;
        $post->update(['title' => 'A new title']);
        $first = $post->revisions()->get()->last();
        $this->assertContains($first->label, ['Created', 'Earlier version']);
        $this->assertSame($old, $first->data['title']);

        $rows = RevisionDiff::rows('post', $first->data, $post->fresh()->revisionData());
        $this->assertSame('Title', $rows[0]['label']);
        $this->assertStringContainsString('<ins>', $rows[0]['html']);

        Livewire::test(VersionHistory::class, ['model' => 'post', 'key' => (string) $post->id])
            ->assertSee('Version history')->callTableAction('restore', $first);
        $this->assertSame($old, $post->fresh()->title);
        $this->assertStringStartsWith('Restored', $post->revisions()->first()->label);
    }

    public function test_history_needs_the_section_permission(): void
    {
        $this->editor('sales');
        $this->get(VersionHistory::getUrl(['model' => 'page', 'key' => 'page~about']))->assertForbidden();
    }

    public function test_word_diff(): void
    {
        $this->assertSame('Local <del>SEO</del><ins>search</ins> agency', RevisionDiff::words('Local SEO agency', 'Local search agency'));
    }
}
