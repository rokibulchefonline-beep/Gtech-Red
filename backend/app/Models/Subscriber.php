<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use \App\Models\Concerns\BlankNotNull;

    protected $table = 'subscribers';

    protected $fillable = ['email','source'];

    protected function casts(): array
    {
        return [];
    }
}
