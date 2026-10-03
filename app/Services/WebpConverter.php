<?php

namespace App\Services;

class WebpConverter
{
    /**
     * Convert an uploaded or local image file (JPG/PNG) to WebP format.
     *
     * @param string $sourcePath Absolute path to the source image
     * @param string $destinationPath Absolute path where the .webp image should be written
     * @param int $quality Quality setting (0 to 100)
     * @return bool True on success, false on failure
     */
    public static function convert(string $sourcePath, string $destinationPath, int $quality = 82): bool
    {
        if (!file_exists($sourcePath)) {
            return false;
        }

        $info = @getimagesize($sourcePath);
        if (!$info) {
            return false;
        }

        $mime = $info['mime'];

        // If already webp, copy directly if destination differs
        if ($mime === 'image/webp') {
            if ($sourcePath !== $destinationPath) {
                return @copy($sourcePath, $destinationPath);
            }
            return true;
        }

        $image = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/gif' => @imagecreatefromgif($sourcePath),
            'image/avif' => function_exists('imagecreatefromavif') ? @imagecreatefromavif($sourcePath) : null,
            'image/bmp' => function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($sourcePath) : null,
            default => null,
        };

        if (!$image) {
            return false;
        }

        // Preserve transparency for PNG and GIF
        if (in_array($mime, ['image/png', 'image/gif'], true)) {
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        $dir = dirname($destinationPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $result = @imagewebp($image, $destinationPath, $quality);
        imagedestroy($image);

        return $result;
    }
}
