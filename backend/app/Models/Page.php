<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use \App\Models\Concerns\BlankNotNull;

    protected $table = 'pages';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key','kind','slug','name','path','sort','meta_title','meta_description','focus_keyword','hero','sections','faqs','related','data','published'];

    protected function casts(): array
    {
        return ['hero'=>'array','sections'=>'array','faqs'=>'array','related'=>'array','data'=>'array','published'=>'boolean','sort'=>'integer'];
    }
}
