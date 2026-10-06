<?php

namespace App\Filament\Support;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * User avatars drawn locally as initials on a coloured circle. Filament's default loads them from an outside
 * service (ui-avatars.com), which sends staff names to a third party and shows a broken image when it is blocked.
 */
class InitialsAvatar implements AvatarProvider
{
    private const COLOURS = ['#e8202f', '#b0122c', '#1d4ed8', '#047857', '#7c3aed', '#c2410c', '#0f766e', '#be185d'];

    public function get(Model|Authenticatable $record): string
    {
        $name = trim((string) Filament::getNameForDefaultAvatar($record));
        $words = preg_split('/\s+/', $name) ?: [];
        $initials = mb_strtoupper(mb_substr($words[0] ?? '?', 0, 1).(count($words) > 1 ? mb_substr(end($words), 0, 1) : ''));
        $bg = self::COLOURS[abs(crc32($name)) % count(self::COLOURS)];
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" fill="'.$bg.'"/>'
            .'<text x="50%" y="50%" dy=".35em" text-anchor="middle" font-family="Inter,Arial,sans-serif" font-size="26" font-weight="600" fill="#fff">'
            .htmlspecialchars($initials, ENT_QUOTES | ENT_XML1).'</text></svg>';
        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
