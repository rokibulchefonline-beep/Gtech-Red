<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// CRM basics: who owns each lead, the next follow-up, where the lead came from, and a timeline of activity.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $t) {
            $t->foreignId('assigned_to')->nullable()->index();
            $t->timestamp('next_action_at')->nullable()->index();
            $t->string('next_action', 160)->default('');
            $t->timestamp('first_contacted_at')->nullable();
            $t->string('channel', 30)->default('');       // as in analytics: Search, AI, Social, Referral, Paid, Email, Campaign, Direct
            $t->string('origin', 80)->default('');        // Google, ChatGPT, linkedin.com...
            $t->string('landing_path', 300)->default('');
            $t->string('form_path', 300)->default('');    // the page the form was sent from
            $t->string('utm_campaign', 120)->default('');
        });

        Schema::create('lead_activities', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lead_id')->index();
            $t->foreignId('user_id')->nullable();
            $t->string('type', 20);   // created, note, call, email, meeting, status, assigned, follow_up, value
            $t->text('body')->nullable();
            $t->timestamp('created_at')->nullable()->index();
        });

        // Existing "assigned to" text that matches a team member's name becomes a real assignment.
        $users = DB::table('users')->pluck('id', 'name')->mapWithKeys(fn ($id, $n) => [mb_strtolower(trim($n)) => $id]);
        foreach (DB::table('leads')->where('assignee', '!=', '')->get(['id', 'assignee']) as $l) {
            if ($id = $users[mb_strtolower(trim($l->assignee))] ?? null) DB::table('leads')->where('id', $l->id)->update(['assigned_to' => $id]);
        }

        // Roles: the new "See everyone's leads" and "Assign" permissions go to roles that could already see leads,
        // except the Sales role, which now sees only its own leads.
        foreach (DB::table('roles')->get() as $r) {
            $perms = json_decode($r->perms ?: '[]', true) ?: [];
            if (! in_array('leads.view', $perms, true) || $r->key === 'sales') continue;
            $add = ['leads.all'];
            if (in_array('leads.edit', $perms, true) && $r->key !== 'viewer') $add[] = 'leads.assign';
            DB::table('roles')->where('id', $r->id)->update(['perms' => json_encode(array_values(array_unique([...$perms, ...$add])))]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
        Schema::table('leads', fn (Blueprint $t) => $t->dropColumn(['assigned_to', 'next_action_at', 'next_action', 'first_contacted_at', 'channel', 'origin', 'landing_path', 'form_path', 'utm_campaign']));
    }
};
