<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use \App\Models\Concerns\BlankNotNull;

    protected $table = 'media';

    protected $fillable = ['legacy_id','name','type','size','path','uploaded_by'];

    protected function casts(): array
    {
        return ['size'=>'integer'];
    }

    /** Public URL. Old MongoDB ids keep their /api/media/<id> address. */
    public function getUrlAttribute(): string
    {
        return url('/api/media/'.($this->legacy_id ?: $this->id));
    }
}
