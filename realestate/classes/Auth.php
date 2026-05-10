<?php

require_once __DIR__ . '/../config/Database.php';

class Auth {

    private Database $db;
    private static ?Auth $instance = null;

    private function __construct() {
        $this->db = Database::getInstance();
    }

    // Singleton
    public static function getInstance(): Auth {
        if (self::$instance === null) {
            self::$instance = new Auth();
        }
        return self::$instance;
    }

    // =========================================
    // ✅ REGISTER
    // =========================================
    // public function register(array $data): array {

    //     // --- Validation ---
    //     $errors = $this->validateRegister($data);
    //     if (!empty($errors)) {
    //         return ['success' => false, 'errors' => $errors];
    //     }

    //     $name  = trim($data['name']);
    //     $email = strtolower(trim($data['email']));
    //     $phone = trim($data['phone'] ?? '');
    //     $pass  = $data['password'];

    //     // Email already exists?
    //     $existing = $this->db->queryOne(
    //         "SELECT id FROM users WHERE email = ?",
    //         [$email]
    //     );
    //     if ($existing) {
    //         return ['success' => false, 'errors' => ['email' => 'এই email আগে থেকেই registered।']];
    //     }

    //     // Password hash
    //     $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);

    //     // OTP generate
    //     $otp        = $this->generateOTP();
    //     $otp_expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    //     // Insert user
    //     $inserted = $this->db->execute(
    //         "INSERT INTO users 
    //             (role_id, name, email, phone, password_hash, otp, otp_expires_at, status)
    //          VALUES 
    //             (3, ?, ?, ?, ?, ?, ?, 'inactive')",
    //         [$name, $email, $phone, $hash, $otp, $otp_expiry]
    //     );

    //     if (!$inserted) {
    //         return ['success' => false, 'errors' => ['general' => 'Registration failed। আবার try করুন।']];
    //     }

    //     $userId = $this->db->lastInsertId();

    //     // Activity log
    //     $this->logActivity($userId, 'user.register', 'New user registered');

    //     // OTP send (email)
    //     $this->sendOTPEmail($email, $name, $otp);

    //     return [
    //         'success' => true,
    //         'message' => 'Registration সফল! আপনার email এ OTP পাঠানো হয়েছে।',
    //         'user_id' => $userId,
    //         'email'   => $email
    //     ];
    // }

    public function register(array $data): array {
    // --- Validation ---
        $errors = $this->validateRegister($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $name  = trim($data['name']);
        $email = strtolower(trim($data['email']));
        $phone = trim($data['phone'] ?? '');
        $pass  = $data['password'];
        
        // ✅ Role হ্যান্ডেল করা (এজেন্ট হলে ২, না হলে বায়ার হিসেবে ৩)
        // আপনার ডাটাবেসের roles টেবিল অনুযায়ী আইডি চেক করে নিবেন
        $role_name = $data['role'] ?? 'buyer';
        $role_id = ($role_name === 'agent') ? 2 : 3; 

        // Email already exists?
        $existing = $this->db->queryOne(
            "SELECT id FROM users WHERE email = ?",
            [$email]
        );
        if ($existing) {
            return ['success' => false, 'errors' => ['email' => 'এই email আগে থেকেই registered।']];
        }

        // Password hash
        $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);

        // OTP generate
        $otp        = $this->generateOTP();
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // ✅ Insert user (এলাহানে role_id ডাইনামিক করা হয়েছে)
        $inserted = $this->db->execute(
            "INSERT INTO users 
                (role_id, name, email, phone, password_hash, otp, otp_expires_at, status)
            VALUES 
                (?, ?, ?, ?, ?, ?, ?, 'inactive')",
            [$role_id, $name, $email, $phone, $hash, $otp, $otp_expiry]
        );

        if (!$inserted) {
            return ['success' => false, 'errors' => ['general' => 'Registration failed। আবার try করুন।']];
        }

        $userId = $this->db->lastInsertId();

        // Activity log
        $this->logActivity($userId, 'user.register', "New user registered as $role_name");

        // OTP send (email)
        $this->sendOTPEmail($email, $name, $otp);

        return [
            'success' => true,
            'message' => 'Registration সফল! আপনার email এ OTP পাঠানো হয়েছে।',
            'user_id' => $userId,
            'email'   => $email
        ];
    }



