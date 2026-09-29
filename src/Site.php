<?php
declare(strict_types=1);

namespace App;

final class Site
{
    private static ?array $data = null;

    public static function data(): array
    {
        return self::$data ??= require __DIR__ . '/data.php';
    }

    public static function slug(string $s): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($s)), '-');
    }

    public static function e(?string $s): string
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }

    public static function render(string $tpl, array $vars = [], int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        extract($vars);
        $site = self::data();
        ob_start();
        require __DIR__ . "/../templates/$tpl.php";
        $content = ob_get_clean();
        $title ??= $site['name'];
        require __DIR__ . '/../templates/layout.php';
        exit;
    }

    /** Fetch documents from MongoDB; empty list if DB unavailable. */
    public static function docs(string $collection, array $filter = [], int $limit = 50): array
    {
        try {
            return Database::db()->selectCollection($collection)
                ->find($filter, ['sort' => ['created_at' => -1], 'limit' => $limit])->toArray();
        } catch (\Throwable) {
            return [];
        }
    }
}
