<?php

namespace App\Services;

use App\Models\UserModel;
use App\Helpers\Logger;

/**
 * AuthService – All authentication business logic lives here.
 * Controllers stay thin; this service does the heavy lifting.
 */
class AuthService
{
    private UserModel $users;

    // Rate limiting: max login attempts per IP per window
    private const MAX_ATTEMPTS    = 5;
    private const LOCKOUT_SECONDS = 900; // 15 min

    public function __construct()
    {
        $this->users = new UserModel();
    }

    // ------------------------------------------------------------------ //
    //  Registration                                                        //
    // ------------------------------------------------------------------ //

    /**
     * Register a new user and return [success, message, userId].
     */
    public function register(array $data): array
    {
        // Check email uniqueness
        if ($this->users->findByEmail($data['email'])) {
            return [false, 'Email address is already registered.', null];
        }

        try {
            $userId = $this->users->register($data);
            $otp    = $this->users->setOtp((int) $userId);

            Logger::activity('user_registered', (int) $userId, ['email' => $data['email']]);

            return [true, 'Registration successful. Check your email for the OTP.', $userId, $otp];
        } catch (\Throwable $e) {
            Logger::error('Registration failed: ' . $e->getMessage());
            return [false, 'Registration failed. Please try again.', null, null];
        }
    }

    // ------------------------------------------------------------------ //
    //  Login                                                               //
    // ------------------------------------------------------------------ //

    /**
     * Attempt login. Returns [success, message, userData|null].
     */
    public function login(string $email, string $password, string $ip): array
    {
        // Rate limit check
        if ($this->isLockedOut($ip)) {
            return [false, 'Too many failed attempts. Try again in 15 minutes.', null];
        }

        $user = $this->users->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->incrementAttempts($ip);
            Logger::security('failed_login', ['email' => $email, 'ip' => $ip]);
            return [false, 'Invalid email or password.', null];
        }

        if (!$user['is_active']) {
            return [false, 'Your account has been suspended. Contact support.', null];
        }

        if (!$user['email_verified_at']) {
            return [false, 'Please verify your email first. Check your inbox for the OTP.', null, 'unverified', $user['id']];
        }

        // Successful login
        $this->clearAttempts($ip);
        $this->users->recordLogin((int) $user['id'], $ip);
        Logger::activity('user_login', (int) $user['id'], ['ip' => $ip]);

        // Remove sensitive fields before storing in session
        unset($user['password'], $user['otp_code'], $user['remember_token']);

        return [true, 'Welcome back, ' . $user['name'] . '!', $user];
    }

    // ------------------------------------------------------------------ //
    //  Remember Me                                                         //
    // ------------------------------------------------------------------ //

    public function setRememberCookie(int $userId): void
    {
        $token = $this->users->setRememberToken($userId);
        setcookie(
            'remember_token',
            $token,
            [
                'expires'  => time() + (30 * 24 * 3600), // 30 days
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure'   => isset($_SERVER['HTTPS']),
            ]
        );
    }

    public function loginViaRememberToken(string $token): ?array
    {
        $user = $this->users->findByRememberToken($token);
        if (!$user) return null;

        unset($user['password'], $user['otp_code'], $user['remember_token']);
        return $user;
    }

    public function forgetRemember(int $userId): void
    {
        $this->users->clearRememberToken($userId);
        setcookie('remember_token', '', time() - 3600, '/');
    }

    // ------------------------------------------------------------------ //
    //  OTP                                                                 //
    // ------------------------------------------------------------------ //

    public function verifyOtp(int $userId, string $otp): array
    {
        $ok = $this->users->verifyOtp($userId, $otp);
        if (!$ok) {
            return [false, 'Invalid or expired OTP. Please try again.'];
        }
        Logger::activity('email_verified', $userId);
        return [true, 'Email verified successfully. You can now log in.'];
    }

    public function resendOtp(string $email): array
    {
        $result = $this->users->resendOtp($email);
        if (!$result) {
            return [false, 'Email not found or already verified.', null, null];
        }
        return [true, 'A new OTP has been sent to your email.', $result['user'], $result['otp']];
    }

    // ------------------------------------------------------------------ //
    //  Password Reset                                                      //
    // ------------------------------------------------------------------ //

    public function forgotPassword(string $email): array
    {
        $result = $this->users->setPasswordResetOtp($email);
        if (!$result) {
            // Return success anyway to prevent email enumeration
            return [true, 'If that email exists, a reset OTP has been sent.', null, null];
        }
        return [true, 'Password reset OTP sent to your email.', $result['user'], $result['otp']];
    }

    public function resetPassword(int $userId, string $otp, string $newPassword): array
    {
        $user = $this->users->find($userId);
        if (!$user) return [false, 'Invalid request.'];

        if ($user['otp_code'] !== $otp) return [false, 'Invalid OTP.'];
        if (strtotime($user['otp_expires_at']) < time()) return [false, 'OTP has expired.'];

        $this->users->resetPassword($userId, $newPassword);
        $this->users->verifyOtp($userId, $otp); // clears OTP fields
        Logger::activity('password_reset', $userId);

        return [true, 'Password reset successfully. Please log in.'];
    }

    // ------------------------------------------------------------------ //
    //  Session                                                             //
    // ------------------------------------------------------------------ //

    public function createSession(array $user): void
    {
        // Regenerate session ID to prevent fixation attacks
        session_regenerate_id(true);
        $_SESSION['user']       = $user;
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['login_time'] = time();
    }

    public function destroySession(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public function refreshSession(): void
    {
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$userId) return;

        $user = $this->users->findWithRole((int) $userId);
        if (!$user) return;

        unset($user['password'], $user['otp_code'], $user['remember_token']);
        $_SESSION['user'] = $user;
    }

    // ------------------------------------------------------------------ //
    //  Rate Limiting (file-based, Redis-upgradeable)                      //
    // ------------------------------------------------------------------ //

    private function attemptKey(string $ip): string
    {
        return ROOT_PATH . '/storage/cache/login_' . md5($ip) . '.json';
    }

    private function isLockedOut(string $ip): bool
    {
        $file = $this->attemptKey($ip);
        if (!file_exists($file)) return false;

        $data = json_decode(file_get_contents($file), true);
        if (!$data) return false;

        // Expired window
        if (time() > $data['reset_at']) {
            unlink($file);
            return false;
        }

        return $data['attempts'] >= self::MAX_ATTEMPTS;
    }

    private function incrementAttempts(string $ip): void
    {
        $file = $this->attemptKey($ip);
        $data = file_exists($file)
            ? json_decode(file_get_contents($file), true)
            : ['attempts' => 0, 'reset_at' => time() + self::LOCKOUT_SECONDS];

        $data['attempts']++;
        file_put_contents($file, json_encode($data));
    }

    private function clearAttempts(string $ip): void
    {
        $file = $this->attemptKey($ip);
        if (file_exists($file)) unlink($file);
    }
}
