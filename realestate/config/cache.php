<?php
/**
 * ══════════════════════════════════════════════
 * CACHE MANAGER — Redis + File Fallback
 * config/Cache.php
 * ══════════════════════════════════════════════
 */

class Cache {

    private static ?Cache $instance = null;
    private mixed $redis = null;
    private bool  $usingRedis = false;
    private string $fileDir;
    private int   $defaultTTL = 3600; // 1 hour

    private function __construct() {
        $this->fileDir = sys_get_temp_dir() . '/realestate_cache/';
        if (!is_dir($this->fileDir)) mkdir($this->fileDir, 0755, true);
        $this->initRedis();
    }

    public static function getInstance(): Cache {
        if (self::$instance === null) self::$instance = new Cache();
        return self::$instance;
    }

    private function initRedis(): void {
        if (!extension_loaded('redis')) return;
        try {
            $this->redis      = new Redis();
            $connected        = $this->redis->connect(
                $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                (int)($_ENV['REDIS_PORT'] ?? 6379),
                2.0  // 2s timeout
            );
            if ($connected) {
                $pass = $_ENV['REDIS_PASS'] ?? '';
                if ($pass) $this->redis->auth($pass);
                $this->redis->select((int)($_ENV['REDIS_DB'] ?? 0));
                $this->usingRedis = true;
            }
        } catch (Exception $e) {
            $this->redis      = null;
            $this->usingRedis = false;
            if (APP_DEBUG) error_log('[Cache] Redis failed: ' . $e->getMessage());
        }
    }

    /* ─────────────────────────────────────────
       CORE METHODS
    ───────────────────────────────────────── */

    /** Get cached value */
    public function get(string $key): mixed {
        $key = $this->sanitizeKey($key);

        if ($this->usingRedis) {
            $val = $this->redis->get($key);
            if ($val === false) return null;
            return unserialize($val);
        }

        return $this->fileGet($key);
    }

    /** Set cached value */
    public function set(string $key, mixed $value, int $ttl = 0): bool {
        $key = $this->sanitizeKey($key);
        $ttl = $ttl ?: $this->defaultTTL;

        if ($this->usingRedis) {
            return $this->redis->setex($key, $ttl, serialize($value));
        }

        return $this->fileSet($key, $value, $ttl);
    }

    /** Delete cached key */
    public function delete(string $key): bool {
        $key = $this->sanitizeKey($key);
        if ($this->usingRedis) return (bool)$this->redis->del($key);
        return $this->fileDelete($key);
    }

    /** Check if key exists */
    public function has(string $key): bool {
        $key = $this->sanitizeKey($key);
        if ($this->usingRedis) return (bool)$this->redis->exists($key);
        return $this->fileGet($key) !== null;
    }

    /** Get or compute (cache-aside pattern) */
    public function remember(string $key, int $ttl, callable $callback): mixed {
        $cached = $this->get($key);
        if ($cached !== null) return $cached;
        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }

    /** Delete keys by pattern (Redis only, file fallback clears all) */
    public function deletePattern(string $pattern): int {
        if ($this->usingRedis) {
            $keys  = $this->redis->keys($this->sanitizeKey($pattern));
            $count = 0;
            foreach ($keys as $key) { $this->redis->del($key); $count++; }
            return $count;
        }
        return $this->clearFileCache();
    }

    /** Flush all cache */
    public function flush(): bool {
        if ($this->usingRedis) {
            $this->redis->flushDB();
            return true;
        }
        return $this->clearFileCache() >= 0;
    }

    /** Increment counter (atomic) */
    public function increment(string $key, int $by = 1): int {
        $key = $this->sanitizeKey($key);
        if ($this->usingRedis) return (int)$this->redis->incrBy($key, $by);
        $val = (int)($this->get($key) ?? 0);
        $this->set($key, $val + $by, 86400);
        return $val + $by;
    }

    /** TTL check */
    public function ttl(string $key): int {
        $key = $this->sanitizeKey($key);
        if ($this->usingRedis) return (int)$this->redis->ttl($key);
        return -1;
    }

    /** Cache stats */
    public function stats(): array {
        if ($this->usingRedis) {
            $info = $this->redis->info('stats');
            return [
                'driver'        => 'redis',
                'hits'          => $info['keyspace_hits']   ?? 0,
                'misses'        => $info['keyspace_misses'] ?? 0,
                'memory_used'   => $this->redis->info('memory')['used_memory_human'] ?? '?',
                'total_keys'    => $this->redis->dbSize(),
                'uptime_seconds'=> $this->redis->info('server')['uptime_in_seconds'] ?? 0,
            ];
        }
        $files = glob($this->fileDir . '*.cache') ?: [];
        return [
            'driver'     => 'file',
            'total_files'=> count($files),
            'cache_dir'  => $this->fileDir,
            'disk_usage' => $this->formatBytes(array_sum(array_map('filesize', $files))),
        ];
    }

