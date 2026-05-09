<?php
ob_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Database.php';

// ✅ DB Connection test
$db = Database::getInstance();

// Page routing (simple)
$page = $_GET['page'] ?? 'home';

$allowed_pages = [
    'home', 'listing', 'property', 'login', 'register', 
    'dashboard', 'verify-otp', 'notifications', 'logout',
    'buyer-dashboard', 'agent-dashboard', 'admin-dashboard',
    'add-property', 'tools' // এই লাইনগুলো যোগ করুন
];



if (!in_array($page, $allowed_pages)) {
    $page = '404';
}

$page_file = __DIR__ . "/pages/{$page}.php";

require_once __DIR__ . '/includes/header.php';

if (file_exists($page_file)) {
    require_once $page_file;
} else {
    echo "<h2>404 - Page Not Found</h2>";
}

require_once __DIR__ . '/includes/footer.php';