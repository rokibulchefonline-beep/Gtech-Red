<?php

namespace App\Support;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Makes uploaded background videos light: 1280 px wide H.264, no sound, "fast start" (plays while downloading),
 * plus a WebP poster from the first second. Needs ffmpeg on the server (FFMPEG_PATH in .env, or on the PATH);
 * without it the upload is kept as it is.
 */
class VideoTools
{
    public static function ffmpeg(): ?string
    {
        $p = (string) env('FFMPEG_PATH', '');
        if ($p !== '' && is_executable($p)) return $p;
        return (new ExecutableFinder())->find('ffmpeg');
    }

    /** Compresses $abs in place (as .mp4). Returns [video path, poster path or null], or null when ffmpeg is missing or fails. */
    public static function optimise(string $abs): ?array
    {
        $ff = self::ffmpeg();
        if (! $ff) return null;
        $out = preg_replace('/\.[a-z0-9]+$/i', '', $abs).'-web.mp4';
        $p = new Process([$ff, '-y', '-loglevel', 'error', '-i', $abs, '-an', '-t', '60',
            '-vf', "scale='min(1280,iw)':-2,fps=30", '-c:v', 'libx264', '-preset', 'slow', '-crf', '28', '-pix_fmt', 'yuv420p', '-movflags', '+faststart', $out]);
        $p->setTimeout(600)->run();
        if (! $p->isSuccessful() || ! is_file($out) || filesize($out) < 1000) { @unlink($out); return null; }
        $poster = preg_replace('/\.mp4$/', '.webp', $out);
        $q = new Process([$ff, '-y', '-loglevel', 'error', '-ss', '1', '-i', $out, '-frames:v', '1', '-vf', "scale='min(1600,iw)':-2", '-c:v', 'libwebp', '-quality', '72', $poster]);
        $q->setTimeout(120)->run();
        if ($out !== $abs) @unlink($abs);
        return [$out, $q->isSuccessful() && is_file($poster) ? $poster : null];
    }
}
