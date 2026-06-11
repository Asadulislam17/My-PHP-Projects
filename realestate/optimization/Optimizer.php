<?php
/**
 * ══════════════════════════════════════════════
 * ASSET MINIFIER — CSS + JS Minification
 * optimization/Minifier.php
 * ══════════════════════════════════════════════
 */

class Minifier {

    private static string $cacheDir;

    public static function init(): void {
        self::$cacheDir = ROOT_PATH . 'assets/cache/';
        if (!is_dir(self::$cacheDir)) mkdir(self::$cacheDir, 0755, true);
    }

    /* ─────────────────────────────────────────
       CSS MINIFICATION
    ───────────────────────────────────────── */

    public static function minifyCSS(string $css): string {
        // Remove comments
        $css = preg_replace('/\/\*.*?\*\//s', '', $css);
        // Remove whitespace
        $css = preg_replace('/\s+/', ' ', $css);
        // Remove spaces around selectors/values
        $css = preg_replace('/\s*([{};:,>+~])\s*/', '$1', $css);
        // Remove trailing semicolons before }
        $css = str_replace(';}', '}', $css);
        // Remove units from zero values
        $css = preg_replace('/(:| )0(?:px|em|rem|%|vw|vh|pt|cm|mm|in)/', '${1}0', $css);
        // Trim
        return trim($css);
    }

    /* ─────────────────────────────────────────
       JS MINIFICATION (Basic)
    ───────────────────────────────────────── */

    public static function minifyJS(string $js): string {
        // Remove single-line comments (careful not to remove URLs)
        $js = preg_replace('/(?<!["\'])\/\/(?![^\n]*[\'"]\s*\))[^\n]*/', '', $js);
        // Remove multi-line comments
        $js = preg_replace('/\/\*.*?\*\//s', '', $js);
        // Remove whitespace
        $js = preg_replace('/\s+/', ' ', $js);
        // Remove spaces around operators
        $js = preg_replace('/\s*([=+\-*\/{};,:()\[\]|&!<>?])\s*/', '$1', $js);
        return trim($js);
    }

    /* ─────────────────────────────────────────
       BUNDLE & CACHE ASSETS
    ───────────────────────────────────────── */

    public static function bundleCSS(array $files, string $bundleName): string {
        $cacheFile = self::$cacheDir . $bundleName . '.min.css';
        $cacheKey  = md5(implode('', array_map('filemtime', $files)));
        $metaFile  = $cacheFile . '.meta';

        // Return cached if valid
        if (file_exists($cacheFile) && file_exists($metaFile)) {
            if (file_get_contents($metaFile) === $cacheKey) {
                return '/assets/cache/' . $bundleName . '.min.css';
            }
        }

        // Rebuild bundle
        $combined = '';
        foreach ($files as $file) {
            if (file_exists($file)) {
                $css       = file_get_contents($file);
                $baseDir   = dirname($file);
                // Fix relative URLs
                $css       = preg_replace_callback(
                    '/url\([\'"]?(?!https?:|data:|\/)(.*?)[\'"]?\)/i',
                    fn($m) => 'url(' . self::resolveRelativePath($baseDir, $m[1]) . ')',
                    $css
                );
                $combined .= "/* {$file} */\n" . $css . "\n";
            }
        }

        $minified = self::minifyCSS($combined);
        file_put_contents($cacheFile, $minified);
        file_put_contents($metaFile, $cacheKey);

        return '/assets/cache/' . $bundleName . '.min.css';
    }

    public static function bundleJS(array $files, string $bundleName): string {
        $cacheFile = self::$cacheDir . $bundleName . '.min.js';
        $metaFile  = $cacheFile . '.meta';

        $validFiles = array_filter($files, 'file_exists');
        $cacheKey   = md5(implode('', array_map('filemtime', $validFiles)));

        if (file_exists($cacheFile) && file_exists($metaFile)) {
            if (file_get_contents($metaFile) === $cacheKey) {
                return '/assets/cache/' . $bundleName . '.min.js';
            }
        }

        $combined = '';
        foreach ($validFiles as $file) {
            $combined .= "/* {$file} */\n" . file_get_contents($file) . ";\n";
        }

        $minified = self::minifyJS($combined);
        file_put_contents($cacheFile, $minified);
        file_put_contents($metaFile, $cacheKey);

        return '/assets/cache/' . $bundleName . '.min.js';
    }

    private static function resolveRelativePath(string $baseDir, string $relativePath): string {
        return str_replace(ROOT_PATH, '/', realpath($baseDir . '/' . $relativePath) ?: $relativePath);
    }

    /* ─────────────────────────────────────────
       HTML MINIFICATION
    ───────────────────────────────────────── */

    public static function minifyHTML(string $html): string {
        // Remove HTML comments (except IE conditionals)
        $html = preg_replace('/<!--(?!\[if).*?-->/s', '', $html);
        // Remove whitespace between tags
        $html = preg_replace('/>\s+</', '><', $html);
        // Remove multiple spaces
        $html = preg_replace('/\s{2,}/', ' ', $html);
        return trim($html);
    }

