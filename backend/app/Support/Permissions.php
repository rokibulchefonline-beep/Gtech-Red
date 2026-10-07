<?php

namespace App\Support;

/** Every permission in the admin panel, grouped by section. A role is a list of "section.action" keys. */
class Permissions
{
    public const SECTIONS = [
        'pages' => ['label' => 'Pages', 'hint' => 'Without "Publish" a user can only save drafts for someone else to publish.', 'actions' => ['view' => 'View', 'create' => 'Create landing pages', 'edit' => 'Edit', 'publish' => 'Publish', 'delete' => 'Delete landing pages']],
        'posts' => ['label' => 'Blog posts', 'hint' => 'Without "Edit any" a user edits only their own posts. Without "Publish" they can only save drafts.', 'actions' => ['view' => 'View', 'create' => 'Write', 'edit' => 'Edit any', 'publish' => 'Publish', 'delete' => 'Delete']],
        'case_studies' => ['label' => 'Case studies', 'actions' => ['view' => 'View', 'create' => 'Create', 'edit' => 'Edit', 'delete' => 'Delete']],
        'media' => ['label' => 'Media library', 'actions' => ['view' => 'View', 'create' => 'Upload', 'delete' => 'Delete']],
        'seo' => ['label' => 'SEO and redirects', 'hint' => 'SEO overrides, keyword map and redirects', 'actions' => ['view' => 'View', 'create' => 'Create', 'edit' => 'Edit', 'delete' => 'Delete']],
        'structure' => ['label' => 'Site structure', 'hint' => 'Services menu, industries, numbers, testimonials, logos, categories', 'actions' => ['view' => 'View', 'create' => 'Create', 'edit' => 'Edit', 'delete' => 'Delete']],
        'leads' => ['label' => 'Leads', 'hint' => 'Without "See everyone\'s" a user sees only the leads assigned to them.', 'actions' => ['view' => 'View', 'all' => 'See everyone\'s', 'edit' => 'Update', 'assign' => 'Assign', 'templates' => 'Manage email templates', 'delete' => 'Delete and erase (GDPR)', 'export' => 'Export']],
        'subscribers' => ['label' => 'Newsletter subscribers', 'actions' => ['view' => 'View', 'delete' => 'Delete', 'export' => 'Export CSV']],
        'analytics' => ['label' => 'Analytics', 'actions' => ['view' => 'View']],
        'settings' => ['label' => 'Site settings', 'hint' => 'Contact details, tracking codes, email (SMTP)', 'actions' => ['view' => 'View', 'edit' => 'Edit']],
        'users' => ['label' => 'Users and roles', 'hint' => 'Only give this to people you trust: they can change everyone\'s access', 'actions' => ['view' => 'View', 'manage' => 'Add, edit and remove']],
    ];

    /** All "section.action" keys. */
    public static function all(): array
    {
        $out = [];
        foreach (self::SECTIONS as $s => $def) foreach (array_keys($def['actions']) as $a) $out[] = "$s.$a";
        return $out;
    }

    private static function sections(array $sections, array $actions = ['view', 'all', 'create', 'edit', 'assign', 'templates', 'publish', 'delete', 'export', 'manage']): array
    {
        return array_values(array_filter(self::all(), fn ($p) => in_array(strtok($p, '.'), $sections, true) && in_array(substr($p, strpos($p, '.') + 1), $actions, true)));
    }

    /** The roles a new installation starts with (they can be changed in Users and roles > Roles). */
    public static function defaultRoles(): array
    {
        $content = ['pages', 'posts', 'case_studies', 'media', 'seo', 'structure'];
        return [
            'super_admin' => ['name' => 'Super admin', 'description' => 'Everything, always. Cannot be changed.', 'perms' => self::all()],
            'admin' => ['name' => 'Admin', 'description' => 'Everything except users and roles.', 'perms' => self::sections(array_diff(array_keys(self::SECTIONS), ['users']))],
            'editor' => ['name' => 'Editor', 'description' => 'All website content.', 'perms' => self::sections($content)],
            'author' => ['name' => 'Author', 'description' => 'Writes blog posts as drafts for an editor to publish.', 'perms' => ['posts.view', 'posts.create', 'media.view', 'media.create']],
            'seo' => ['name' => 'SEO manager', 'description' => 'SEO fields, redirects, page copy and analytics.', 'perms' => [...self::sections(['seo']), 'pages.view', 'pages.edit', 'pages.publish', 'posts.view', 'posts.edit', 'case_studies.view', 'analytics.view']],
            'sales_manager' => ['name' => 'Sales manager', 'description' => 'All leads and subscribers, with exports and analytics.', 'perms' => [...self::sections(['leads', 'subscribers']), 'analytics.view']],
            'sales' => ['name' => 'Sales', 'description' => 'Works the leads assigned to them.', 'perms' => ['leads.view', 'leads.edit']],
            'viewer' => ['name' => 'Viewer', 'description' => 'Read-only access to content, leads and analytics.', 'perms' => [...self::sections(array_keys(self::SECTIONS), ['view']), 'leads.all']],
        ];
    }

    /** The old single-word permissions, still accepted: true when the role can at least view one of these sections. */
    public const LEGACY = [
        'content' => ['pages', 'posts', 'case_studies', 'media', 'seo', 'structure'],
        'leads' => ['leads', 'subscribers'], 'settings' => ['settings'], 'users' => ['users'], 'analytics' => ['analytics'],
    ];
}
