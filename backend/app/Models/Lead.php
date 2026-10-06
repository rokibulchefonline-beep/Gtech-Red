<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use \App\Models\Concerns\BlankNotNull;

    protected $table = 'leads';

    protected $fillable = ['legacy_id','name','business','email','phone','service','budget','designation','company_size','website','postcode','message','source','status','notes','assignee','value'];

    protected function casts(): array
    {
        return ['value'=>'decimal:2'];
    }

    /** Phone number for tel: and WhatsApp links: digits only, UK numbers starting with 0 written with +44. */
    public function phoneDigits(): string
    {
        $d = preg_replace('/\D+/', '', (string) $this->phone);
        if (str_starts_with((string) $this->phone, '+')) return $d;
        return str_starts_with($d, '00') ? substr($d, 2) : (str_starts_with($d, '0') ? '44'.substr($d, 1) : $d);
    }
}