    // =========================================
    // ✅ OTP VERIFY
    // =========================================
    public function verifyOTP(string $email, string $otp): array {

        $email = strtolower(trim($email));
        $otp   = trim($otp);

        if (empty($otp)) {
            return ['success' => false, 'message' => 'OTP দিন।'];
        }

        $user = $this->db->queryOne(
            "SELECT id, name, otp, otp_expires_at, status 
             FROM users WHERE email = ?",
            [$email]
        );

        if (!$user) {
            return ['success' => false, 'message' => 'User পাওয়া যায়নি।'];
        }

        if ($user['status'] === 'active') {
            return ['success' => false, 'message' => 'এই account আগেই verified।'];
        }

        // OTP match?
        if ($user['otp'] !== $otp) {
            return ['success' => false, 'message' => 'OTP সঠিক নয়।'];
        }

        // OTP expired?
        if (strtotime($user['otp_expires_at']) < time()) {
            return ['success' => false, 'message' => 'OTP expired। নতুন OTP নিন।'];
        }

        // Activate account
        $this->db->execute(
            "UPDATE users 
             SET status = 'active', 
                 email_verified_at = NOW(), 
                 otp = NULL, 
                 otp_expires_at = NULL 
             WHERE email = ?",
            [$email]
        );

        $this->logActivity($user['id'], 'user.verified', 'Email verified via OTP');

        return [
            'success' => true,
            'message' => 'Email verify সফল! এখন login করুন।'
        ];
    }


    // =========================================
    // ✅ RESEND OTP
    // =========================================
    public function resendOTP(string $email): array {

        $email = strtolower(trim($email));

        $user = $this->db->queryOne(
            "SELECT id, name, status FROM users WHERE email = ?",
            [$email]
        );

        if (!$user) {
            return ['success' => false, 'message' => 'Email পাওয়া যায়নি।'];
        }

        if ($user['status'] === 'active') {
            return ['success' => false, 'message' => 'এই account আগেই verified।'];
        }

        $otp        = $this->generateOTP();
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $this->db->execute(
            "UPDATE users SET otp = ?, otp_expires_at = ? WHERE email = ?",
            [$otp, $otp_expiry, $email]
        );

        $this->sendOTPEmail($email, $user['name'], $otp);

        return [
            'success' => true,
            'message' => 'নতুন OTP পাঠানো হয়েছে।'
        ];
    }


    // =========================================
    // ✅ LOGIN
    // =========================================
    public function login(string $email, string $password, bool $remember = false): array {

        $email = strtolower(trim($email));

        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email ও Password দিন।'];
        }

        $user = $this->db->queryOne(
            "SELECT u.*, r.name AS role_name 
             FROM users u 
             JOIN roles r ON r.id = u.role_id 
             WHERE u.email = ?",
            [$email]
        );

        if (!$user) {
            return ['success' => false, 'message' => 'Email বা Password সঠিক নয়।'];
        }

        // Account active?
        if ($user['status'] === 'inactive') {
            return [
                'success'     => false,
                'message'     => 'আপনার email verify করুন।',
                'need_verify' => true,
                'email'       => $email
            ];
        }

        if ($user['status'] === 'banned') {
            return ['success' => false, 'message' => 'আপনার account ban করা হয়েছে।'];
        }

