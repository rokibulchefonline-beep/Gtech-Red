<?php

namespace App\Filament\Support;

/** Default no-op form hooks; resources override what they need. */
trait HooksDefault
{
    public static function beforeFill(array $data): array { return $data; }

    public static function beforeSave(array $data, $record = null): array { return $data; }
}
