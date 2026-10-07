<?php

namespace App\Support\Site;

use App\Models\Setting;

/** Contact details from Site settings > Contact, ready for the pages and the schema. */
class Contact
{
    /** "0330 380 1000" -> "+443303801000" for tel: links. */
    public static function tel(string $phone): string
    {
        $d = preg_replace('/[^\d+]/', '', $phone);
        if (str_starts_with($d, '+')) return $d;
        if (str_starts_with($d, '00')) return '+'.substr($d, 2);
        return str_starts_with($d, '0') ? '+44'.substr($d, 1) : $d;
    }

    public static function get(): array
    {
        $c = Setting::group('contact');
        $phones = array_values(array_filter([trim((string) ($c['phone'] ?? '')), trim((string) ($c['phone2'] ?? ''))]));
        $lines = array_values(array_filter(array_map('trim', [(string) ($c['street'] ?? ''), (string) ($c['city'] ?? ''), (string) ($c['region'] ?? ''), (string) ($c['postcode'] ?? '')])));
        // Older single-box address, until the Google Business Profile fields are filled in.
        if (! $lines && trim((string) ($c['address'] ?? '')) !== '') $lines = array_values(array_filter(array_map('trim', preg_split('/[\n,]+/', (string) $c['address']))));
        $maps = (string) ($c['mapsUrl'] ?? '');
        return [
            'email' => (string) ($c['email'] ?? ''),
            'phones' => array_map(fn ($p) => ['label' => $p, 'tel' => self::tel($p)], $phones),
            'hours' => (string) ($c['hours'] ?? ''),
            'address' => $lines,
            'mapsUrl' => preg_match('#^https://#', $maps) ? $maps : '',
            'showAddress' => (bool) ($c['showAddress'] ?? true) && $lines,
            'showAddressFooter' => (bool) ($c['showAddressFooter'] ?? false) && $lines,
            'postal' => ($c['street'] ?? '') !== '' ? array_filter(['@type' => 'PostalAddress', 'streetAddress' => $c['street'], 'addressLocality' => $c['city'] ?? '',
                'addressRegion' => $c['region'] ?? '', 'postalCode' => $c['postcode'] ?? '', 'addressCountry' => $c['country'] ?: 'GB'])
                : (trim((string) ($c['address'] ?? '')) !== '' ? ['@type' => 'PostalAddress', 'streetAddress' => $c['address'], 'addressCountry' => 'GB'] : null),
        ];
    }
}
