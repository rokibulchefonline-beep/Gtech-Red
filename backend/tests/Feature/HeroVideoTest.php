<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\PageResource\Pages\EditPage;
use App\Models\Page;
use App\Models\User;
use App\Support\Site\HeroVideo;
use App\Support\Site\PageCache;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class HeroVideoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Artisan::call('gtech:seed-content');
        $this->flush();
        $this->actingAs(User::factory()->create(['role' => 'super_admin', 'active' => true]));
    }

    private function flush(): void
    {
        PageCache::flush();
        \App\Support\Site\Repo::flush();
        \App\View\SiteComposer::flush();
    }

    private function save(array $hero): void
    {
        $c = Livewire::test(EditPage::class, ['record' => 'page~home']);
        foreach ($hero as $k => $v) $c->set("data.hero.$k", $v);
        $c->call('save')->assertHasNoErrors();
        Page::find('page~home')->publish();
        $this->flush();
    }

    public function test_the_original_youtube_video_and_the_numbers_are_in_the_hero_by_default(): void
    {
        $this->get('/')->assertOk()->assertSee('data-yt="'.HeroVideo::DEFAULT_YOUTUBE.'"', false)->assertSee('stats-hero', false);
    }

    public function test_a_youtube_link_or_a_video_file_can_be_set_in_the_admin(): void
    {
        $this->save(['video_type' => 'youtube', 'video_youtube' => 'https://youtu.be/dQw4w9WgXcQ?t=3']);
        $this->get('/')->assertSee('data-yt="dQw4w9WgXcQ"', false);

        $this->save(['video_type' => 'file', 'video_url' => '/storage/media/hero.mp4', 'video_poster' => '/storage/media/hero.webp', 'video_mobile' => true]);
        $this->get('/')->assertSee('data-src="/storage/media/hero.mp4"', false)->assertSee('poster="/storage/media/hero.webp"', false)
            ->assertSee('data-mobile="1"', false)->assertDontSee('data-yt=', false);
    }

    public function test_bad_links_are_refused(): void
    {
        $c = Livewire::test(EditPage::class, ['record' => 'page~home'])->set('data.hero.video_type', 'youtube')->set('data.hero.video_youtube', 'https://vimeo.com/123')->call('save');
        $c->assertHasErrors();
        $this->assertSame('', HeroVideo::safeUrl("https://x.com/a')"));
        $this->assertSame('', HeroVideo::safeUrl('javascript:alert(1)'));
    }
}
