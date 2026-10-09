<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\Tools\ImageConverter;
use App\Filament\Admin\Pages\Tools\ImageResizer;
use App\Filament\Admin\Resources\LeadResource\Pages\ListLead;
use App\Filament\Support\ImageField;
use App\Models\Email;
use App\Models\Lead;
use App\Models\Post;
use App\Models\SchemaRule;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\User;
use App\Support\ImageTools;
use App\Support\Site\Blog;
use App\Support\SiteMailer;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class AdminToolsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Artisan::call('gtech:seed-content');
        \App\Support\Site\Repo::flush();
        \App\View\SiteComposer::flush();
        Storage::fake('public');
        Storage::fake('tmp-for-tests');
    }

    private function admin(string $role = 'super_admin'): User
    {
        $u = User::factory()->create(['role' => $role, 'active' => true]);
        $this->actingAs($u);
        return $u;
    }

    /** A noisy photo, so it does not compress to nothing. */
    private function photo(int $w = 2400, int $h = 1600, string $format = 'jpg'): string
    {
        $img = imagecreatetruecolor($w, $h);
        mt_srand(7);
        for ($y = 0; $y < $h; $y += 4) for ($x = 0; $x < $w; $x += 4) imagefilledrectangle($img, $x, $y, $x + 3, $y + 3, mt_rand(0, 0xFFFFFF));
        $path = tempnam(sys_get_temp_dir(), 'img').'.'.$format;
        $format === 'png' ? imagepng($img, $path) : imagejpeg($img, $path, 95);
        return $path;
    }

    private function upload(string $path, string $name): TemporaryUploadedFile
    {
        $file = UploadedFile::fake()->createWithContent($name, (string) file_get_contents($path));
        $stored = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded($file);
        Storage::disk('tmp-for-tests')->put('livewire-tmp/'.$stored, (string) file_get_contents($path));
        return TemporaryUploadedFile::createFromLivewire($stored);
    }

    public function test_login_password_has_a_lock_icon_and_show_button(): void
    {
        $html = $this->get('/admin/login')->assertOk()->getContent();
        $this->assertStringContainsString('isPasswordRevealed', $html);
        $this->assertGreaterThanOrEqual(2, substr_count($html, 'fi-input-wrp-prefix'));
    }

    public function test_images_are_compressed_under_250_kb_and_cropped_square(): void
    {
        $src = $this->photo();
        $this->assertGreaterThan(ImageTools::MAX_BYTES, filesize($src));
        $bytes = ImageTools::compress(ImageTools::load($src));
        $this->assertLessThanOrEqual(ImageTools::MAX_BYTES, strlen($bytes));
        $this->assertSame('image/webp', getimagesizefromstring($bytes)['mime']);

        $sq = ImageTools::squareFile($this->photo(800, 500));
        $this->assertSame([300, 300], array_slice(getimagesize($sq), 0, 2));
        $this->assertSame('My team photo 2024', ImageTools::altFromName('my-team_photo-2024.JPG'));
        $this->assertSame('', ImageTools::altFromName('IMG_2024.jpg'));
    }

    public function test_blog_uploads_over_250_kb_are_refused_unless_optimised(): void
    {
        $this->admin();
        $big = $this->photo();
        $this->assertNull(ImageField::store($this->upload($big, 'big.jpg'), 'blog'));
        Storage::disk('tmp-for-tests')->deleteDirectory('livewire-tmp');
        $m = ImageField::store($this->upload($big, 'office-team.jpg'), 'optimise');
        $this->assertSame('image/webp', $m->type);
        $this->assertLessThanOrEqual(ImageTools::MAX_BYTES, $m->size);
        $this->assertSame('Office team', $m->alt);
        Storage::disk('tmp-for-tests')->deleteDirectory('livewire-tmp');
        $a = ImageField::store($this->upload($this->photo(900, 600), 'jane.jpg'), 'avatar');
        $this->assertSame([300, 300], array_slice(getimagesize(Storage::disk('public')->path($a->path)), 0, 2));
    }

    public function test_image_captions_show_under_blog_images(): void
    {
        $this->assertSame('<figure><img src="/a.webp" alt="A"><figcaption>Our team</figcaption></figure>', Blog::captions('<p><img src="/a.webp" alt="A" title="Our team"></p>'));
        Post::create(['title' => 'Captioned', 'slug' => 'captioned', 'status' => 'published', 'body' => '<p>Hello <b>there</b>.</p><p><img src="/x.webp" alt="X" title="A chart of results"></p>',
            'image' => '/posts/default.webp', 'image_alt' => 'Cover', 'image_caption' => 'Photo by our team']);
        $this->get('/blogs/captioned')->assertOk()
            ->assertSee('<figcaption class="bp-caption">Photo by our team</figcaption>', false)
            ->assertSee('<figcaption>A chart of results</figcaption>', false);
    }

    public function test_image_resizer_and_converter(): void
    {
        $this->admin();
        $src = $this->photo(1600, 1000, 'png');
        Livewire::test(ImageResizer::class)
            ->set('data.files', [UploadedFile::fake()->createWithContent('banner.png', (string) file_get_contents($src))])
            ->set('data.width', 800)->set('data.format', 'jpg')
            ->call('run')->assertHasNoErrors()
            ->assertSet('results.0.w', 800)->assertSet('results.0.h', 500)->assertSee('banner.jpg')
            ->call('save', 0)->assertSet('results.0.saved', true);
        $this->assertDatabaseHas('media', ['name' => 'banner.jpg']);

        Livewire::test(ImageConverter::class)
            ->set('data.files', [UploadedFile::fake()->createWithContent('logo.png', (string) file_get_contents($src))])
            ->set('data.format', 'webp')->call('run')->assertHasNoErrors()->assertSee('logo.webp');
        $this->get('/admin/image-resizer')->assertOk();
        $this->get('/admin/image-converter')->assertOk();
    }

    public function test_additional_schema_rules_apply_by_page_type(): void
    {
        Testimonial::query()->create(['name' => 'Sam Patel', 'title' => 'Great', 'text' => 'Brilliant team.', 'visible' => true, 'sort' => 1]);
        SchemaRule::query()->create(['name' => 'Posts', 'scope' => 'posts', 'json' => json_encode(['@context' => 'https://schema.org', '@type' => 'HowTo', 'name' => '{name}', 'url' => '{url}'])]);
        SchemaRule::query()->create(['name' => 'Reviews', 'scope' => 'all', 'json' => json_encode(['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => 'GTech Digital', 'review' => '{testimonials}'])]);
        Post::create(['title' => 'Rule test', 'slug' => 'rule-test', 'status' => 'published', 'body' => '<p>x</p>']);
        \App\Support\Site\PageCache::flush();

        $post = $this->get('/blogs/rule-test')->assertOk()->getContent();
        $this->assertStringContainsString('"@type":"HowTo"', $post);
        $this->assertStringContainsString('/blogs/rule-test', $post);
        $about = $this->get('/about')->assertOk()->getContent();
        $this->assertStringNotContainsString('"@type":"HowTo"', $about);
        $this->assertStringContainsString('"reviewBody":"Brilliant team."', $about);

        $this->admin();
        $this->get('/admin/schema-rules')->assertOk()->assertSee('Reviews');
    }

    public function test_lead_follow_up_history_summary_history_filters_and_csv(): void
    {
        $this->admin();
        $a = Lead::query()->create(['name' => 'Amy Lee', 'business' => 'Lee Dental', 'email' => 'amy@example.com', 'phone' => '07700 900123', 'service' => 'SEO', 'form_path' => '/services/local-seo']);
        $b = Lead::query()->create(['name' => 'Bob Ray', 'business' => 'Ray Ltd', 'email' => 'bob@example.com', 'phone' => '020 7946 0000', 'service' => 'Web design', 'form_path' => '/contact']);
        $a->log('call', 'Called, left a message');
        $a->log('email', 'Sent the proposal');
        $a->update(['notes' => 'First summary']);
        $a->update(['notes' => 'Second summary']);
        $this->assertSame(['Second summary', 'First summary'], $a->activities()->where('type', 'summary')->pluck('body')->all());

        $this->get('/admin/leads/'.$a->id)->assertOk()->assertSee('Follow-up history')->assertSee('2 times')->assertSee('Follow-up #2')->assertSee('Summary history');

        Livewire::test(ListLead::class)
            ->filterTable('person', ['phone' => '07700900123'])->assertCanSeeTableRecords([$a])->assertCanNotSeeTableRecords([$b])
            ->resetTableFilters()
            ->filterTable('form_path', '/contact')->assertCanSeeTableRecords([$b])->assertCanNotSeeTableRecords([$a])
            ->resetTableFilters()
            ->filterTable('followed', '0')->assertCanSeeTableRecords([$b])->assertCanNotSeeTableRecords([$a]);

        $csv = Livewire::test(ListLead::class)->filterTable('person', ['email' => 'amy@'])->callAction('export', ['which' => 'filtered']);
        $csv->assertFileDownloaded('leads-'.now()->format('Y-m-d').'.csv');
        $rows = \App\Filament\Admin\Resources\LeadResource::csv(Lead::query()->withCount('followUps')->whereKey($a->id)->get(), 'x');
        ob_start(); $rows->sendContent(); $out = ob_get_clean();
        $this->assertStringContainsString('Followed up (times)', $out);
        $this->assertMatchesRegularExpression('/Amy Lee.*,2,/', $out);
    }

    public function test_every_email_the_site_sends_is_in_the_sent_folder(): void
    {
        $lead = Lead::query()->create(['name' => 'Amy Lee', 'business' => 'Lee Dental', 'email' => 'amy@example.com', 'phone' => '07700', 'service' => 'SEO']);
        SiteMailer::send('amy@example.com', 'Hello Amy', '<p>Thanks for your enquiry.</p>');
        $e = Email::query()->where('subject', 'Hello Amy')->firstOrFail();
        $this->assertSame('sent', $e->folder);
        $this->assertSame($lead->id, $e->lead_id);
        $this->assertStringContainsString('amy@example.com', $e->to);
    }

    public function test_google_recaptcha_protects_the_forms(): void
    {
        Setting::put('forms', ['recaptchaSite' => str_repeat('a', 40), 'recaptchaSecret' => Crypt::encryptString('secret'), 'recaptchaVersion' => 'v3', 'recaptchaScore' => 0.5] + Setting::group('forms'));
        \App\View\SiteComposer::flush();
        $this->get('/contact')->assertSee('www.google.com/recaptcha/api.js?render='.str_repeat('a', 40), false);
        $form = ['name' => 'Jo', 'business' => 'Co', 'email' => 'jo@example.com', 'phone' => '0700', 'service' => 'SEO', 'budget' => '£500'];
        $this->postJson('/api/contact', $form)->assertStatus(400);
        Http::fake(['www.google.com/*' => Http::sequence()->push(['success' => true, 'score' => 0.2])->push(['success' => true, 'score' => 0.9])]);
        $this->postJson('/api/contact', $form + ['g-recaptcha-response' => 'tok'])->assertStatus(400);
        $this->postJson('/api/contact', $form + ['g-recaptcha-response' => 'tok2'])->assertOk()->assertJson(['ok' => true]);
    }
}
