<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Setting;
use App\Models\Subscriber;
use App\Support\SiteMailer;
use Illuminate\Http\Request;

/** Public form endpoints used by the website (contact / proposal forms and the blog newsletter). */
class FormController extends Controller
{
    public function contact(Request $r)
    {
        $v = fn (string $k) => trim((string) $r->input($k, ''));
        if ($v('hp_field') !== '') return response()->json(['ok' => true]); // honeypot

        $name = $v('name') ?: trim($v('firstName').' '.$v('lastName'));
        $business = $v('business') ?: $v('company');
        $phone = trim($v('countryCode').' '.$v('phone'));
        $email = $v('email');
        $service = $v('service');
        $bad = ! $name || ! $business || ! $phone || ! $service || ! filter_var($email, FILTER_VALIDATE_EMAIL) || ($v('source') !== 'inquiry' && ! $v('budget'));
        if ($bad) return response()->json(['ok' => false, 'error' => 'Fill all required fields with a valid email.'], 400);

        $lead = Lead::create([
            'name' => mb_substr($name, 0, 120), 'business' => mb_substr($business, 0, 160), 'email' => mb_substr($email, 0, 160), 'phone' => mb_substr($phone, 0, 40),
            'service' => mb_substr($service, 0, 120), 'budget' => mb_substr($v('budget'), 0, 60), 'designation' => mb_substr($v('designation'), 0, 80),
            'company_size' => mb_substr($v('size'), 0, 40), 'website' => mb_substr($v('website'), 0, 200), 'postcode' => mb_substr($v('postcode'), 0, 20),
            'message' => mb_substr($v('message'), 0, 3000), 'source' => $v('source') ?: 'contact', 'status' => 'new', 'notes' => '', 'assignee' => '',
        ]);

        // Link the lead to the website visit it came from (analytics: leads by source and landing page).
        $sid = $v('sid');
        if (preg_match('/^[a-f0-9]{32}$/', $sid) && ($visit = \App\Models\AnalyticsVisit::query()->where('sid', $sid)->first())) {
            $lead->forceFill(['visit_id' => $visit->id])->saveQuietly();
            if (! $visit->lead_id) $visit->forceFill(['lead_id' => $lead->id])->save();
        }

        // Email is best effort: the lead is already saved.
        try { SiteMailer::newLead($lead); } catch (\Throwable $e) { report($e); }
        return response()->json(['ok' => true]);
    }

    public function subscribe(Request $r)
    {
        if ($r->input('website')) return response()->json(['ok' => true]); // honeypot
        $email = strtolower(trim((string) $r->input('email')));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) return response()->json(['ok' => false, 'error' => 'Enter a valid email address.'], 400);
        Subscriber::firstOrCreate(['email' => $email], ['source' => 'blog']);
        return response()->json(['ok' => true]);
    }

    /** Public, non-secret settings for the website (contact details, tracking ids, socials). */
    public function settings()
    {
        return response()->json(['ok' => true, 'settings' => Setting::publicView()]);
    }
}
