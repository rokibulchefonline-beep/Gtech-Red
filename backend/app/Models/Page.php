<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use \App\Models\Concerns\BlankNotNull, \App\Models\Concerns\HasRevisions;

    /** Attributes kept in the version history. */
    public const REVISIONED = ['name','path','meta_title','meta_description','focus_keyword','hero','sections','faqs','related','data','published'];

    protected $table = 'pages';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    /** Page types. Service, industry and landing pages are built from sections; the others keep a fixed layout. */
    public const KINDS = ['page' => 'Main page', 'service' => 'Service', 'industry' => 'Industry', 'landing' => 'Landing page', 'legal' => 'Legal'];
    public const BUILDER_KINDS = ['service', 'industry', 'landing'];

    /** Fields a draft can hold (everything an editor changes). */
    public const DRAFTABLE = ['name', 'meta_title', 'meta_description', 'focus_keyword', 'hero', 'sections', 'faqs', 'related', 'data'];

    protected $fillable = ['key','kind','slug','name','path','sort','meta_title','meta_description','focus_keyword','hero','sections','faqs','related','data','published','draft','draft_by','draft_at','publish_at'];

    protected function casts(): array
    {
        return ['hero'=>'array','sections'=>'array','faqs'=>'array','related'=>'array','data'=>'array','published'=>'boolean','sort'=>'integer',
            'draft'=>'array','draft_at'=>'datetime','publish_at'=>'datetime'];
    }

    public function draftAuthor() { return $this->belongsTo(User::class, 'draft_by'); }

    public function usesBuilder(): bool
    {
        return in_array($this->kind, self::BUILDER_KINDS, true);
    }

    public function hasDraft(): bool
    {
        return ! empty($this->draft);
    }

    /** A copy of the page showing its draft (for the editor and the preview); nothing is saved. */
    public function withDraft(): static
    {
        $p = clone $this;
        if ($this->hasDraft()) $p->forceFill(array_intersect_key($this->draft, array_flip(self::DRAFTABLE)));
        return $p;
    }

    /** Keeps changes without putting them live. With $at, they go live at that time. */
    public function saveDraft(array $row, ?\DateTimeInterface $at = null): void
    {
        $this->forceFill(['draft' => array_intersect_key($row, array_flip(self::DRAFTABLE)), 'draft_by' => auth()->id(), 'draft_at' => now(), 'publish_at' => $at])->save();
    }

    /** Puts the draft (if any) live, publishes the page and clears the schedule. */
    public function publish(?array $row = null, string $label = 'Published'): void
    {
        $row ??= (array) $this->draft;
        static::$revisionLabel = $label;
        $this->forceFill(array_intersect_key($row, array_flip(self::DRAFTABLE)) + ['published' => true, 'draft' => null, 'draft_by' => null, 'draft_at' => null, 'publish_at' => null])->save();
        static::$revisionLabel = null;
    }

    public function discardDraft(): void
    {
        $this->forceFill(['draft' => null, 'draft_by' => null, 'draft_at' => null, 'publish_at' => $this->published ? null : $this->publish_at])->save();
    }

    /** Publishes every page whose scheduled time has passed. Returns how many. */
    public static function publishDue(): int
    {
        $n = 0;
        foreach (static::query()->whereNotNull('publish_at')->where('publish_at', '<=', now())->get() as $p) {
            $p->publish(null, 'Scheduled publish');
            $n++;
        }
        return $n;
    }
}
