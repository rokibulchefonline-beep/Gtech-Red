<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = ['legacy_id','name','slug'];

    protected function casts(): array
    {
        return [];
    }
}
