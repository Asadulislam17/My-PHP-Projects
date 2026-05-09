<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../classes/Auth.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$method = $_SERVER['REQUEST_METHOD'];
$path   = trim($_GET['path'] ?? '', '/');
$body   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$auth   = Auth::getInstance();

function apiResp(mixed $data, int $code = 200): never {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// POST /api/v1/auth/register
if ($method === 'POST' && $path === 'auth/register') {
    $result = $auth->register($body);
    apiResp($result, $result['success'] ? 201 : 422);
}

// POST /api/v1/auth/login
if ($method === 'POST' && $path === 'auth/login') {
    $result = $auth->login(
        $body['email']    ?? '',
        $body['password'] ?? '',
        (bool)($body['remember'] ?? false)
    );
    // Generate API token (use remember_token field)
    if ($result['success']) {
        $db    = Database::getInstance();
        $token = bin2hex(random_bytes(32));
        $db->execute(
            "UPDATE users SET remember_token=? WHERE email=?",
            [$token, strtolower($body['email'])]
        );
        $result['token'] = $token;
    }
    apiResp($result, $result['success'] ? 200 : 401);
}

// POST /api/v1/auth/verify-otp
if ($method === 'POST' && $path === 'auth/verify-otp') {
    $result = $auth->verifyOTP($body['email'] ?? '', $body['otp'] ?? '');
    apiResp($result, $result['success'] ? 200 : 400);
}

// POST /api/v1/auth/resend-otp
if ($method === 'POST' && $path === 'auth/resend-otp') {
    $result = $auth->resendOTP($body['email'] ?? '');
    apiResp($result, $result['success'] ? 200 : 400);
}

// POST /api/v1/auth/forgot-password
if ($method === 'POST' && $path === 'auth/forgot-password') {
    $result = $auth->forgotPassword($body['email'] ?? '');
    apiResp($result);
}

// POST /api/v1/auth/reset-password
if ($method === 'POST' && $path === 'auth/reset-password') {
    $result = $auth->resetPassword(
        $body['email']        ?? '',
        $body['otp']          ?? '',
        $body['new_password'] ?? ''
    );
    apiResp($result, $result['success'] ? 200 : 400);
}

// GET /api/v1/auth/me  ── Current User
if ($method === 'GET' && $path === 'auth/me') {
    if (!isset($_SESSION['user_id'])) {
        apiResp(['success'=>false,'message'=>'Unauthorized'], 401);
    }
    $db   = Database::getInstance();
    $user = $db->queryOne(
        "SELECT u.id,u.name,u.email,u.phone,u.avatar,u.status,r.name role
         FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=?",
        [$_SESSION['user_id']]
    );
    apiResp(['success'=>true,'data'=>$user]);
}

// POST /api/v1/auth/logout
if ($method === 'POST' && $path === 'auth/logout') {
    $db    = Database::getInstance();
    $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION'] ?? '');
    if ($token) {
        $db->execute("UPDATE users SET remember_token=NULL WHERE remember_token=?", [$token]);
    }
    apiResp(['success'=>true,'message'=>'Logged out']);
}

apiResp(['status'=>'error','message'=>'Endpoint not found'], 404);