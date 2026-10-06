<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoKeyword extends Model
{
    use \App\Models\Concerns\BlankNotNull;

    protected $table = 'seo_keywords';
    protected $primaryKey = 'slug';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['slug','kw','sec','ent','links'];

    protected function casts(): array
    {
        return ['sec'=>'array','ent'=>'array','links'=>'array'];
    }
}
