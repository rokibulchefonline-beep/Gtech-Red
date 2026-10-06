<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use \App\Models\Concerns\BlankNotNull;

    protected $table = 'categories';

    protected $fillable = ['legacy_id','name','slug'];

    protected function casts(): array
    {
        return [];
    }
}