        // Password check
        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Email বা Password সঠিক নয়।'];
        }

        // Session set
        $this->setSession($user);

        // Remember Me
        if ($remember) {
            $this->setRememberMe($user['id']);
        }

        $this->logActivity($user['id'], 'user.login', 'User logged in');

        return [
            'success'  => true,
            'message'  => 'Login সফল! স্বাগতম, ' . $user['name'],
            'role'     => $user['role_name'],
            'redirect' => $this->getDashboardUrl($user['role_name'])
        ];
    }


    // =========================================
    // ✅ LOGOUT
    // =========================================
    public function logout(): void {

        if ($this->isLoggedIn()) {
            $this->logActivity($_SESSION['user_id'], 'user.logout', 'User logged out');
        }

        // Session destroy
        $_SESSION = [];
        session_destroy();

        // Remember me cookie clear
        if (isset($_COOKIE['remember_token'])) {
            $this->db->execute(
                "UPDATE users SET remember_token = NULL WHERE remember_token = ?",
                [$_COOKIE['remember_token']]
            );
            setcookie('remember_token', '', time() - 3600, '/');
        }

        header('Location: ' . APP_URL . '/index.php?page=login');
        exit;
    }


    // =========================================
    // ✅ FORGOT PASSWORD
    // =========================================
    public function forgotPassword(string $email): array {

        $email = strtolower(trim($email));

        $user = $this->db->queryOne(
            "SELECT id, name, status FROM users WHERE email = ?",
            [$email]
        );

        // Security: সব সময় same message (email enumeration prevent)
        if (!$user || $user['status'] !== 'active') {
            return [
                'success' => true,
                'message' => 'যদি email টি registered থাকে, OTP পাঠানো হবে।'
            ];
        }

        $otp        = $this->generateOTP();
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $this->db->execute(
            "UPDATE users SET otp = ?, otp_expires_at = ? WHERE email = ?",
            [$otp, $otp_expiry, $email]
        );

        $this->sendOTPEmail($email, $user['name'], $otp, 'reset');

        $this->logActivity($user['id'], 'user.forgot_password', 'Password reset OTP sent');

        return [
            'success' => true,
            'message' => 'Password reset OTP পাঠানো হয়েছে।',
            'email'   => $email
        ];
    }


    // =========================================
    // ✅ RESET PASSWORD
    // =========================================
    public function resetPassword(string $email, string $otp, string $newPassword): array {

        $email = strtolower(trim($email));

        // OTP verify first
        $user = $this->db->queryOne(
            "SELECT id, otp, otp_expires_at FROM users WHERE email = ?",
            [$email]
        );

        if (!$user) {
            return ['success' => false, 'message' => 'User পাওয়া যায়নি।'];
        }

        if ($user['otp'] !== trim($otp)) {
            return ['success' => false, 'message' => 'OTP সঠিক নয়।'];
        }

        if (strtotime($user['otp_expires_at']) < time()) {
            return ['success' => false, 'message' => 'OTP expired।'];
        }

        if (strlen($newPassword) < 6) {
            return ['success' => false, 'message' => 'Password কমপক্ষে 6 character হতে হবে।'];
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        $this->db->execute(
            "UPDATE users 
             SET password_hash = ?, otp = NULL, otp_expires_at = NULL 
             WHERE email = ?",
            [$hash, $email]
        );

        $this->logActivity($user['id'], 'user.reset_password', 'Password reset successful');

        return [
            'success' => true,
            'message' => 'Password সফলভাবে পরিবর্তন হয়েছে।'
        ];
    }


    // =========================================
    // ✅ CHECK LOGGED IN
    // =========================================
    public function isLoggedIn(): bool {

        if (isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {
            return true;
        }

        // Remember me cookie check
        if (isset($_COOKIE['remember_token'])) {
            return $this->loginByRememberToken($_COOKIE['remember_token']);
        }

        return false;
    }


    // =========================================
    // ✅ ROLE CHECK
    // =========================================
    public function hasRole(string|array $roles): bool {
        if (!$this->isLoggedIn()) return false;

        $userRole = $_SESSION['user_role'] ?? '';

        if (is_array($roles)) {
            return in_array($userRole, $roles);
        }

        return $userRole === $roles;
    }

    public function isAdmin(): bool  { return $this->hasRole('admin'); }
    public function isAgent(): bool  { return $this->hasRole('agent'); }
    public function isBuyer(): bool  { return $this->hasRole('buyer'); }


    // =========================================
    // ✅ GET CURRENT USER
    // =========================================
    public function currentUser(): ?array {
        if (!$this->isLoggedIn()) return null;

        return $this->db->queryOne(
            "SELECT u.id, u.name, u.email, u.phone, u.avatar, u.status,
                    r.name AS role
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.id = ?",
            [$_SESSION['user_id']]
        );
    }


    // =========================================
    // ✅ MIDDLEWARE — Page protect
    // =========================================
    public function requireLogin(string $redirect = 'login'): void {
        if (!$this->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . APP_URL . '/index.php?page=' . $redirect);
            exit;
        }
    }

    public function requireRole(string|array $roles): void {
        $this->requireLogin();
        if (!$this->hasRole($roles)) {
            header('Location: ' . APP_URL . '/index.php?page=home');
            exit;
        }
    }


    // =========================================
    // 🔒 PRIVATE HELPER METHODS
    // =========================================

    // Session set
    private function setSession(array $user): void {
        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role_name'];
        $_SESSION['user_avatar']= $user['avatar'];
        $_SESSION['logged_in']  = true;
        $_SESSION['login_time'] = time();
    }

    // Remember Me cookie
    private function setRememberMe(int $userId): void {
        $token   = bin2hex(random_bytes(32));
        $expires = time() + (30 * 24 * 60 * 60); // 30 days

        $this->db->execute(
            "UPDATE users SET remember_token = ? WHERE id = ?",
            [$token, $userId]
        );

        setcookie('remember_token', $token, $expires, '/', '', false, true);
    }

    // Login by remember token
    private function loginByRememberToken(string $token): bool {
        $user = $this->db->queryOne(
            "SELECT u.*, r.name AS role_name 
             FROM users u 
             JOIN roles r ON r.id = u.role_id
             WHERE u.remember_token = ? AND u.status = 'active'",
            [$token]
        );

        if (!$user) return false;

        $this->setSession($user);
        return true;
    }

    // OTP generate — 6 digit
    private function generateOTP(): string {
        return str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    // Send OTP Email (PHP mail — production এ SMTP use করবে)
    private function sendOTPEmail(string $email, string $name, string $otp, string $type = 'verify'): void {

        $subject = $type === 'reset'
            ? '[' . APP_NAME . '] Password Reset OTP'
            : '[' . APP_NAME . '] Email Verify OTP';

        $body = $type === 'reset'
            ? "হ্যালো $name,\n\nআপনার password reset OTP: $otp\n\nএটি 10 মিনিটের মধ্যে ব্যবহার করুন।\n\n" . APP_NAME
            : "হ্যালো $name,\n\nআপনার email verify OTP: $otp\n\nএটি 15 মিনিটের মধ্যে ব্যবহার করুন।\n\n" . APP_NAME;

        $headers = "From: no-reply@realestate.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // mail($email, $subject, $body, $headers);
        if (!@mail($email, $subject, $body, $headers)) {
            // এখানে চাইলে একটি লগ রাখতে পারেন যে ইমেইল পাঠানো যায়নি
            error_log("Failed to send email to $email");
        }

        // Debug mode তে OTP দেখাও
        if (APP_DEBUG) {
            error_log("OTP for $email: $otp");
        }
    }

    // Dashboard URL by role
    private function getDashboardUrl(string $role): string {
        return match($role) {
            'admin' => APP_URL . '/index.php?page=admin-dashboard',
            'agent' => APP_URL . '/index.php?page=agent-dashboard',
            default => APP_URL . '/index.php?page=buyer-dashboard',
        };
    }

    // Validate register input
    private function validateRegister(array $data): array {
        $errors = [];

        if (empty(trim($data['name'] ?? ''))) {
            $errors['name'] = 'নাম দিন।';
        } elseif (strlen($data['name']) < 3) {
            $errors['name'] = 'নাম কমপক্ষে 3 character হতে হবে।';
        }

        if (empty(trim($data['email'] ?? ''))) {
            $errors['email'] = 'Email দিন।';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'সঠিক Email দিন।';
        }

        if (empty($data['password'] ?? '')) {
            $errors['password'] = 'Password দিন।';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'Password কমপক্ষে 6 character হতে হবে।';
        }

        if (($data['password'] ?? '') !== ($data['confirm_password'] ?? '')) {
            $errors['confirm_password'] = 'Password match করেনি।';
        }

        return $errors;
    }

    // Activity log
    private function logActivity(?int $userId, string $action, string $details = ''): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $this->db->execute(
            "INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)",
            [$userId, $action, $details, $ip]
        );
    }

    // Clone বন্ধ
    private function __clone() {}
}