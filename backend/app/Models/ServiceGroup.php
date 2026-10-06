<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceGroup extends Model
{
    protected $table = 'service_groups';

    protected $fillable = ['slug','title','intro','icon','sort'];

    protected function casts(): array
    {
        return ['sort'=>'integer'];
    }

    public function items()
    {
        return $this->hasMany(ServiceItem::class, 'group_slug', 'slug')->orderBy('sort');
    }
}
