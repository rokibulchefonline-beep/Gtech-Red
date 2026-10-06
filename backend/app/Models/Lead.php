<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $table = 'leads';

    protected $fillable = ['legacy_id','name','business','email','phone','service','budget','designation','company_size','website','postcode','message','source','status','notes','assignee','value'];

    protected function casts(): array
    {
        return ['value'=>'decimal:2'];
    }
}
