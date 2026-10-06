<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $table = 'partners';

    protected $fillable = ['legacy_id','name','logo','url','order','visible'];

    protected function casts(): array
    {
        return ['visible'=>'boolean','order'=>'integer'];
    }
}
