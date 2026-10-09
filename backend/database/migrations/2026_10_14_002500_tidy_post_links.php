<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Removes the empty link attributes (id="" and others) the editor added, which showed a "#" before every link.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('posts')->where('body', 'like', '%<a %')->orderBy('id')->each(function ($row) {
            $clean = Post::tidyLinks($row->body);
            if ($clean !== $row->body) DB::table('posts')->where('id', $row->id)->update(['body' => $clean]);
        });
    }

    public function down(): void {}
};
