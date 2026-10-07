<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// CRM, part 2: one contact per person (however often they enquire), what each person was told about their data,
// reusable email templates, and a log of data erased on request (GDPR).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $t) {
            $t->id();
            $t->string('email', 160)->unique();
            $t->string('name', 120)->default('');
            $t->string('phone', 40)->default('');
            $t->string('business', 160)->default('');
            $t->timestamp('first_seen_at')->nullable();
            $t->timestamp('last_seen_at')->nullable();
            $t->timestamps();
        });

        Schema::table('leads', function (Blueprint $t) {
            $t->foreignId('contact_id')->nullable()->index();
            $t->string('consent_text', 600)->default(''); // the privacy notice shown with the form when it was sent
            $t->string('ip', 45)->default('');
        });

        Schema::create('email_templates', function (Blueprint $t) {
            $t->id();
            $t->string('name', 80);
            $t->string('subject', 200);
            $t->text('body');
            $t->timestamps();
        });

        Schema::create('erasures', function (Blueprint $t) {
            $t->id();
            $t->char('email_hash', 64)->index();      // sha256 of the address, so a repeat request can be recognised
            $t->foreignId('user_id')->nullable();
            $t->string('reason', 200)->default('');
            $t->json('counts')->nullable();             // what was removed
            $t->timestamp('created_at')->nullable();
        });

        // One contact per email address from the leads so far.
        foreach (DB::table('leads')->where('email', '!=', '')->orderBy('created_at')->get() as $l) {
            $email = mb_strtolower(trim($l->email));
            $c = DB::table('contacts')->where('email', $email)->first();
            if (! $c) {
                $id = DB::table('contacts')->insertGetId(['email' => $email, 'name' => $l->name, 'phone' => $l->phone, 'business' => $l->business,
                    'first_seen_at' => $l->created_at, 'last_seen_at' => $l->created_at, 'created_at' => now(), 'updated_at' => now()]);
            } else {
                $id = $c->id;
                DB::table('contacts')->where('id', $id)->update(['name' => $l->name ?: $c->name, 'phone' => $l->phone ?: $c->phone, 'business' => $l->business ?: $c->business, 'last_seen_at' => $l->created_at]);
            }
            DB::table('leads')->where('id', $l->id)->update(['contact_id' => $id]);
        }

        $now = now();
        DB::table('email_templates')->insert([
            ['name' => 'First reply', 'subject' => 'Your enquiry about {service}', 'created_at' => $now, 'updated_at' => $now,
                'body' => '<p>Hi {first_name},</p><p>Thanks for getting in touch about {service} for {business}. I would love to learn a bit more about your goals so we can put together the right proposal.</p><p>Are you free for a 15-minute call this week? Just reply with a time that suits you.</p><p>Best regards,<br>{my_name}<br>{company_name} · {company_phone}</p>'],
            ['name' => 'Proposal follow-up', 'subject' => 'Following up on your {service} proposal', 'created_at' => $now, 'updated_at' => $now,
                'body' => '<p>Hi {first_name},</p><p>I wanted to check you received the proposal for {business}, and whether you have any questions about it.</p><p>Happy to walk you through it on a quick call.</p><p>Best regards,<br>{my_name}<br>{company_name}</p>'],
            ['name' => 'Checking in', 'subject' => 'Checking in, {first_name}', 'created_at' => $now, 'updated_at' => $now,
                'body' => '<p>Hi {first_name},</p><p>Just checking in to see if {service} is still on your list for {business}. If the timing is not right, no problem: let me know and I will get back in touch later.</p><p>Best regards,<br>{my_name}</p>'],
        ]);

        // New permission: managing email templates goes to roles that can assign leads.
        foreach (DB::table('roles')->get() as $r) {
            $perms = json_decode($r->perms ?: '[]', true) ?: [];
            if (in_array('leads.assign', $perms, true)) DB::table('roles')->where('id', $r->id)->update(['perms' => json_encode(array_values(array_unique([...$perms, 'leads.templates'])))]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('erasures');
        Schema::dropIfExists('email_templates');
        Schema::table('leads', fn (Blueprint $t) => $t->dropColumn(['contact_id', 'consent_text', 'ip']));
        Schema::dropIfExists('contacts');
    }
};
