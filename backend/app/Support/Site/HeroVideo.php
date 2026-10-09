<?php

namespace App\Support\Site;

use App\Models\Page;

/**
 * The home hero's background video, set in Pages > Home > Hero: a YouTube link, or a video file (uploaded or any
 * MP4/WebM address). Without a choice it is the original YouTube video. The poster image shows until the video plays,
 * and is all that phones see unless "Play on phones" is on.
 */
class HeroVideo
{
    public const DEFAULT_YOUTUBE = '4YKT1KJbzuQ';

    /** ['type' => 'youtube'|'file'|'none', 'id' => YouTube id, 'src' => file address, 'poster' => image, 'mobile' => bool] */
    public static function for(?Page $p): array
    {
        $h = (array) ($p?->hero ?? []);
        $type = (string) ($h['video_type'] ?? 'youtube');
        $out = ['type' => 'none', 'id' => '', 'src' => '', 'poster' => self::safeUrl($h['video_poster'] ?? ''), 'mobile' => (bool) ($h['video_mobile'] ?? false)];
        if ($type === 'file' && ($src = self::safeUrl($h['video_url'] ?? '')) !== '') return ['type' => 'file', 'src' => $src] + $out;
        if ($type === 'youtube') {
            $id = self::youtubeId((string) ($h['video_youtube'] ?? '')) ?: self::DEFAULT_YOUTUBE;
            return ['type' => 'youtube', 'id' => $id] + $out;
        }
        return $out;
    }

    /** An http(s) or site-relative address with no characters that could break out of an attribute or CSS url(). */
    public static function safeUrl(mixed $v): string
    {
        $v = self::local(trim((string) $v));
        return preg_match('~^(https?://|/)[^\s\'"()<>\\\\]+$~i', $v) ? $v : '';
    }

    /**
     * Files on this site saved with a full address (from APP_URL, e.g. http://192.168.1.20/storage/...) become
     * site-relative, so they load on whatever domain the site is opened on. Also covers private network addresses.
     */
    public static function local(string $v): string
    {
        if (! preg_match('~^https?://~i', $v)) return $v;
        $host = strtolower((string) parse_url($v, PHP_URL_HOST));
        $own = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
        $private = filter_var($host, FILTER_VALIDATE_IP) && ! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        if ($host === $own || $private || $host === 'localhost' || str_ends_with($host, '.test')) {
            $path = (string) parse_url($v, PHP_URL_PATH);
            $q = parse_url($v, PHP_URL_QUERY);
            return $path.($q ? '?'.$q : '');
        }
        return $v;
    }

    /** The video id from a YouTube link (watch, youtu.be, shorts, embed) or a bare id. */
    public static function youtubeId(string $v): string
    {
        $v = trim($v);
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $v)) return $v;
        if (preg_match('~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/))([A-Za-z0-9_-]{11})~', $v, $m)) return $m[1];
        return '';
    }
}
