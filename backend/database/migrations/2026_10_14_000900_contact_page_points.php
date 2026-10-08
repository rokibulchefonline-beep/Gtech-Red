<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/** Contact page highlights: Free audit, no obligation / Reply within hours / No hidden fees. */
return new class extends Migration
{
    public function up(): void
    {
        $p = Page::query()->find('page~contact');
        if (! $p) return;
        $hero = (array) $p->hero;
        if (($hero['points'] ?? []) === ['Reply within one working day', 'Free audit and proposal', 'No long contracts']) {
            $hero['points'] = ['Free audit, no obligation', 'Reply within hours', 'No hidden fees'];
            $p->forceFill(['hero' => $hero])->save();
        }
    }

    public function down(): void
    {
    }
};
