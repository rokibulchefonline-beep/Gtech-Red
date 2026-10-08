<?php

namespace App\Support;

use GdImage;
use RuntimeException;

/**
 * Image work with PHP's GD: resize, convert, compress to a size limit and square crops. Used by the upload fields
 * ("Optimise automatically"), the Image resizer and Image converter tools, and author photos.
 */
class ImageTools
{
    /** Formats GD can write, by extension. */
    public const FORMATS = ['webp' => 'WebP', 'jpg' => 'JPG', 'png' => 'PNG'];

    /** Largest file for blog images and optimised uploads. */
    public const MAX_BYTES = 250 * 1024;

    /** JPG, PNG and still WebP can be processed; SVG, GIF and animated WebP are kept as they are. */
    public static function canProcess(string $path, string $mime): bool
    {
        if (in_array($mime, ['image/svg+xml', 'image/gif'], true) || ! str_starts_with($mime, 'image/')) return false;
        return ! ($mime === 'image/webp' && str_contains((string) @file_get_contents($path, false, null, 0, 64), 'ANIM'));
    }

    public static function load(string $path): GdImage
    {
        $data = @file_get_contents($path);
        $img = $data === false ? false : @imagecreatefromstring($data);
        if (! $img instanceof GdImage) throw new RuntimeException('This file could not be read as an image. Use a JPG, PNG or WebP file.');
        if (! imageistruecolor($img)) imagepalettetotruecolor($img);
        imagealphablending($img, true);
        imagesavealpha($img, true);
        return self::orient($img, $path);
    }

    /** Phone photos are often stored sideways with an EXIF rotation flag: apply it. */
    private static function orient(GdImage $img, string $path): GdImage
    {
        if (! function_exists('exif_read_data')) return $img;
        $o = (int) (@exif_read_data($path)['Orientation'] ?? 1);
        $deg = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
        return $deg ? (imagerotate($img, $deg, 0) ?: $img) : $img;
    }

    private static function canvas(int $w, int $h): GdImage
    {
        $c = imagecreatetruecolor(max(1, $w), max(1, $h));
        imagealphablending($c, false);
        imagesavealpha($c, true);
        imagefill($c, 0, 0, imagecolorallocatealpha($c, 0, 0, 0, 127));
        return $c;
    }

    /**
     * Resize. 'fit' keeps the proportions inside width x height (either may be 0 = automatic), 'crop' fills the
     * exact size and trims the edges, 'stretch' forces the exact size.
     */
    public static function resize(GdImage $img, int $w, int $h, string $mode = 'fit', bool $enlarge = false): GdImage
    {
        $sw = imagesx($img);
        $sh = imagesy($img);
        if ($w <= 0 && $h <= 0) return $img;
        if ($mode === 'crop' && $w > 0 && $h > 0) {
            $scale = max($w / $sw, $h / $sh);
            $cw = (int) round($w / $scale);
            $ch = (int) round($h / $scale);
            $c = self::canvas($w, $h);
            imagecopyresampled($c, $img, 0, 0, (int) (($sw - $cw) / 2), (int) (($sh - $ch) / 2), $w, $h, $cw, $ch);
            return $c;
        }
        if ($mode === 'stretch' && $w > 0 && $h > 0) {
            $c = self::canvas($w, $h);
            imagecopyresampled($c, $img, 0, 0, 0, 0, $w, $h, $sw, $sh);
            return $c;
        }
        $scale = min($w > 0 ? $w / $sw : INF, $h > 0 ? $h / $sh : INF);
        if ($scale >= 1 && ! $enlarge) return $img;
        $nw = max(1, (int) round($sw * $scale));
        $nh = max(1, (int) round($sh * $scale));
        $c = self::canvas($nw, $nh);
        imagecopyresampled($c, $img, 0, 0, 0, 0, $nw, $nh, $sw, $sh);
        return $c;
    }

    /** The image as file bytes in jpg, png or webp. Quality 1-100 (PNG is lossless). */
    public static function encode(GdImage $img, string $format, int $quality = 82): string
    {
        $format = strtolower($format) === 'jpeg' ? 'jpg' : strtolower($format);
        ob_start();
        match ($format) {
            'webp' => imagewebp($img, null, $quality),
            'png' => imagepng($img, null, 9),
            'jpg' => imagejpeg(self::flatten($img), null, $quality),
            default => throw new RuntimeException("Unsupported format: $format"),
        };
        return (string) ob_get_clean();
    }

    /** JPG has no transparency: put the image on white. */
    private static function flatten(GdImage $img): GdImage
    {
        $c = imagecreatetruecolor(imagesx($img), imagesy($img));
        imagefill($c, 0, 0, imagecolorallocate($c, 255, 255, 255));
        imagecopy($c, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
        return $c;
    }

    /**
     * Smallest good-looking version under $maxBytes: no wider than $maxWidth, quality lowered step by step, then
     * made smaller if it is still too big. Returns the bytes.
     */
    public static function compress(GdImage $img, string $format = 'webp', int $maxBytes = self::MAX_BYTES, int $maxWidth = 1920): string
    {
        $img = self::resize($img, $maxWidth, 0);
        for ($round = 0; $round < 8; $round++) {
            foreach ($format === 'png' ? [100] : [85, 78, 70, 62, 55, 48, 40] as $q) {
                $bytes = self::encode($img, $format, $q);
                if (strlen($bytes) <= $maxBytes) return $bytes;
            }
            $img = self::resize($img, (int) round(imagesx($img) * 0.8), 0);
        }
        return $bytes;
    }

    /** Optimise a stored file in place: WebP (or JPG), at most 1920px wide and under 250 KB. Returns the new path. */
    public static function optimiseFile(string $absPath, string $format = 'webp', int $maxBytes = self::MAX_BYTES): string
    {
        $bytes = self::compress(self::load($absPath), $format, $maxBytes);
        $new = preg_replace('/\.[a-z0-9]+$/i', '', $absPath).'.'.$format;
        file_put_contents($new, $bytes);
        if ($new !== $absPath) @unlink($absPath);
        return $new;
    }

    /** A square crop from the centre, e.g. 300 x 300 for author photos. */
    public static function squareFile(string $absPath, int $size = 300, string $format = 'webp'): string
    {
        $bytes = self::encode(self::resize(self::load($absPath), $size, $size, 'crop', true), $format, 85);
        $new = preg_replace('/\.[a-z0-9]+$/i', '', $absPath).'.'.$format;
        file_put_contents($new, $bytes);
        if ($new !== $absPath) @unlink($absPath);
        return $new;
    }

    /** "my-team_photo-2024.jpg" -> "My team photo 2024": a starting alt text from the file name. */
    public static function altFromName(string $name): string
    {
        $base = pathinfo($name, PATHINFO_FILENAME);
        $base = preg_replace('/^(img|image|dsc|pxl|photo)[-_ ]?\d*$/i', '', $base);
        $text = trim(preg_replace('/\s+/', ' ', preg_replace('/[-_.+]+/', ' ', (string) $base)));
        return $text === '' || preg_match('/^[0-9a-f\s]{16,}$/i', $text) ? '' : ucfirst(mb_strtolower($text));
    }

    public static function kb(int $bytes): string
    {
        return $bytes >= 1048576 ? round($bytes / 1048576, 1).' MB' : max(1, (int) round($bytes / 1024)).' KB';
    }
}
