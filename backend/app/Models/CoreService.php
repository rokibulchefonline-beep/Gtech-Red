<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreService extends Model
{
    protected $table = 'core_services';

    protected $fillable = ['slug','title','line','points','image','sort'];

    protected function casts(): array
    {
        return ['points'=>'array','sort'=>'integer'];
    }
}