    /* ─────────────────────────────────────────
       OUTPUT BUFFERING (use in header.php)
    ───────────────────────────────────────── */

    public static function startBuffering(): void {
        if (!APP_DEBUG) {
            ob_start([self::class, 'minifyHTML']);
        }
    }

    /** Clear cache */
    public static function clearCache(): int {
        $files = glob(self::$cacheDir . '*.{css,js,meta}', GLOB_BRACE) ?: [];
        foreach ($files as $f) unlink($f);
        return count($files);
    }
}

/**
 * ══════════════════════════════════════════════
 * IMAGE OPTIMIZER
 * ══════════════════════════════════════════════
 */
class ImageOptimizer {

    /** Convert any image to WebP with quality control */
    public static function toWebP(string $sourcePath, string $destPath, int $quality = 82): bool {
        if (!file_exists($sourcePath)) return false;
        $info = @getimagesize($sourcePath);
        if (!$info) return false;

        $image = match($info['mime']) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png'  => self::pngWithAlpha($sourcePath),
            'image/gif'  => imagecreatefromgif($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default      => null,
        };
        if (!$image) return false;

        $dir = dirname($destPath);
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $result = imagewebp($image, $destPath, $quality);
        imagedestroy($image);
        return $result;
    }

    /** Generate multiple responsive sizes */
    public static function generateResponsive(string $sourcePath, string $outputDir, string $basename): array {
        $sizes  = [320 => 'sm', 640 => 'md', 960 => 'lg', 1280 => 'xl'];
        $output = [];

        foreach ($sizes as $width => $suffix) {
            $destPath = $outputDir . '/' . $basename . '-' . $suffix . '.webp';
            if (self::resizeAndConvert($sourcePath, $destPath, $width)) {
                $output[$suffix] = $destPath;
            }
        }

        return $output;
    }

    /** Resize + convert to WebP */
    public static function resizeAndConvert(
        string $source,
        string $dest,
        int    $maxWidth,
        int    $maxHeight = 0,
        int    $quality   = 82
    ): bool {
        $info = @getimagesize($source);
        if (!$info) return false;

        [$srcW, $srcH] = $info;

        // Calculate new dimensions
        if ($maxHeight > 0) {
            $ratio  = min($maxWidth / $srcW, $maxHeight / $srcH);
        } else {
            $ratio  = $maxWidth / $srcW;
        }

        // Don't upscale
        if ($ratio >= 1) return self::toWebP($source, $dest, $quality);

        $newW   = (int)($srcW * $ratio);
        $newH   = (int)($srcH * $ratio);

        $image  = match($info['mime']) {
            'image/jpeg' => imagecreatefromjpeg($source),
            'image/png'  => self::pngWithAlpha($source),
            'image/gif'  => imagecreatefromgif($source),
            'image/webp' => imagecreatefromwebp($source),
            default      => null,
        };
        if (!$image) return false;

        $resized = imagecreatetruecolor($newW, $newH);
        // Preserve transparency
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefilledrectangle($resized, 0, 0, $newW, $newH, $transparent);

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);

        $dir = dirname($dest);
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $result = imagewebp($resized, $dest, $quality);
        imagedestroy($image);
        imagedestroy($resized);
        return $result;
    }

    /** Generate srcset string for responsive images */
    public static function generateSrcset(string $baseUrl, array $sizes): string {
        $srcset = [];
        foreach ($sizes as $width => $suffix) {
            $srcset[] = str_replace('{size}', $suffix, $baseUrl) . " {$width}w";
        }
        return implode(', ', $srcset);
    }

    /** PNG with alpha channel */
    private static function pngWithAlpha(string $path): \GdImage|false {
        $image = imagecreatefrompng($path);
        if (!$image) return false;
        imagealphablending($image, false);
        imagesavealpha($image, true);
        return $image;
    }

    /** Get file size in human-readable format */
    public static function getSize(string $path): string {
        if (!file_exists($path)) return '0 B';
        $bytes = filesize($path);
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes/1024, 1) . ' KB';
        return round($bytes/1048576, 1) . ' MB';
    }

    /** Calculate compression ratio */
    public static function compressionRatio(string $original, string $optimized): float {
        if (!file_exists($original) || !file_exists($optimized)) return 0;
        $origSize = filesize($original);
        $optSize  = filesize($optimized);
        if ($origSize === 0) return 0;
        return round((1 - $optSize / $origSize) * 100, 1);
    }
}

/**
 * ══════════════════════════════════════════════
 * RATE LIMITER — Protect APIs and forms
 * ══════════════════════════════════════════════
 */
class RateLimiter {

    private Cache $cache;

    public function __construct() {
        $this->cache = Cache::getInstance();
    }

