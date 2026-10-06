<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Schema;

/**
 * Forms send an emptied optional field as null, but text columns here are NOT NULL with a '' default, so saving
 * failed with a database error. Before saving, null in such a column becomes '' (and 0 for numbers).
 */
trait BlankNotNull
{
    private static array $notNullColumns = [];

    public static function bootBlankNotNull(): void
    {
        static::saving(function ($model) {
            $table = $model->getTable();
            self::$notNullColumns[$table] ??= collect(Schema::getColumns($table))
                ->filter(fn ($c) => ! $c['nullable'] && ! $c['auto_increment'])
                ->mapWithKeys(fn ($c) => [$c['name'] => preg_match('/int|decimal|float|double|numeric/i', $c['type_name']) ? 0 : ''])
                ->all();
            foreach (self::$notNullColumns[$table] as $col => $empty) {
                if (array_key_exists($col, $model->getAttributes()) && $model->getAttributes()[$col] === null && ! in_array($col, ['created_at', 'updated_at'], true)) {
                    $model->setAttribute($col, $empty);
                }
            }
        });
    }
}
