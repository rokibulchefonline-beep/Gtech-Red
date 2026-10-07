<?php

namespace App\Console\Commands;

use App\Models\CaseStudy;
use App\Models\Category;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Media;
use App\Models\Page;
use App\Support\Content;
use App\Models\Partner;
use App\Models\Post;
use App\Models\SeoEntry;
use App\Models\Setting;
use App\Models\Subscriber;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Imports a MongoDB export (one JSON file per collection, from mongoexport, Compass or Atlas) into MySQL.
 * Safe to run more than once: rows are matched on their old MongoDB id (or slug/key) and updated in place.
 *
 *   php artisan gtech:import-mongo storage/app/import --auth-secret="<old AUTH_SECRET>"
 */
class ImportMongo extends Command
{
    protected $signature = 'gtech:import-mongo
        {dir : Folder with posts.json, case_studies.json, users.json ...}
        {--auth-secret= : The old site\'s AUTH_SECRET, to carry over the encrypted SMTP password}
        {--dry-run : Read and validate the files without writing anything}';

    protected $description = 'Import the GTech website data exported from MongoDB';

    private const FILES = ['users', 'categories', 'posts', 'case_studies', 'partners', 'clients', 'page_content', 'seo', 'leads', 'subscribers', 'media', 'settings'];

    private array $report = [];

    public function handle(): int
    {
        $dir = rtrim($this->argument('dir'), '/');
        if (! is_dir($dir)) {
            $this->error("Folder not found: $dir");
            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');
        // Page edits from the old admin are applied on top of the full page content, so load that first.
        if (! $dry && ! Page::query()->exists()) $this->call('gtech:seed-content', ['--no-demo' => true]);
        foreach (self::FILES as $name) {
            $file = $this->findFile($dir, $name);
            if (! $file) {
                $this->report[] = [$name, '-', '-', 'no file (skipped)'];
                continue;
            }
            try {
                $docs = $this->readDocs($file);
            } catch (\Throwable $e) {
                $this->report[] = [$name, '?', 0, 'unreadable: '.$e->getMessage()];
                continue;
            }
            $ok = 0;
            $errors = [];
            $run = function () use ($docs, $name, &$ok, &$errors) {
                foreach ($docs as $i => $doc) {
                    try {
                        $this->{'import'.str_replace('_', '', ucwords($name, '_'))}($doc);
                        $ok++;
                    } catch (\Throwable $e) {
                        $errors[] = '#'.($i + 1).': '.$e->getMessage();
                    }
                }
            };
            if ($dry) {
                DB::beginTransaction();
                $run();
                DB::rollBack();
            } else {
                DB::transaction($run);
            }
            $this->report[] = [$name, count($docs), $ok, $errors ? count($errors).' error(s): '.implode(' | ', array_slice($errors, 0, 3)) : 'ok'];
        }

        $this->table(['Collection', 'In file', 'Imported', 'Status'], $this->report);
        $this->info($dry ? 'Dry run: nothing was saved.' : 'Import finished. Row counts above should match the counts shown in MongoDB Atlas.');
        return self::SUCCESS;
    }

    // ---- reading ----------------------------------------------------------------------------------

    private function findFile(string $dir, string $name): ?string
    {
        foreach (["$name.json", "gtech.$name.json", "$name.ndjson"] as $f) {
            if (is_file("$dir/$f")) return "$dir/$f";
        }
        $hits = glob("$dir/*$name*.json") ?: [];
        return $hits[0] ?? null;
    }

    /** Accepts a JSON array (mongoexport --jsonArray, Compass, Atlas) or one document per line (plain mongoexport). */
    private function readDocs(string $file): array
    {
        $raw = trim((string) file_get_contents($file));
        if ($raw === '') return [];
        if ($raw[0] === '[') {
            $docs = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } else {
            $docs = [];
            foreach (preg_split('/\r?\n/', $raw) as $line) {
                if (trim($line) !== '') $docs[] = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
            }
        }
        return array_map(fn ($d) => $this->plain($d), $docs);
    }

    /** Turns MongoDB Extended JSON ({"$oid"}, {"$date"}, {"$numberInt"} ...) into plain PHP values. */
    private function plain(mixed $v): mixed
    {
        if (! is_array($v)) return $v;
        if (count($v) === 1) {
            $k = array_key_first($v);
            $x = $v[$k];
            switch ($k) {
                case '$oid': return (string) $x;
                case '$date': return is_array($x) ? Carbon::createFromTimestampMs((int) ($x['$numberLong'] ?? 0))->toIso8601String() : (is_numeric($x) ? Carbon::createFromTimestampMs((int) $x)->toIso8601String() : (string) $x);
                case '$numberInt': case '$numberLong': return (int) $x;
                case '$numberDouble': case '$numberDecimal': return (float) $x;
                case '$binary': return is_array($x) ? ($x['base64'] ?? '') : (string) $x;
            }
        }
        return array_map(fn ($i) => $this->plain($i), $v);
    }

    // ---- helpers ----------------------------------------------------------------------------------

    private static function s(array $d, string $k, int $max = 255): string
    {
        $v = $d[$k] ?? '';
        return mb_substr(is_scalar($v) ? (string) $v : '', 0, $max);
    }

    private static function list(array $d, string $k): array
    {
        return is_array($d[$k] ?? null) ? array_values($d[$k]) : [];
    }

    private static function date(mixed $v): ?Carbon
    {
        if (! $v) return null;
        try { return Carbon::parse($v); } catch (\Throwable) { return null; }
    }

    /** Applies the old createdAt/updatedAt to a saved model. */
    private static function stamp($model, array $d): void
    {
        $c = self::date($d['createdAt'] ?? $d['created_at'] ?? null);
        $u = self::date($d['updatedAt'] ?? null);
        if (! $c && ! $u) return;
        $model->timestamps = false;
        $model->forceFill(array_filter(['created_at' => $c, 'updated_at' => $u ?? $c]))->save();
        $model->timestamps = true;
    }

    private static function id(array $d): string
    {
        return (string) ($d['_id'] ?? '');
    }

    // ---- one method per collection -------------------------------------------------------------------

    private function importUsers(array $d): void
    {
        $email = strtolower(self::s($d, 'email', 160));
        if (! $email) throw new \RuntimeException('user without email');
        $u = User::query()->where('legacy_id', self::id($d))->orWhere('email', $email)->first() ?? new User;
        $u->forceFill([
            'legacy_id' => self::id($d), 'name' => self::s($d, 'name', 80) ?: explode('@', $email)[0], 'email' => $email,
            'role' => \App\Models\Role::query()->where('key', $d['role'] ?? '')->exists() ? $d['role'] : 'editor',
            'active' => ($d['active'] ?? true) !== false,
            // Old PBKDF2 hash is kept as is; it is upgraded to bcrypt on the first login.
            'password' => self::s($d, 'passwordHash', 255) ?: bcrypt(bin2hex(random_bytes(16))),
        ])->save();
        self::stamp($u, $d);
    }

    private function importCategories(array $d): void
    {
        $m = Category::query()->updateOrCreate(['slug' => self::s($d, 'slug', 80) ?: str($d['name'] ?? '')->slug()], ['legacy_id' => self::id($d), 'name' => self::s($d, 'name', 60)]);
        self::stamp($m, $d);
    }

    private function importPosts(array $d): void
    {
        $m = Post::query()->updateOrCreate(['slug' => self::s($d, 'slug', 160)], [
            'legacy_id' => self::id($d), 'title' => self::s($d, 'title', 160), 'excerpt' => self::s($d, 'excerpt', 400),
            'body' => (string) ($d['body'] ?? ''), 'format' => ($d['format'] ?? 'md') === 'html' ? 'html' : 'md',
            'category' => self::s($d, 'category', 60) ?: 'Insights', 'categories' => self::list($d, 'categories'), 'tags' => self::list($d, 'tags'),
            'post_format' => self::s($d, 'postFormat', 20) ?: 'standard', 'visibility' => ($d['visibility'] ?? 'public') === 'private' ? 'private' : 'public',
            'allow_comments' => ($d['allowComments'] ?? true) !== false, 'allow_pingbacks' => ($d['allowPingbacks'] ?? true) !== false,
            'custom_fields' => self::list($d, 'customFields'), 'image' => self::s($d, 'image', 500), 'image_alt' => self::s($d, 'imageAlt', 200),
            'author' => self::s($d, 'author', 80) ?: 'GTech Editorial Team', 'featured' => (bool) ($d['featured'] ?? false),
            'status' => in_array($d['status'] ?? '', ['draft', 'published', 'scheduled'], true) ? $d['status'] : 'draft',
            'date' => self::date($d['date'] ?? null), 'meta_title' => self::s($d, 'metaTitle', 120), 'meta_description' => self::s($d, 'metaDescription', 300),
            'focus_keyword' => self::s($d, 'focusKeyword', 80), 'canonical' => self::s($d, 'canonical', 500), 'noindex' => (bool) ($d['noindex'] ?? false),
        ]);
        self::stamp($m, $d);
    }

    private function importCaseStudies(array $d): void
    {
        $m = CaseStudy::query()->updateOrCreate(['slug' => self::s($d, 'slug', 120)], [
            'legacy_id' => self::id($d), 'title' => self::s($d, 'title', 120), 'client' => self::s($d, 'client', 120), 'industry' => self::s($d, 'industry', 80),
            'duration' => self::s($d, 'duration', 60), 'website' => self::s($d, 'website', 500), 'excerpt' => self::s($d, 'excerpt', 300),
            'image' => self::s($d, 'image', 500), 'image_alt' => self::s($d, 'imageAlt', 200), 'logo' => self::s($d, 'logo', 500),
            'services' => self::list($d, 'services'), 'metrics' => self::list($d, 'metrics'), 'challenge' => (string) ($d['challenge'] ?? ''),
            'solution' => (string) ($d['solution'] ?? ''), 'results' => self::list($d, 'results'), 'quote' => is_array($d['quote'] ?? null) ? $d['quote'] : null,
            'body' => (string) ($d['body'] ?? ''), 'status' => ($d['status'] ?? 'draft') === 'published' ? 'published' : 'draft', 'order' => (int) ($d['order'] ?? 100),
            'meta_title' => self::s($d, 'metaTitle', 120), 'meta_description' => self::s($d, 'metaDescription', 300), 'focus_keyword' => self::s($d, 'focusKeyword', 80),
        ]);
        self::stamp($m, $d);
    }

    private function logo(string $class, array $d): void
    {
        $m = $class::query()->updateOrCreate(['legacy_id' => self::id($d) ?: null, 'name' => self::s($d, 'name', 80)], [
            'logo' => self::s($d, 'logo', 500), 'url' => self::s($d, 'url', 500), 'order' => (int) ($d['order'] ?? 100), 'visible' => ($d['visible'] ?? true) !== false,
        ]);
        self::stamp($m, $d);
    }

    private function importPartners(array $d): void { $this->logo(Partner::class, $d); }

    private function importClients(array $d): void { $this->logo(Client::class, $d); }

    /** Old page edits (overrides) are merged into the full page content. */
    private function importPageContent(array $d): void
    {
        $page = Page::query()->find(self::id($d));
        if (! $page) {
            if ($this->option('dry-run')) return;
            throw new \RuntimeException('no page for '.self::id($d));
        }
        $merged = Content::mergeOverride($page->only(['meta_title', 'meta_description', 'focus_keyword', 'hero', 'sections', 'faqs']), $d);
        $page->fill($merged)->save();
        self::stamp($page, $d);
    }

    private function importSeo(array $d): void
    {
        $m = SeoEntry::query()->updateOrCreate(['key' => self::id($d)], [
            'path' => self::s($d, 'path', 200), 'title' => self::s($d, 'title', 120), 'description' => self::s($d, 'description', 300),
            'canonical' => self::s($d, 'canonical', 500), 'og_image' => self::s($d, 'ogImage', 500), 'noindex' => (bool) ($d['noindex'] ?? false),
            'focus_keyword' => self::s($d, 'focusKeyword', 80), 'schema_off' => (bool) ($d['schemaOff'] ?? false), 'schema_custom' => (string) ($d['schemaCustom'] ?? ''),
        ]);
        self::stamp($m, $d);
    }

    private function importLeads(array $d): void
    {
        $m = Lead::query()->updateOrCreate(['legacy_id' => self::id($d)], [
            'name' => self::s($d, 'name', 120), 'business' => self::s($d, 'business', 160), 'email' => self::s($d, 'email', 160), 'phone' => self::s($d, 'phone', 40),
            'service' => self::s($d, 'service', 120), 'budget' => self::s($d, 'budget', 60), 'designation' => self::s($d, 'designation', 80),
            'company_size' => self::s($d, 'companySize', 40), 'website' => self::s($d, 'website', 200), 'postcode' => self::s($d, 'postcode', 20),
            'message' => (string) ($d['message'] ?? ''), 'source' => self::s($d, 'source', 40) ?: 'contact', 'status' => self::s($d, 'status', 20) ?: 'new',
            'notes' => (string) ($d['notes'] ?? ''), 'assignee' => self::s($d, 'assignee', 80), 'value' => (float) ($d['value'] ?? 0),
        ]);
        self::stamp($m, $d);
    }

    private function importSubscribers(array $d): void
    {
        $email = strtolower(self::s($d, 'email', 160));
        if (! $email) throw new \RuntimeException('subscriber without email');
        $m = Subscriber::query()->updateOrCreate(['email' => $email], ['source' => self::s($d, 'source', 40) ?: 'blog']);
        self::stamp($m, $d);
    }

    /** Images were stored inside MongoDB as base64; they become real files under storage/app/public/media. */
    private function importMedia(array $d): void
    {
        $id = self::id($d);
        $bytes = base64_decode((string) ($d['data'] ?? ''), true);
        if ($bytes === false || $bytes === '') throw new \RuntimeException("media $id has no image data");
        $ext = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp', 'image/gif' => 'gif', 'image/svg+xml' => 'svg'][$d['type'] ?? ''] ?? 'bin';
        $path = "media/$id.$ext";
        if (! $this->option('dry-run')) Storage::disk('public')->put($path, $bytes);
        $m = Media::query()->updateOrCreate(['legacy_id' => $id], [
            'name' => self::s($d, 'name', 160) ?: "$id.$ext", 'type' => self::s($d, 'type', 60), 'size' => strlen($bytes), 'path' => $path, 'uploaded_by' => self::s($d, 'by', 160),
        ]);
        self::stamp($m, $d);
    }

    private function importSettings(array $d): void
    {
        if (self::id($d) !== 'site') return;
        foreach (array_keys(Setting::DEFAULTS) as $group) {
            if (! isset($d[$group]) || ! is_array($d[$group])) continue;
            $value = $d[$group];
            if ($group === 'smtp') {
                $plain = $this->decryptOldSmtp((string) ($value['pass'] ?? ''));
                $value['pass'] = $plain !== '' ? Crypt::encryptString($plain) : '';
            }
            Setting::put($group, $value);
        }
    }

    /** The old site encrypted the SMTP password with AES-GCM using a key derived from AUTH_SECRET. */
    private function decryptOldSmtp(string $v): string
    {
        if ($v === '') return '';
        if (! str_starts_with($v, 'enc:')) return $v;
        $secret = (string) $this->option('auth-secret');
        if ($secret === '') {
            $this->warn('SMTP password not carried over (pass --auth-secret). Re-enter it in Settings > Email.');
            return '';
        }
        [, $iv, $ct] = array_pad(explode(':', $v, 3), 3, '');
        $key = hash('sha256', 'smtp:'.$secret, true);
        $raw = base64_decode($ct);
        $plain = openssl_decrypt(substr($raw, 0, -16), 'aes-256-gcm', $key, OPENSSL_RAW_DATA, base64_decode($iv), substr($raw, -16));
        if ($plain === false) {
            $this->warn('SMTP password could not be decrypted with that AUTH_SECRET. Re-enter it in Settings > Email.');
            return '';
        }
        return $plain;
    }
}