    /**
     * Check rate limit
     * @param string $key    Unique identifier (ip+action)
     * @param int    $limit  Max requests
     * @param int    $window Time window in seconds
     */
    public function check(string $key, int $limit, int $window): array {
        $cacheKey  = "rl:{$key}";
        $data      = $this->cache->get($cacheKey) ?? ['count' => 0, 'reset_at' => time() + $window];

        // Reset if window expired
        if (time() > $data['reset_at']) {
            $data = ['count' => 0, 'reset_at' => time() + $window];
        }

        $data['count']++;
        $this->cache->set($cacheKey, $data, $window);

        $remaining = max(0, $limit - $data['count']);
        $exceeded  = $data['count'] > $limit;

        // Set rate limit headers
        header('X-RateLimit-Limit: '     . $limit);
        header('X-RateLimit-Remaining: ' . $remaining);
        header('X-RateLimit-Reset: '     . $data['reset_at']);

        return [
            'allowed'   => !$exceeded,
            'count'     => $data['count'],
            'remaining' => $remaining,
            'reset_at'  => $data['reset_at'],
        ];
    }

    /** Quick check — dies with 429 if exceeded */
    public function enforce(string $key, int $limit = 60, int $window = 60): void {
        $ip     = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $result = $this->check("{$key}:{$ip}", $limit, $window);

        if (!$result['allowed']) {
            http_response_code(429);
            header('Retry-After: ' . ($result['reset_at'] - time()));
            echo json_encode([
                'status'  => 'error',
                'message' => "Rate limit exceeded. Wait {$window}s.",
                'reset_at'=> $result['reset_at'],
            ]);
            exit;
        }
    }
}

/**
 * ══════════════════════════════════════════════
 * PAGINATION HELPER
 * ══════════════════════════════════════════════
 */
class Paginator {

    public int   $total;
    public int   $perPage;
    public int   $currentPage;
    public int   $lastPage;
    public int   $from;
    public int   $to;
    public array $items;

    public function __construct(array $items, int $total, int $perPage, int $currentPage) {
        $this->items       = $items;
        $this->total       = $total;
        $this->perPage     = $perPage;
        $this->currentPage = $currentPage;
        $this->lastPage    = (int)ceil($total / $perPage);
        $this->from        = ($currentPage - 1) * $perPage + 1;
        $this->to          = min($total, $currentPage * $perPage);
    }

    public function hasPages(): bool     { return $this->lastPage > 1; }
    public function hasPrevPage(): bool  { return $this->currentPage > 1; }
    public function hasNextPage(): bool  { return $this->currentPage < $this->lastPage; }
    public function prevPage(): int      { return $this->currentPage - 1; }
    public function nextPage(): int      { return $this->currentPage + 1; }

    /** Generate pagination links array */
    public function links(int $radius = 2): array {
        $links = [];
        $start = max(1, $this->currentPage - $radius);
        $end   = min($this->lastPage, $this->currentPage + $radius);

        if ($start > 1)               $links[] = ['page'=>1,    'label'=>'«', 'type'=>'first'];
        if ($this->hasPrevPage())     $links[] = ['page'=>$this->prevPage(), 'label'=>'‹', 'type'=>'prev'];
        if ($start > 2)               $links[] = ['page'=>null, 'label'=>'…', 'type'=>'dots'];

        for ($i = $start; $i <= $end; $i++) {
            $links[] = ['page'=>$i, 'label'=>(string)$i, 'type'=> $i===$this->currentPage ? 'current' : 'page'];
        }

        if ($end < $this->lastPage - 1) $links[] = ['page'=>null, 'label'=>'…', 'type'=>'dots'];
        if ($this->hasNextPage())        $links[] = ['page'=>$this->nextPage(), 'label'=>'›', 'type'=>'next'];
        if ($end < $this->lastPage)      $links[] = ['page'=>$this->lastPage, 'label'=>'»', 'type'=>'last'];

        return $links;
    }

    /** Render pagination HTML */
    public function render(string $urlPattern = '?p={page}'): string {
        if (!$this->hasPages()) return '';

        $html = '<nav class="pagination-wrap"><ul class="pagination-custom">';
        foreach ($this->links() as $link) {
            if ($link['type'] === 'dots') {
                $html .= '<li><span class="page-dots">…</span></li>';
            } elseif ($link['type'] === 'current') {
                $html .= "<li><span class='page-btn active'>{$link['label']}</span></li>";
            } elseif ($link['page']) {
                $url   = str_replace('{page}', $link['page'], $urlPattern);
                $html .= "<li><a href='{$url}' class='page-btn'>{$link['label']}</a></li>";
            }
        }
        $html .= '</ul>';
        $html .= "<p class='pagination-info'>মোট {$this->total} টি — {$this->from}-{$this->to} দেখাচ্ছে</p>";
        $html .= '</nav>';
        return $html;
    }

    /** JSON for API */
    public function toArray(): array {
        return [
            'total'        => $this->total,
            'per_page'     => $this->perPage,
            'current_page' => $this->currentPage,
            'last_page'    => $this->lastPage,
            'from'         => $this->from,
            'to'           => $this->to,
            'has_prev'     => $this->hasPrevPage(),
            'has_next'     => $this->hasNextPage(),
        ];
    }
}