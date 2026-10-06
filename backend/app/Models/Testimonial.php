<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use \App\Models\Concerns\BlankNotNull;

    protected $table = 'testimonials';

    protected $fillable = ['title','name','text','visible','sort'];

    protected function casts(): array
    {
        return ['visible'=>'boolean','sort'=>'integer'];
    }
}
