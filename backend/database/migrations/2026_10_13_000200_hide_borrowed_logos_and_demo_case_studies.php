<?php

use App\Models\CaseStudy;
use App\Models\Client;
use Illuminate\Database\Migrations\Migration;

/**
 * The client logos loaded from another agency's website (growmemarketing.ca) are hidden, and the two placeholder
 * case studies go back to draft. Nothing is deleted: show a logo again (Client logos) once it is your own client
 * with the logo uploaded here, and publish a case study when it is real.
 */
return new class extends Migration
{
    public function up(): void
    {
        Client::query()->where('logo', 'like', '%growmemarketing.ca%')->get()->each(fn (Client $c) => $c->forceFill(['visible' => false])->save());
        CaseStudy::query()->whereIn('slug', ['demo-brand-five', 'demo-brand-six'])->get()->each(fn (CaseStudy $c) => $c->forceFill(['status' => 'draft'])->save());
        \App\Support\Site\PageCache::flush();
    }

    public function down(): void
    {
    }
};
