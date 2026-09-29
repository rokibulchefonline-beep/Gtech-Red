<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Database;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

header('Content-Type: application/json');

function respond(mixed $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

try {
    if ($path === 'api/health') {
        Database::db()->command(['ping' => 1]);
        respond(['status' => 'ok', 'mongodb' => 'connected']);
    }

    // Example resource: /api/items and /api/items/{id}
    if (preg_match('#^api/items(?:/([a-f0-9]{24}))?$#', $path, $m)) {
        $items = Database::db()->selectCollection('items');
        $id = isset($m[1]) ? new ObjectId($m[1]) : null;
        $body = json_decode(file_get_contents('php://input') ?: '[]', true) ?? [];

        switch ([$method, $id !== null]) {
            case ['GET', false]:
                respond(array_map(fn($d) => ['id' => (string) $d['_id']] + $d, $items->find()->toArray()));
            case ['GET', true]:
                $doc = $items->findOne(['_id' => $id]) ?? respond(['error' => 'Not found'], 404);
                respond(['id' => (string) $doc['_id']] + $doc);
            case ['POST', false]:
                $body['created_at'] = new UTCDateTime();
                $res = $items->insertOne($body);
                respond(['id' => (string) $res->getInsertedId()], 201);
            case ['PUT', true]:
                $items->updateOne(['_id' => $id], ['$set' => $body]);
                respond(['updated' => true]);
            case ['DELETE', true]:
                $items->deleteOne(['_id' => $id]);
                respond(['deleted' => true]);
        }
    }

    respond(['error' => 'Not found'], 404);
} catch (Throwable $e) {
    respond(['error' => 'Server error', 'detail' => $e->getMessage()], 500);
}
