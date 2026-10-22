<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Support\Site\Content\GlobalContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class GlobalContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_fresh_site_gets_the_uk_and_worldwide_content(): void
    {
        Artisan::call('gtech:seed-content');
        config(['gtech.blade_live' => true]);

        $this->assertStringContainsString('worldwide', Page::find('page~home')->hero['lead']);
        foreach (Page::query()->whereIn('kind', ['service', 'industry'])->get() as $p) {
            $this->assertMatchesRegularExpression('/worldwide|internationally|outside the UK/i', json_encode([$p->hero, $p->sections, $p->faqs]), $p->key);
            $this->assertDoesNotMatchRegularExpression('/\bUK\b/', (string) ($p->hero['h1'] ?? ''), $p->key);
        }
        $this->get('http://www.gtechdigital.co.uk/services/search-engine-optimization')->assertOk()->assertSee('worldwide');
        $this->assertSame([0, 0], GlobalContent::apply());
    }

    public function test_text_edited_in_the_admin_is_kept(): void
    {
        $p = Page::query()->create(['key' => 'service~demo', 'kind' => 'service', 'slug' => 'demo', 'name' => 'Demo', 'path' => '/services/demo', 'published' => true,
            'meta_title' => 'Old title', 'meta_description' => 'Old description', 'hero' => [],
            'sections' => [['type' => 'text', 'id' => 'a', 'heading' => 'Old A'], ['type' => 'text', 'id' => 'b', 'heading' => 'Old B']]]);
        $c = fn ($old, $new) => ['old' => GlobalContent::fp($old), 'new' => $new];
        $file = tempnam(sys_get_temp_dir(), 'gc');
        file_put_contents($file, json_encode(['pages' => ['service~demo' => [
            'meta_title' => $c('Old title', 'New title'), 'meta_description' => $c('Old description', 'New description'),
            'sections' => ['a' => $c(['type' => 'text', 'id' => 'a', 'heading' => 'Old A'], ['type' => 'text', 'id' => 'a', 'heading' => 'New A']),
                'b' => $c(['type' => 'text', 'id' => 'b', 'heading' => 'Old B'], ['type' => 'text', 'id' => 'b', 'heading' => 'New B'])],
        ]]]));
        // Edited in the admin before the update arrives.
        $p->forceFill(['meta_description' => 'My description', 'sections' => [['type' => 'text', 'id' => 'a', 'heading' => 'Old A'], ['type' => 'text', 'id' => 'b', 'heading' => 'My B']]])->save();

        $this->assertSame([2, 2], GlobalContent::apply($file));
        $p->refresh();
        $this->assertSame(['New title', 'My description', 'New A', 'My B'], [$p->meta_title, $p->meta_description, $p->sections[0]['heading'], $p->sections[1]['heading']]);
        unlink($file);
    }
}
