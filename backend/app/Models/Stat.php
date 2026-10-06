<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    protected $table = 'stats';

    protected $fillable = ['value','suffix','label','decimals','sort'];

    protected function casts(): array
    {
        return ['value'=>'float','decimals'=>'integer','sort'=>'integer'];
    }
}
