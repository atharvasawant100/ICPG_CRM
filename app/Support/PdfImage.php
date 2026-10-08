<?php

namespace App\Support;

class PdfImage
{
    /**
     * Build a resized data-URI for DomPDF (keeps PDFs small/fast).
     * Transparent pixels are composited onto white so logos don't get a black box.
     */
    public static function dataUri(string $path, int $maxWidth = 400, int $quality = 85): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        if (! extension_loaded('gd')) {
            $mime = mime_content_type($path) ?: 'image/png';

            return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
        }

        $info = @getimagesize($path);
        if ($info === false) {
            return null;
        }

        [$width, $height] = $info;
        $type = $info[2] ?? null;

        $source = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if ($source === false) {
            return null;
        }

        $targetWidth = $width;
        $targetHeight = $height;
        if ($width > $maxWidth) {
            $targetWidth = $maxWidth;
            $targetHeight = (int) max(1, round($height * ($maxWidth / $width)));
        }

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($canvas === false) {
            imagedestroy($source);

            return null;
        }

        // White background (PDF page is white — avoids black boxes from transparent PNGs)
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $white);

        imagealphablending($canvas, true);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        imagejpeg($canvas, null, $quality);
        $binary = ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        if ($binary === false || $binary === '') {
            return null;
        }

        return 'data:image/jpeg;base64,'.base64_encode($binary);
    }
}
