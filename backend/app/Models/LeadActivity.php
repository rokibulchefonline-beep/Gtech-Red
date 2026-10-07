<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One entry on a lead's timeline: a note, call, email or meeting someone logged, or a change recorded automatically. */
class LeadActivity extends Model
{
    public const UPDATED_AT = null;

    /** Types a person can log by hand. */
    public const LOGGABLE = ['note' => 'Note', 'call' => 'Call', 'email' => 'Email', 'meeting' => 'Meeting'];

    public const ICONS = [
        'created' => 'heroicon-o-inbox-arrow-down', 'note' => 'heroicon-o-pencil-square', 'call' => 'heroicon-o-phone', 'email' => 'heroicon-o-envelope',
        'meeting' => 'heroicon-o-users', 'status' => 'heroicon-o-flag', 'assigned' => 'heroicon-o-user-plus', 'follow_up' => 'heroicon-o-calendar-days', 'value' => 'heroicon-o-banknotes',
    ];

    protected $fillable = ['lead_id', 'user_id', 'type', 'body'];

    public function lead() { return $this->belongsTo(Lead::class); }
    public function user() { return $this->belongsTo(User::class); }

    public function label(): string
    {
        return self::LOGGABLE[$this->type] ?? ['created' => 'Enquiry received', 'status' => 'Status', 'assigned' => 'Assigned', 'follow_up' => 'Follow-up', 'value' => 'Deal value'][$this->type] ?? ucfirst($this->type);
    }
}
