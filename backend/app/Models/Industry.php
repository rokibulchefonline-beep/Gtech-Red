<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    protected $table = 'industries';

    protected $fillable = ['slug','name','icon','sort'];

    protected function casts(): array
    {
        return ['sort'=>'integer'];
    }
}
