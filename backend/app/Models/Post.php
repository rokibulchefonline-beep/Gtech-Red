<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use \App\Models\Concerns\BlankNotNull;

    protected $table = 'posts';

    protected $fillable = ['created_by', 'legacy_id','title','slug','excerpt','body','format','category','categories','tags','post_format','visibility','allow_comments','allow_pingbacks','custom_fields','image','image_alt','author','featured','status','date','meta_title','meta_description','focus_keyword','canonical','noindex'];

    protected function casts(): array
    {
        return ['categories'=>'array','tags'=>'array','custom_fields'=>'array','allow_comments'=>'boolean','allow_pingbacks'=>'boolean','featured'=>'boolean','noindex'=>'boolean','date'=>'datetime'];
    }

    protected static function booted(): void
    {
        // A changed address keeps working: the old one redirects to the new one.
        static::updated(function (self $m) {
            if ($m->wasChanged('slug') && $m->getOriginal('slug')) Redirect::moved('/blogs/'.$m->getOriginal('slug'), '/blogs/'.$m->slug);
        });
    }
}
