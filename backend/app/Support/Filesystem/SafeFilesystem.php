<?php

namespace App\Support\Filesystem;

use Illuminate\Filesystem\Filesystem;

/**
 * Laravel writes compiled templates to a temporary file and renames it into place. On Windows the rename fails with
 * "Access is denied" when the target is open at that moment (another request compiling the same view, or antivirus
 * scanning it), which crashed admin pages. Here the rename is retried briefly, then the file is written directly.
 */
class SafeFilesystem extends Filesystem
{
    public function replace($path, $content, $mode = null)
    {
        if (DIRECTORY_SEPARATOR !== '\\') {
            parent::replace($path, $content, $mode);
            return;
        }
        clearstatcache(true, $path);
        $path = realpath($path) ?: $path;
        $temp = tempnam(dirname($path), basename($path));
        @chmod($temp, is_null($mode) ? 0777 - umask() : $mode);
        file_put_contents($temp, $content);
        for ($i = 0; $i < 5; $i++) {
            if (@rename($temp, $path)) return;
            // Another request already wrote the same compiled file: nothing to do.
            if (is_file($path) && @file_get_contents($path) === $content) { @unlink($temp); return; }
            usleep(50000 * ($i + 1));
        }
        @unlink($temp);
        file_put_contents($path, $content, LOCK_EX);
    }
}
