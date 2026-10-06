<?php

// Router for `php artisan serve`. Same as Laravel's own, except that only real files are served directly:
// image folders such as public/services share their name with pages (/services).
$publicPath = getcwd();
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');

if ($uri !== '/' && is_file($publicPath.$uri)) {
    return false;
}

require_once $publicPath.'/index.php';
