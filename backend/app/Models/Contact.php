<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One person, however many times they enquire (matched by email address). */
class Contact extends Model
{
    use \App\Models\Concerns\BlankNotNull;

    protected $fillable = ['email', 'name', 'phone', 'business', 'first_seen_at', 'last_seen_at'];

    protected function casts(): array
    {
        return ['first_seen_at' => 'datetime', 'last_seen_at' => 'datetime'];
    }

    public function leads() { return $this->hasMany(Lead::class)->latest(); }

    /** The contact for this lead's email address (created on the first enquiry), kept up to date. */
    public static function forLead(Lead $lead): ?self
    {
        $email = mb_strtolower(trim((string) $lead->email));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) return null;
        $c = static::query()->firstOrNew(['email' => $email]);
        $c->fill(array_filter(['name' => $lead->name, 'phone' => $lead->phone, 'business' => $lead->business]));
        $c->first_seen_at ??= $lead->created_at ?? now();
        $c->last_seen_at = $lead->created_at ?? now();
        $c->save();
        return $c;
    }

    public function subscriber(): ?Subscriber
    {
        return Subscriber::query()->where('email', $this->email)->first();
    }
}
