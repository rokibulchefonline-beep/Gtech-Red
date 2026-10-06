<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceItem extends Model
{
    protected $table = 'service_items';

    protected $fillable = ['slug','group_slug','name','blurb','icon','sort'];

    protected function casts(): array
    {
        return ['sort'=>'integer'];
    }
}
