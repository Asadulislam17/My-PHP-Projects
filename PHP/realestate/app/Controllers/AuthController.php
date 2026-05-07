<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Mail\Mailer;

/**
 * AuthController – Handles all authentication routes.
 *
 * Routes (web.php):
 *   GET  /auth/register            → registerForm()
 *   POST /auth/register            → register()
 *   GET  /auth/login               → loginForm()
 *   POST /auth/login               → login()
 *   GET  /auth/logout              → logout()
 *   GET  /auth/verify-otp          → verifyOtpForm()
 *   POST /auth/verify-otp          → verifyOtp()
 *   POST /auth/resend-otp          → resendOtp()
 *   GET  /auth/forgot-password     → forgotForm()
 *   POST /auth/forgot-password     → forgot()
 *   GET  /auth/reset-password      → resetForm()
 *   POST /auth/reset-password      → reset()
 */
class AuthController extends BaseController
{
    private AuthService $authService;
    private Mailer      $mailer;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->authService = new AuthService();
        $this->mailer      = new Mailer();
    }

    // ------------------------------------------------------------------ //
    //  Register                                                            //
    // ------------------------------------------------------------------ //

    public function registerForm(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect($this->dashboardUrl());
        }

        $this->view('auth.register', [
            'title'  => 'Create Account',
            'errors' => $_SESSION['_validation_errors'] ?? [],
            'old'    => $_SESSION['_old_input'] ?? [],
        ]);
        unset($_SESSION['_validation_errors'], $_SESSION['_old_input']);
    }

    public function register(): void
    {
        $this->verifyCsrf();

        $data = $this->validate([
            'name'             => 'required|min:2|max:150',
            'email'            => 'required|email|max:191',
            'phone'            => 'max:20',
            'password'         => 'required|min:8|max:100',
            'password_confirm' => 'required',
            'role'             => 'in:buyer,agent',
        ]);

        // Confirm passwords match
        if ($data['password'] !== $data['password_confirm']) {
            $_SESSION['_validation_errors']['password_confirm'] = ['Passwords do not match.'];
            $_SESSION['_old_input'] = $data;
            $this->redirect('/auth/register');
            return;
        }

        [$ok, $message, $userId, $otp] = $this->authService->register($data);

        if (!$ok) {
            $this->flash('error', $message);
            $this->redirect('/auth/register');
            return;
        }

        // Send OTP email
        $this->mailer->sendOtp($data['email'], $data['name'], $otp);

        // Store pending verification in session
        $_SESSION['_pending_verify'] = ['user_id' => $userId, 'email' => $data['email']];

        $this->flash('success', $message);
        $this->redirect('/auth/verify-otp');
    }

    // ------------------------------------------------------------------ //
    //  OTP Verification                                                    //
    // ------------------------------------------------------------------ //

    public function verifyOtpForm(): void
    {
        if (!isset($_SESSION['_pending_verify'])) {
            $this->redirect('/auth/login');
            return;
        }

        $this->view('auth.verify_otp', [
            'title' => 'Verify Your Email',
            'email' => $_SESSION['_pending_verify']['email'] ?? '',
        ]);
    }

    public function verifyOtp(): void
    {
        $this->verifyCsrf();

        if (!isset($_SESSION['_pending_verify'])) {
            $this->redirect('/auth/login');
            return;
        }

        $userId = (int) $_SESSION['_pending_verify']['user_id'];
        $otp    = trim($this->request->post('otp', ''));

        [$ok, $message] = $this->authService->verifyOtp($userId, $otp);

        if (!$ok) {
            $this->flash('error', $message);
            $this->redirect('/auth/verify-otp');
            return;
        }

        unset($_SESSION['_pending_verify']);
        $this->flash('success', $message);
        $this->redirect('/auth/login');
    }

    public function resendOtp(): void
    {
        $this->verifyCsrf();

        $email = $_SESSION['_pending_verify']['email']
            ?? $this->request->post('email', '');

        [$ok, $message, $user, $otp] = $this->authService->resendOtp($email);

        if ($ok && $user) {
            $this->mailer->sendOtp($user['email'], $user['name'], $otp);
        }

        $this->flash($ok ? 'success' : 'error', $message);
        $this->redirect('/auth/verify-otp');
    }

    // ------------------------------------------------------------------ //
    //  Login                                                               //
    // ------------------------------------------------------------------ //

    public function loginForm(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect($this->dashboardUrl());
        }

        $this->view('auth.login', [
            'title'  => 'Sign In',
            'errors' => $_SESSION['_validation_errors'] ?? [],
            'old'    => $_SESSION['_old_input'] ?? [],
        ]);
        unset($_SESSION['_validation_errors'], $_SESSION['_old_input']);
    }

    public function login(): void
    {
        $this->verifyCsrf();

        $data = $this->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $ip     = $this->request->ip();
        $result = $this->authService->login($data['email'], $data['password'], $ip);

        [$ok, $message, $user] = $result;
        $extra = $result[3] ?? null;

        if (!$ok) {
            // Email not verified – redirect to OTP page
            if ($extra === 'unverified') {
                $_SESSION['_pending_verify'] = [
                    'user_id' => $result[4],
                    'email'   => $data['email'],
                ];
                $this->flash('warning', $message);
                $this->redirect('/auth/verify-otp');
                return;
            }

            $this->flash('error', $message);
            $this->redirect('/auth/login');
            return;
        }

        // Create session
        $this->authService->createSession($user);

        // Remember me
        if ($this->request->post('remember')) {
            $this->authService->setRememberCookie((int) $user['id']);
        }

        $this->flash('success', $message);

        // Redirect to intended URL or dashboard
        $intended = $_SESSION['_intended'] ?? null;
        unset($_SESSION['_intended']);

        $this->redirect($intended ?? $this->dashboardUrl($user['role']));
    }

    // ------------------------------------------------------------------ //
    //  Logout                                                              //
    // ------------------------------------------------------------------ //

    public function logout(): void
    {
        $userId = $_SESSION['user']['id'] ?? null;
        if ($userId) {
            $this->authService->forgetRemember((int) $userId);
        }
        $this->authService->destroySession();
        $this->redirect('/auth/login');
    }

    // ------------------------------------------------------------------ //
    //  Forgot Password                                                     //
    // ------------------------------------------------------------------ //

    public function forgotForm(): void
    {
        $this->view('auth.forgot_password', ['title' => 'Forgot Password']);
    }

    public function forgot(): void
    {
        $this->verifyCsrf();

        $data = $this->validate(['email' => 'required|email']);

        [$ok, $message, $user, $otp] = $this->authService->forgotPassword($data['email']);

        if ($ok && $user && $otp) {
            $this->mailer->sendPasswordResetOtp($user['email'], $user['name'], $otp);
            $_SESSION['_reset_user_id'] = $user['id'];
        }

        $this->flash('success', $message); // Always success to prevent email enumeration
        $this->redirect('/auth/reset-password');
    }

    public function resetForm(): void
    {
        if (!isset($_SESSION['_reset_user_id'])) {
            $this->redirect('/auth/forgot-password');
            return;
        }
        $this->view('auth.reset_password', ['title' => 'Reset Password']);
    }

    public function reset(): void
    {
        $this->verifyCsrf();

        if (!isset($_SESSION['_reset_user_id'])) {
            $this->redirect('/auth/forgot-password');
            return;
        }

        $data = $this->validate([
            'otp'              => 'required|min:6|max:6',
            'password'         => 'required|min:8',
            'password_confirm' => 'required',
        ]);

        if ($data['password'] !== $data['password_confirm']) {
            $this->flash('error', 'Passwords do not match.');
            $this->redirect('/auth/reset-password');
            return;
        }

        $userId = (int) $_SESSION['_reset_user_id'];
        [$ok, $message] = $this->authService->resetPassword($userId, $data['otp'], $data['password']);

        if (!$ok) {
            $this->flash('error', $message);
            $this->redirect('/auth/reset-password');
            return;
        }

        unset($_SESSION['_reset_user_id']);
        $this->flash('success', $message);
        $this->redirect('/auth/login');
    }

    // ------------------------------------------------------------------ //
    //  Helpers                                                             //
    // ------------------------------------------------------------------ //

    private function dashboardUrl(?string $role = null): string
    {
        $role = $role ?? ($_SESSION['user']['role'] ?? 'buyer');
        return match ($role) {
            'admin' => '/admin/dashboard',
            'agent' => '/agent/dashboard',
            default => '/buyer/dashboard',
        };
    }
}
