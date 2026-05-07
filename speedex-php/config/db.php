<?php
// SpeedEx Courier - DB Connection
define('DB_HOST', 'localhost');
define('DB_NAME', 'speedex_courier');
define('DB_USER', 'root');
define('DB_PASS', '');
define('ADMIN_SECRET_CODE', 'SPEEDEX-ADMIN-2025');

try {
  $pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
    DB_USER, DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
  );
} catch (PDOException $e) {
  http_response_code(500);
  die(json_encode(['error' => 'Database connection failed']));
}

if (session_status() === PHP_SESSION_NONE) session_start();

function require_role($role) {
  if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== $role) {
    header('Location: /auth/login.php'); exit;
  }
}

function json_input() {
  return json_decode(file_get_contents('php://input'), true) ?? [];
}

function generate_tracking_id() {
  return 'SPX' . strtoupper(bin2hex(random_bytes(5)));
}
