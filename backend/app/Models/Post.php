<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $fillable = ['legacy_id','title','slug','excerpt','body','format','category','categories','tags','post_format','visibility','allow_comments','allow_pingbacks','custom_fields','image','image_alt','author','featured','status','date','meta_title','meta_description','focus_keyword','canonical','noindex'];

    protected function casts(): array
    {
        return ['categories'=>'array','tags'=>'array','custom_fields'=>'array','allow_comments'=>'boolean','allow_pingbacks'=>'boolean','featured'=>'boolean','noindex'=>'boolean','date'=>'datetime'];
    }
}
