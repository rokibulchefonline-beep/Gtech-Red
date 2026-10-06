<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseStudy extends Model
{
    use \App\Models\Concerns\BlankNotNull;

    protected $table = 'case_studies';

    protected $fillable = ['legacy_id','title','slug','client','industry','duration','website','excerpt','image','image_alt','logo','services','metrics','challenge','solution','results','quote','body','status','order','meta_title','meta_description','focus_keyword'];

    protected function casts(): array
    {
        return ['services'=>'array','metrics'=>'array','results'=>'array','quote'=>'array','order'=>'integer'];
    }
}
