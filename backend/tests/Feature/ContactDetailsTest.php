<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Support\Site\Contact;
use App\Support\Site\PageCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ContactDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_uk_numbers_become_international_tel_links(): void
    {
        $this->assertSame('+443303801000', Contact::tel('0330 380 1000'));
        $this->assertSame('+442035985956', Contact::tel('0203 598 5956'));
        $this->assertSame('+442035985956', Contact::tel('+44 20 3598 5956'));
    }

    public function test_contact_page_shows_both_phones_the_email_and_the_business_profile_address(): void
    {
        Artisan::call('gtech:seed-content');
        Setting::put('contact', array_merge(Setting::group('contact'), ['street' => '1 King Street', 'city' => 'London', 'postcode' => 'EC2V 8AU', 'mapsUrl' => 'https://maps.app.goo.gl/abc']));
        PageCache::flush();
        $html = $this->get('/contact')->assertOk()->getContent();
        foreach (['href="tel:+443303801000"', 'href="tel:+442035985956"', 'info@gtechdigital.co.uk', '1 King Street<br>London<br>EC2V 8AU', 'href="https://maps.app.goo.gl/abc"', '"postalCode":"EC2V 8AU"', '"hasMap":"https://maps.app.goo.gl/abc"'] as $s) {
            $this->assertStringContainsString($s, $html);
        }
        // Hidden from the page when switched off; the footer only shows it when asked.
        $this->assertStringNotContainsString('EC2V 8AU</span>', $html);
        Setting::put('contact', array_merge(Setting::group('contact'), ['showAddress' => false]));
        PageCache::flush();
        $this->assertStringNotContainsString('1 King Street<br>', $this->get('/contact')->getContent());
    }
}
