<?php
declare(strict_types=1);

namespace App;

use MongoDB\Client;
use MongoDB\Database as MongoDatabase;

final class Database
{
    private static ?Client $client = null;

    public static function client(): Client
    {
        return self::$client ??= new Client(
            $_ENV['MONGODB_URI'] ?? 'mongodb://127.0.0.1:27017',
            [],
            ['typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']]
        );
    }

    public static function db(): MongoDatabase
    {
        return self::client()->selectDatabase($_ENV['MONGODB_DB'] ?? 'gtech_red');
    }
}
