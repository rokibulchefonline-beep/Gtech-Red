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
        $key = $s(1);
        if (!$key) break;
        if ($s(2)) { // old /services/{group}/{item} -> /services/{item}
            header('Location: /services/' . $s(2), true, 301);
            exit;
        }
        if (isset($site['services'][$key])) {
            $group = $site['services'][$key];
            Site::render('service-group', ['group' => $group, 'gslug' => $key, 'title' => $group['title'] . ' | ' . $site['name']]);
        }
        foreach ($site['services'] as $gslug => $group) {
            foreach ($group['items'] as $name => $blurb) {
                if (Site::slug($name) === $key) {
                    Site::render('service-item', [
                        'group' => $group, 'gslug' => $gslug, 'name' => $name, 'blurb' => $blurb,
                        'title' => $name . ' | ' . $site['name'],
                    ]);
                }
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

    case 'blog': // old URL
        header('Location: /blogs' . (isset($parts[1]) ? '/' . $parts[1] : ''), true, 301);
        exit;

    case 'blogs':
        if ($s(1)) {
            $doc = Site::docs('posts', ['slug' => $s(1)], 1)[0] ?? null;
            if (!$doc) break;
            Site::render('post', ['doc' => $doc, 'back' => '/blogs', 'label' => 'Blog', 'title' => $doc['title'] . ' | ' . $site['name']]);
        }
        Site::render('listing', [
            'heading' => 'Blog', 'base' => '/blogs',
            'docs' => Site::docs('posts'), 'title' => 'Blog | ' . $site['name'],
        ]);

    case 'contact':
    case 'quote':
        $msg = null;
        $old = [];
        if ($method === 'POST') {
            $old = array_map(fn($v) => trim((string) $v), $_POST);
            $required = ['name', 'business', 'email', 'phone', 'service', 'budget'];
            $missing = array_filter($required, fn($k) => ($old[$k] ?? '') === '');
            if (!empty($old['website'])) { // honeypot
                $msg = ['ok', 'Thanks. We will send your proposal within 24 hours.'];
            } elseif ($missing || !filter_var($old['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
                $msg = ['err', 'Fill all required fields with a valid email.'];
            } else {
                try {
                    Database::db()->selectCollection('leads')->insertOne([
                        'name' => $old['name'], 'business' => $old['business'], 'email' => $old['email'],
                        'phone' => $old['phone'], 'service' => $old['service'], 'budget' => $old['budget'],
                        'message' => $old['message'] ?? '', 'created_at' => new UTCDateTime(),
                    ]);
                    $msg = ['ok', 'Thanks. We will send your proposal within 24 hours.'];
                    $old = [];
                } catch (Throwable) {
                    $msg = ['err', 'Could not save request. Try again later.'];
                }
            }
        }
        Site::render('contact', ['msg' => $msg, 'old' => $old, 'title' => 'Request Your Growth Proposal | ' . $site['name']]);

    case 'privacy-policy':
    case 'terms':
        Site::render('legal', ['page' => $parts[0], 'title' => ucwords(str_replace('-', ' ', $parts[0])) . ' | ' . $site['name']]);
}

Site::render('404', ['title' => 'Page not found | ' . $site['name']], 404);
