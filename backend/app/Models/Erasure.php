<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Record that a person's data was erased on request: no personal data, only a hash of the address and counts. */
class Erasure extends Model
{
    public const UPDATED_AT = null;
    protected $fillable = ['email_hash', 'user_id', 'reason', 'counts'];

    protected function casts(): array
    {
        return ['counts' => 'array'];
    }

    public function user() { return $this->belongsTo(User::class); }

    public static function hash(string $email): string
    {
        return hash('sha256', mb_strtolower(trim($email)));
    }
}
