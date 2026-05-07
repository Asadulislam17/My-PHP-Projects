<?php

namespace App\Services;

/**
 * ImageService – Upload, optimize, and manage property images.
 *
 * Pipeline per upload:
 *  1. Validate file (type, size)
 *  2. Save original
 *  3. Convert to WebP (GD)
 *  4. Generate thumbnail (400×300 WebP)
 */
class ImageService
{
    private string $uploadPath;
    private string $thumbPath;
    private int    $maxSize;
    private array  $allowedTypes = ['image/jpeg','image/png','image/webp','image/gif'];

    private int $maxWidth  = 1200;
    private int $maxHeight = 900;
    private int $thumbW    = 400;
    private int $thumbH    = 300;
    private int $quality   = 82;   // WebP quality (0–100)

    public function __construct()
    {
        $this->uploadPath = ROOT_PATH . '/public/uploads/images/';
        $this->thumbPath  = ROOT_PATH . '/public/uploads/images/thumbs/';
        $this->maxSize    = (int) env('MAX_FILE_SIZE', 10485760);

        foreach ([$this->uploadPath, $this->thumbPath] as $dir) {
            if (!is_dir($dir)) mkdir($dir, 0755, true);
        }
    }

    // ------------------------------------------------------------------ //
    //  Public API                                                          //
    // ------------------------------------------------------------------ //

    /**
     * Process one uploaded file. Returns metadata array or throws.
     *
     * @param  array $file  Single entry from $_FILES
     * @return array{file_name:string, original:string, thumbnail:string, webp:string}
     */
    public function upload(array $file): array
    {
        $this->validate($file);

        $uid      = uniqid('', true);
        $original = $uid . '_orig.' . $this->extension($file['name']);
        $webpName = $uid . '.webp';
        $thumbName = 'thumb_' . $uid . '.webp';

        // Save the original first
        if (!move_uploaded_file($file['tmp_name'], $this->uploadPath . $original)) {
            throw new \RuntimeException('Failed to save uploaded file.');
        }

        // Load image resource
        $src = $this->createResource($this->uploadPath . $original, $file['type']);

        // Resize if needed
        $src = $this->resizeIfNeeded($src, $this->maxWidth, $this->maxHeight);

        // Save WebP
        imagewebp($src, $this->uploadPath . $webpName, $this->quality);

        // Thumbnail
        $thumb = $this->cropResize($src, $this->thumbW, $this->thumbH);
        imagewebp($thumb, $this->thumbPath . $thumbName, $this->quality);

        imagedestroy($src);
        imagedestroy($thumb);

        return [
            'file_name' => $webpName,
            'original'  => $original,
            'thumbnail' => 'thumbs/' . $thumbName,
            'webp'      => $webpName,
        ];
    }

    /**
     * Process multiple files from a multi-upload input.
     * Returns array of metadata arrays.
     */
    public function uploadMultiple(array $filesInput): array
    {
        $results = [];
        $count   = count($filesInput['name']);

        for ($i = 0; $i < $count; $i++) {
            if ($filesInput['error'][$i] !== UPLOAD_ERR_OK) continue;

            $file = [
                'name'     => $filesInput['name'][$i],
                'type'     => $filesInput['type'][$i],
                'tmp_name' => $filesInput['tmp_name'][$i],
                'error'    => $filesInput['error'][$i],
                'size'     => $filesInput['size'][$i],
            ];

            try {
                $results[] = $this->upload($file);
            } catch (\Throwable $e) {
                // Skip invalid files, log and continue
                error_log('Image upload error: ' . $e->getMessage());
            }
        }

        return $results;
    }

    // ------------------------------------------------------------------ //
    //  Internals                                                           //
    // ------------------------------------------------------------------ //

    private function validate(array $file): void
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload error code: ' . $file['error']);
        }
        if ($file['size'] > $this->maxSize) {
            throw new \RuntimeException('File too large. Max: ' . ($this->maxSize / 1048576) . 'MB');
        }

        // Verify MIME by reading file magic bytes, not trusting browser type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $this->allowedTypes)) {
            throw new \RuntimeException("File type not allowed: $mime");
        }
    }

    private function createResource(string $path, string $mime): \GdImage
    {
        return match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png'  => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            'image/gif'  => imagecreatefromgif($path),
            default      => throw new \RuntimeException("Unsupported image type: $mime"),
        };
    }

    private function resizeIfNeeded(\GdImage $src, int $maxW, int $maxH): \GdImage
    {
        $w = imagesx($src);
        $h = imagesy($src);

        if ($w <= $maxW && $h <= $maxH) return $src;

        $ratio  = min($maxW / $w, $maxH / $h);
        $newW   = (int) round($w * $ratio);
        $newH   = (int) round($h * $ratio);

        $dst = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
        imagedestroy($src);
        return $dst;
    }

    /**
     * Center-crop + resize to exact dimensions (for thumbnails).
     */
    private function cropResize(\GdImage $src, int $targetW, int $targetH): \GdImage
    {
        $srcW  = imagesx($src);
        $srcH  = imagesy($src);

        $srcRatio = $srcW / $srcH;
        $dstRatio = $targetW / $targetH;

        if ($srcRatio > $dstRatio) {
            // Wider than target – crop sides
            $cropH = $srcH;
            $cropW = (int) round($srcH * $dstRatio);
            $cropX = (int) round(($srcW - $cropW) / 2);
            $cropY = 0;
        } else {
            // Taller than target – crop top/bottom
            $cropW = $srcW;
            $cropH = (int) round($srcW / $dstRatio);
            $cropX = 0;
            $cropY = (int) round(($srcH - $cropH) / 2);
        }

        $dst = imagecreatetruecolor($targetW, $targetH);
        imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);
        return $dst;
    }

    private function extension(string $filename): string
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
}
