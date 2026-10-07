<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One saved version of a page, blog post or case study (Version history on its edit screen). */
class Revision extends Model
{
    public const UPDATED_AT = null;
    public const KEEP = 60; // versions kept per item

    public const MODELS = ['page' => Page::class, 'post' => Post::class, 'case_study' => CaseStudy::class];

    protected $fillable = ['model', 'model_key', 'user_id', 'label', 'data'];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    public function user() { return $this->belongsTo(User::class); }

    /** The page, post or case study this version belongs to. */
    public function subject(): ?Model
    {
        $class = self::MODELS[$this->model] ?? null;
        return $class ? $class::query()->find($this->model_key) : null;
    }

    public function previous(): ?self
    {
        return self::query()->where('model', $this->model)->where('model_key', $this->model_key)->where('id', '<', $this->id)->latest('id')->first();
    }
}
