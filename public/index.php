<?php
declare(strict_types=1);

// php -S router: let the built-in server serve real files (assets).
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file) && $file !== __FILE__) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
require __DIR__ . '/../src/' . (str_starts_with($path, 'api/') || $path === 'api' ? 'api.php' : 'pages.php');
