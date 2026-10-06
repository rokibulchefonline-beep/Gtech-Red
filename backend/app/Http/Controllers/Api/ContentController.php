<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\LegacyDocs;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    /** POST /api/v1/query  {coll, filter, sort, limit, skip, count} -> {ok, rows} or {ok, total} */
    public function query(Request $r)
    {
        $data = $r->validate([
            'coll' => 'required|string|max:40', 'filter' => 'array', 'sort' => 'array',
            'limit' => 'integer|min:0|max:500', 'skip' => 'integer|min:0', 'count' => 'boolean',
        ]);
        try {
            $res = LegacyDocs::query($data['coll'], $data['filter'] ?? [], $data['sort'] ?? [], (int) ($data['limit'] ?? 100), (int) ($data['skip'] ?? 0), (bool) ($data['count'] ?? false));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        }
        return response()->json(['ok' => true] + $res);
    }
}
