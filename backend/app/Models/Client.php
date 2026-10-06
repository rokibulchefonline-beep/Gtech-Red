<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = ['legacy_id','name','logo','url','order','visible'];

    protected function casts(): array
    {
        return ['visible'=>'boolean','order'=>'integer'];
    }
}
