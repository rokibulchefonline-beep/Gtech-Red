<?php

namespace App\Filament\Support;

/**
 * For edit pages that show the Schema markup panel: loads it from the page's SEO override, saves it back, and
 * moves the override when the page's address changes. The page defines schemaPath().
 */
trait HasSchemaPanel
{
    public ?string $schemaOldPath = null;

    abstract protected function schemaPath(): string;

    protected function fillSchema(array $data): array
    {
        $this->schemaOldPath = $this->schemaPath();
        $data['schema'] = SchemaPanel::fill($this->schemaOldPath);
        return $data;
    }

    protected function saveSchema(): void
    {
        $new = $this->schemaPath();
        if ($this->schemaOldPath && $this->schemaOldPath !== $new) {
            // The address changed: its SEO override (title, schema...) moves with it.
            $old = \App\Models\SeoEntry::query()->find(\App\Models\SeoEntry::keyFor($this->schemaOldPath));
            if ($old && ! \App\Models\SeoEntry::query()->find(\App\Models\SeoEntry::keyFor($new))) {
                $copy = $old->replicate();
                $copy->forceFill(['key' => \App\Models\SeoEntry::keyFor($new), 'path' => $new])->save();
                $old->delete();
            }
        }
        SchemaPanel::save($new, $this->data['schema'] ?? null);
        $this->schemaOldPath = $new;
    }
}
