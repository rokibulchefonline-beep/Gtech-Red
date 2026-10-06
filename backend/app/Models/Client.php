<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use \App\Models\Concerns\BlankNotNull;

    protected $table = 'clients';

    protected $fillable = ['legacy_id','name','logo','url','order','visible'];

    protected function casts(): array
    {
        return ['visible'=>'boolean','order'=>'integer'];
    }
}
