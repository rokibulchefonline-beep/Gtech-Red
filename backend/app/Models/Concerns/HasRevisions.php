<?php

namespace App\Models\Concerns;

use App\Models\Revision;

/**
 * Keeps a version of the record every time its content changes, so any earlier version can be compared and
 * restored. The model lists the attributes that make up its content in REVISIONED.
 */
trait HasRevisions
{
    /** Label for the next version written (set by the screen that saves, e.g. "Restored"). */
    public static ?string $revisionLabel = null;

    public static function bootHasRevisions(): void
    {
        static::created(fn ($m) => $m->writeRevision('Created'));
        // Records from before the history existed: keep the version being replaced, so the first change can be undone.
        static::updating(function ($m) {
            if ($m->isDirty(static::REVISIONED) && ! $m->revisions()->exists()) {
                $m->writeRevision('Earlier version', array_intersect_key($m->getOriginal(), array_flip(static::REVISIONED)), keepLabel: true);
            }
        });
        static::updated(function ($m) {
            if ($m->wasChanged(static::REVISIONED)) $m->writeRevision();
        });
    }

    public static function revisionType(): string
    {
        return array_search(static::class, Revision::MODELS, true) ?: class_basename(static::class);
    }

    public function revisionData(): array
    {
        return array_intersect_key($this->attributesToArray(), array_flip(static::REVISIONED));
    }

    public function writeRevision(?string $label = null, ?array $data = null, bool $keepLabel = false): void
    {
        try {
            Revision::query()->create(['model' => static::revisionType(), 'model_key' => (string) $this->getKey(), 'user_id' => $data === null ? auth()->id() : null,
                'label' => $label ?? static::$revisionLabel ?? 'Saved', 'data' => json_decode(json_encode($data ?? $this->revisionData()), true)]);
            if (! $keepLabel) static::$revisionLabel = null;
            $old = Revision::query()->where('model', static::revisionType())->where('model_key', (string) $this->getKey())
                ->latest('id')->skip(Revision::KEEP)->take(1000)->pluck('id');
            if ($old->isNotEmpty()) Revision::query()->whereIn('id', $old)->delete();
        } catch (\Throwable $e) {
            report($e); // never block a save because of the history
        }
    }

    public function revisions()
    {
        return Revision::query()->where('model', static::revisionType())->where('model_key', (string) $this->getKey())->latest('id');
    }

    /** Puts an earlier version back (it becomes the newest version, so the restore can be undone too). */
    public function restoreRevision(Revision $r): void
    {
        static::$revisionLabel = 'Restored version from '.$r->created_at?->format('j M Y H:i');
        $this->fill(array_intersect_key($r->data, array_flip(static::REVISIONED)))->save();
    }
}
