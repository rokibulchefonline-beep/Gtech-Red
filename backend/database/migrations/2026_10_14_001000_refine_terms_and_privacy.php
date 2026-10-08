<?php

use Illuminate\Database\Migrations\Migration;

/** Terms and Privacy Policy refined; company details filled from Site settings instead of placeholders. */
return new class extends Migration
{
    public function up(): void
    {
        \App\Support\Site\LegalContent::apply();
    }

    public function down(): void
    {
    }
};