    /* ─────────────────────────────────────────
       FILE CACHE IMPLEMENTATION
    ───────────────────────────────────────── */

    private function fileGet(string $key): mixed {
        $file = $this->fileDir . $key . '.cache';
        if (!file_exists($file)) return null;

        $data = unserialize(file_get_contents($file));
        if (!is_array($data) || !isset($data['expires_at'], $data['value'])) return null;
        if ($data['expires_at'] < time()) { unlink($file); return null; }

        return $data['value'];
    }

    private function fileSet(string $key, mixed $value, int $ttl): bool {
        $file = $this->fileDir . $key . '.cache';
        $data = serialize(['expires_at' => time() + $ttl, 'value' => $value]);
        return file_put_contents($file, $data, LOCK_EX) !== false;
    }

    private function fileDelete(string $key): bool {
        $file = $this->fileDir . $key . '.cache';
        return file_exists($file) ? unlink($file) : true;
    }

    private function clearFileCache(): int {
        $files = glob($this->fileDir . '*.cache') ?: [];
        foreach ($files as $f) unlink($f);
        return count($files);
    }

    /* ─────────────────────────────────────────
       HELPERS
    ───────────────────────────────────────── */

    private function sanitizeKey(string $key): string {
        return 're:' . preg_replace('/[^a-zA-Z0-9:_*-]/', '_', $key);
    }

    private function formatBytes(int $bytes): string {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes/1024, 1) . ' KB';
        return round($bytes/1048576, 1) . ' MB';
    }

    private function __clone() {}
}

/**
 * ══════════════════════════════════════════════
 * QUERY OPTIMIZER — DB Query Helper
 * ══════════════════════════════════════════════
 */
class QueryOptimizer {

    private static Cache $cache;
    private static Database $db;

    public static function init(): void {
        self::$cache = Cache::getInstance();
        self::$db    = Database::getInstance();
    }

    /** Cached query — auto-invalidate by tag */
    public static function cachedQuery(
        string $cacheKey,
        string $sql,
        array  $params  = [],
        int    $ttl     = 300,
        bool   $single  = false
    ): mixed {
        return self::$cache->remember($cacheKey, $ttl, function() use ($sql, $params, $single) {
            return $single
                ? self::$db->queryOne($sql, $params)
                : self::$db->query($sql, $params);
        });
    }

    /** Get featured properties (cached 10 min) */
    public static function getFeaturedProperties(int $limit = 6): array {
        return self::cachedQuery(
            "featured_props_{$limit}",
            "SELECT p.*,pt.name type_name,a.name area_name,d.name district_name,
                    u.name agent_name,u.phone agent_phone,
                    (SELECT image_path FROM property_images
                     WHERE property_id=p.id AND is_cover=1 LIMIT 1) cover_image
             FROM properties p
             JOIN property_types pt ON pt.id=p.type_id
             JOIN areas a           ON a.id=p.area_id
             JOIN districts d       ON d.id=a.district_id
             JOIN users u           ON u.id=p.user_id
             WHERE p.is_featured=1 AND p.status='approved'
             ORDER BY p.created_at DESC LIMIT ?",
            [$limit], 600
        );
    }

    /** Get property types (cached 1 hour) */
    public static function getPropertyTypes(): array {
        return self::cachedQuery('property_types', "SELECT * FROM property_types ORDER BY name", [], 3600);
    }

    /** Get areas with districts (cached 1 hour) */
    public static function getAreas(): array {
        return self::cachedQuery(
            'areas_list',
            "SELECT a.id,a.name,d.name district,dv.name division
             FROM areas a
             JOIN districts d ON d.id=a.district_id
             JOIN divisions dv ON dv.id=d.division_id
             ORDER BY dv.name,d.name,a.name",
            [], 3600
        );
    }

    /** Get material rates (cached 30 min) */
    public static function getMaterialRates(): array {
        return self::cachedQuery('material_rates', "SELECT * FROM material_rates ORDER BY id", [], 1800);
    }

    /** Invalidate property-related caches */
    public static function invalidatePropertyCache(int $propertyId = 0): void {
        $cache = Cache::getInstance();
        $cache->delete("prop_{$propertyId}");
        $cache->deletePattern('featured_props_*');
        $cache->deletePattern('prop_list_*');
        $cache->deletePattern('trending_*');
    }
}