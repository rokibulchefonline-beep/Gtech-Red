<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Setting;
use App\Models\Subscriber;
use App\Support\Crm\LeadRouting;
use App\Support\Crm\Turnstile;
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
        $audit = $v('source') === 'audit';
        $service = $audit ? 'Free audit' : $v('service');
        $bad = ! $name || ! $business || ! $phone || ! $service || ! filter_var($email, FILTER_VALIDATE_EMAIL) || (! in_array($v('source'), ['inquiry', 'audit'], true) && ! $v('budget')) || ($audit && ! $v('website'));
        if ($bad) return response()->json(['ok' => false, 'error' => 'Fill all required fields with a valid email.'], 400);
        if (! Turnstile::passes($v('cf-turnstile-response'), $r->ip())) {
            return response()->json(['ok' => false, 'error' => 'Please complete the "I am human" check and send again.'], 400);
        }
        if (! \App\Support\Crm\Recaptcha::passes($v('g-recaptcha-response'), $r->ip())) {
            return response()->json(['ok' => false, 'error' => 'Please complete the "I\'m not a robot" check and send again.'], 400);
        }
        // More than 5 enquiries an hour from one network is not a person.
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($rk = 'leads-ip:'.$r->ip(), 5)) {
            return response()->json(['ok' => false, 'error' => 'We have already received several enquiries from you. Please call or email us instead.'], 429);
        }
        \Illuminate\Support\Facades\RateLimiter::hit($rk, 3600);

        // Where the lead came from: the website visit (analytics) it belongs to, and the page the form was on.
        $sid = $v('sid');
        $visit = preg_match('/^[a-f0-9]{32}$/', $sid) ? \App\Models\AnalyticsVisit::query()->where('sid', $sid)->first() : null;
        $formPath = (string) parse_url((string) $r->headers->get('referer'), PHP_URL_PATH);

        $lead = Lead::create([
            'name' => mb_substr($name, 0, 120), 'business' => mb_substr($business, 0, 160), 'email' => mb_substr($email, 0, 160), 'phone' => mb_substr($phone, 0, 40),
            'service' => mb_substr($service, 0, 120), 'budget' => mb_substr($v('budget'), 0, 60), 'designation' => mb_substr($v('designation'), 0, 80),
            'company_size' => mb_substr($v('size'), 0, 40), 'website' => mb_substr($v('website'), 0, 200), 'postcode' => mb_substr($v('postcode'), 0, 20),
            'message' => mb_substr($v('message'), 0, 3000), 'source' => $v('source') ?: 'contact', 'status' => 'new', 'notes' => '', 'assignee' => '',
            'channel' => $visit->channel ?? '', 'origin' => mb_substr($visit->source ?? '', 0, 80), 'landing_path' => mb_substr($visit->landing_path ?? '', 0, 300),
            'utm_campaign' => mb_substr($visit->utm_campaign ?? '', 0, 120), 'form_path' => mb_substr($formPath, 0, 300),
            'assigned_to' => LeadRouting::pickOwner(),
            // What the person was told about their data when they sent the form (UK GDPR transparency).
            'consent_text' => mb_substr(strip_tags(preg_replace('/\[([^\]]+)\]\(([^)]*)\)/', '$1 ($2)', (string) (Setting::group('forms')['privacyNotice'] ?? ''))), 0, 600),
            'ip' => (string) $r->ip(),
            'details' => $audit ? self::auditDetails($r) : null,
        ]);

        if ($visit) {
            $lead->forceFill(['visit_id' => $visit->id])->saveQuietly();
            if (! $visit->lead_id) $visit->forceFill(['lead_id' => $lead->id])->save();
        }

        try { LeadRouting::alert($lead); } catch (\Throwable $e) { report($e); }
        // Email is best effort: the lead is already saved.
        try { SiteMailer::newLead($lead); } catch (\Throwable $e) { report($e); }
        return response()->json(['ok' => true]);
    }

    /** The free audit form's extra answers. */
    private static function auditDetails(Request $r): array
    {
        $list = fn ($x) => array_values(array_filter(array_map(fn ($s) => mb_substr(trim((string) $s), 0, 80), is_array($x) ? $x : explode(',', (string) $x))));
        return array_filter([
            'goals' => array_slice($list($r->input('goals')), 0, 10),
            'areas' => array_slice($list($r->input('areas')), 0, 10),
            'competitors' => mb_substr(trim((string) $r->input('competitors', '')), 0, 600),
            'location' => mb_substr(trim((string) $r->input('location', '')), 0, 120),
        ]);
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
