<?php
declare(strict_types=1);

use App\Database;
use App\Site;
use MongoDB\BSON\UTCDateTime;

require __DIR__ . '/helpers.php';

$site = Site::data();
$method = $_SERVER['REQUEST_METHOD'];
$parts = $path === '' ? [] : explode('/', $path);
$s = fn(int $k) => $parts[$k] ?? null;

switch ($parts[0] ?? '') {
    case '':
        Site::render('home', ['title' => $site['name'] . ' | ' . $site['tagline']]);

    case 'about':
        Site::render('about', ['title' => 'About Us | ' . $site['name']]);

    case 'services':
        $group = $site['services'][$s(1)] ?? null;
        if (!$group) break;
        if (!$s(2)) {
            Site::render('service-group', ['group' => $group, 'gslug' => $s(1), 'title' => $group['title'] . ' | ' . $site['name']]);
        }
        foreach ($group['items'] as $name => $blurb) {
            if (Site::slug($name) === $s(2)) {
                Site::render('service-item', [
                    'group' => $group, 'gslug' => $s(1), 'name' => $name, 'blurb' => $blurb,
                    'title' => $name . ' | ' . $site['name'],
                ]);
            }
        }
        break;

    case 'industries':
        foreach ($site['industries'] as $name) {
            if (Site::slug($name) === $s(1)) {
                Site::render('industry', ['name' => $name, 'title' => $name . ' Marketing & Software | ' . $site['name']]);
            }
        }
        break;

    case 'case-studies':
        if ($s(1)) {
            $doc = Site::docs('case_studies', ['slug' => $s(1)], 1)[0] ?? null;
            if (!$doc) break;
            Site::render('post', ['doc' => $doc, 'back' => '/case-studies', 'label' => 'Case Studies', 'title' => $doc['title'] . ' | ' . $site['name']]);
        }
        Site::render('listing', [
            'heading' => 'Case Studies', 'base' => '/case-studies',
            'docs' => Site::docs('case_studies'), 'title' => 'Case Studies | ' . $site['name'],
        ]);

    case 'blog':
        if ($s(1)) {
            $doc = Site::docs('posts', ['slug' => $s(1)], 1)[0] ?? null;
            if (!$doc) break;
            Site::render('post', ['doc' => $doc, 'back' => '/blog', 'label' => 'Blog', 'title' => $doc['title'] . ' | ' . $site['name']]);
        }
        Site::render('listing', [
            'heading' => 'Blog', 'base' => '/blog',
            'docs' => Site::docs('posts'), 'title' => 'Blog | ' . $site['name'],
        ]);

    case 'contact':
        $msg = null;
        $old = [];
        if ($method === 'POST') {
            $old = array_map(fn($v) => trim((string) $v), $_POST);
            if (!empty($old['website'])) { // honeypot
                $msg = ['ok', 'Thanks. We will reply soon.'];
            } elseif (empty($old['name']) || !filter_var($old['email'] ?? '', FILTER_VALIDATE_EMAIL) || empty($old['message'])) {
                $msg = ['err', 'Enter name, valid email and message.'];
            } else {
                try {
                    Database::db()->selectCollection('leads')->insertOne([
                        'name' => $old['name'], 'email' => $old['email'],
                        'phone' => $old['phone'] ?? '', 'service' => $old['service'] ?? '',
                        'message' => $old['message'], 'created_at' => new UTCDateTime(),
                    ]);
                    $msg = ['ok', 'Thanks. We will reply soon.'];
                    $old = [];
                } catch (Throwable) {
                    $msg = ['err', 'Could not save message. Try again later.'];
                }
            }
        }
        Site::render('contact', ['msg' => $msg, 'old' => $old, 'title' => 'Contact Us | ' . $site['name']]);

    case 'privacy-policy':
    case 'terms':
        Site::render('legal', ['page' => $parts[0], 'title' => ucwords(str_replace('-', ' ', $parts[0])) . ' | ' . $site['name']]);
}

Site::render('404', ['title' => 'Page not found | ' . $site['name']], 404);
