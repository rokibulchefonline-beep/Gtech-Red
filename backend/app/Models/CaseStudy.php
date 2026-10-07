<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseStudy extends Model
{
    use \App\Models\Concerns\BlankNotNull, \App\Models\Concerns\HasRevisions;

    /** Attributes kept in the version history. */
    public const REVISIONED = ['title','slug','client','industry','duration','website','excerpt','image','image_alt','logo','services','metrics','challenge','solution','results','quote','body','status','order','meta_title','meta_description','focus_keyword'];

    protected $table = 'case_studies';

    protected $fillable = ['legacy_id','title','slug','client','industry','duration','website','excerpt','image','image_alt','logo','services','metrics','challenge','solution','results','quote','body','status','order','meta_title','meta_description','focus_keyword'];

    protected function casts(): array
    {
        return ['services'=>'array','metrics'=>'array','results'=>'array','quote'=>'array','order'=>'integer'];
    }

    protected static function booted(): void
    {
        // A changed address keeps working: the old one redirects to the new one.
        static::updated(function (self $m) {
            if ($m->wasChanged('slug') && $m->getOriginal('slug')) Redirect::moved('/case-studies/'.$m->getOriginal('slug'), '/case-studies/'.$m->slug);
        });
    }
}
