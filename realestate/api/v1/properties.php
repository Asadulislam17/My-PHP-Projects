<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../classes/Auth.php';
require_once __DIR__ . '/../../classes/Property.php';

// ── API Bootstrap ─────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

class APIResponse {
    public static function success(mixed $data, string $message = 'success', int $code = 200): never {
        http_response_code($code);
        echo json_encode([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
            'meta'    => ['timestamp' => date('c'), 'version' => 'v1']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function error(string $message, int $code = 400, array $errors = []): never {
        http_response_code($code);
        echo json_encode([
            'status'  => 'error',
            'message' => $message,
            'errors'  => $errors,
            'meta'    => ['timestamp' => date('c'), 'version' => 'v1']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ── Rate Limiting ─────────────────────────────────
function checkRateLimit(string $ip, int $limit = 60): void {
    $db    = Database::getInstance();
    $key   = 'ratelimit_' . md5($ip);
    $file  = sys_get_temp_dir() . '/' . $key;
    $now   = time();
    $window= 60;

    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : ['count'=>0,'reset'=>$now+$window];
    if ($now > $data['reset']) { $data = ['count'=>0,'reset'=>$now+$window]; }
    $data['count']++;
    file_put_contents($file, json_encode($data));

    if ($data['count'] > $limit) {
        APIResponse::error('Rate limit exceeded. ' . $limit . ' req/min.', 429);
    }
    header('X-RateLimit-Limit: '    . $limit);
    header('X-RateLimit-Remaining: '. max(0, $limit - $data['count']));
}

checkRateLimit($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

// ── Auth Middleware ───────────────────────────────
function getAPIUser(): ?array {
    $token = null;

    // Bearer token
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (str_starts_with($authHeader, 'Bearer ')) {
        $token = substr($authHeader, 7);
    }
    // Fallback: session
    if (!$token && isset($_SESSION['user_id'])) {
        return ['id' => $_SESSION['user_id'], 'role' => $_SESSION['user_role']];
    }

    if (!$token) return null;

    $db = Database::getInstance();
    $user = $db->queryOne(
        "SELECT u.id, r.name AS role FROM users u
         JOIN roles r ON r.id = u.role_id
         WHERE u.remember_token = ? AND u.status = 'active'",
        [$token]
    );
    return $user ?: null;
}

function requireAuth(): array {
    $user = getAPIUser();
    if (!$user) APIResponse::error('Unauthorized', 401);
    return $user;
}

// ── Router ────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$path   = trim($_GET['path'] ?? $_GET['endpoint'] ?? '', '/');

// Parse body
$body = [];
if (in_array($method, ['POST','PUT','PATCH'])) {
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true) ?? $_POST;
}

$db       = Database::getInstance();
$propClass= Property::getInstance();

// ──────────────────────────────────────────────────
// ROUTES
// ──────────────────────────────────────────────────

// GET /api/v1/properties  ── List with filters
if ($method === 'GET' && ($path === '' || $path === 'properties')) {
    $filters = [
        'keyword'    => $_GET['keyword']    ?? '',
        'price_type' => $_GET['price_type'] ?? '',
        'type'       => $_GET['type']       ?? '',
        'area_id'    => $_GET['area_id']    ?? '',
        'price_min'  => $_GET['price_min']  ?? '',
        'price_max'  => $_GET['price_max']  ?? '',
        'bedrooms'   => $_GET['bedrooms']   ?? '',
        'featured'   => $_GET['featured']   ?? '',
        'sort'       => $_GET['sort']       ?? 'newest',
    ];
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $perPage = min(50, max(1, (int)($_GET['per_page'] ?? 12)));

    $result = $propClass->getAll($filters, $page, $perPage);

    // Clean up for API
    $data = array_map(function($p) {
        $p['cover_url'] = $p['cover_image']
            ? UPLOAD_URL . 'properties/' . $p['cover_image']
            : null;
        return $p;
    }, $result['data']);

    APIResponse::success([
        'properties' => $data,
        'pagination' => [
            'total'        => $result['total'],
            'per_page'     => $result['per_page'],
            'current_page' => $result['current_page'],
            'last_page'    => $result['last_page'],
        ]
    ]);
}

// GET /api/v1/properties/{id}  ── Single
if ($method === 'GET' && preg_match('/^properties\/(\d+)$/', $path, $m)) {
    $prop = $propClass->getById((int)$m[1]);
    if (!$prop || $prop['status'] !== 'approved') {
        APIResponse::error('Property not found', 404);
    }
    $prop['images'] = array_map(fn($img) => array_merge($img, [
        'url'       => UPLOAD_URL . 'properties/' . $img['image_path'],
        'thumb_url' => UPLOAD_URL . 'properties/thumbs/' . ($img['thumbnail'] ?: $img['image_path']),
    ]), $prop['images']);
    APIResponse::success($prop);
}

// GET /api/v1/properties/featured  ── Featured List
if ($method === 'GET' && $path === 'properties/featured') {
    $result = $propClass->getAll(['featured' => true], 1, 6);
    APIResponse::success($result['data']);
}

// GET /api/v1/properties/search/suggestions  ── AJAX Suggestions
if ($method === 'GET' && $path === 'properties/search/suggestions') {
    $q = trim($_GET['q'] ?? '');
    if (strlen($q) < 2) APIResponse::success([]);

    $suggestions = $db->query(
        "SELECT DISTINCT a.id, a.name, d.name AS district
         FROM areas a
         JOIN districts d ON d.id = a.district_id
         WHERE a.name LIKE ? OR d.name LIKE ?
         LIMIT 8",
        ['%'.$q.'%', '%'.$q.'%']
    );
    APIResponse::success($suggestions);
}

// POST /api/v1/properties  ── Create (auth required)
if ($method === 'POST' && $path === 'properties') {
    $user = requireAuth();
    if (!in_array($user['role'], ['agent','admin'])) {
        APIResponse::error('Only agents can create properties', 403);
    }
    $result = $propClass->create($body, $user['id']);
    if ($result['success']) {
        APIResponse::success(['property_id' => $result['property_id']], $result['message'], 201);
    }
    APIResponse::error('Validation failed', 422, $result['errors']);
}

// PUT /api/v1/properties/{id}  ── Update
if ($method === 'PUT' && preg_match('/^properties\/(\d+)$/', $path, $m)) {
    $user   = requireAuth();
    $result = $propClass->update((int)$m[1], $body, $user['id']);
    $result['success']
        ? APIResponse::success([], $result['message'])
        : APIResponse::error($result['message'], 400);
}

// DELETE /api/v1/properties/{id}  ── Delete
if ($method === 'DELETE' && preg_match('/^properties\/(\d+)$/', $path, $m)) {
    $user   = requireAuth();
    $result = $propClass->delete((int)$m[1], $user['id']);
    $result['success']
        ? APIResponse::success([], $result['message'])
        : APIResponse::error($result['message'], 400);
}

// POST /api/v1/properties/{id}/wishlist  ── Toggle Wishlist
if ($method === 'POST' && preg_match('/^properties\/(\d+)\/wishlist$/', $path, $m)) {
    $user   = requireAuth();
    $result = $propClass->toggleWishlist((int)$m[1], $user['id']);
    APIResponse::success(['wishlisted' => $result['wishlisted']], $result['message']);
}

// GET /api/v1/properties/wishlist  ── Get Wishlist
if ($method === 'GET' && $path === 'properties/wishlist') {
    $user = requireAuth();
    $list = $propClass->getWishlist($user['id']);
    APIResponse::success($list);
}

// GET /api/v1/properties/types  ── Property Types
if ($method === 'GET' && $path === 'properties/types') {
    $types = $db->query("SELECT * FROM property_types ORDER BY name");
    APIResponse::success($types);
}

// GET /api/v1/properties/areas  ── Areas
if ($method === 'GET' && $path === 'properties/areas') {
    $areas = $db->query(
        "SELECT a.id, a.name, d.name district, dv.name division
         FROM areas a
         JOIN districts d ON d.id=a.district_id
         JOIN divisions dv ON dv.id=d.division_id
         ORDER BY a.name"
    );
    APIResponse::success($areas);
}

// ── 404 ───────────────────────────────────────────
APIResponse::error('Endpoint not found', 404);