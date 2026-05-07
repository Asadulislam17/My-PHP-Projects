<?php
require_once __DIR__ . '/../classes/Auth.php';

$auth = Auth::getInstance();

if ($auth->isLoggedIn()) {
    header('Location: ' . APP_URL . '/index.php?page=home');
    exit;
}

$error   = '';
$success = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token.');
    }

    $result = $auth->login(
        $_POST['email']    ?? '',
        $_POST['password'] ?? '',
        isset($_POST['remember'])
    );

    if ($result['success']) {
        $redirect = $_SESSION['redirect_after_login'] ?? $result['redirect'];
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
        exit;
    } else {
        $error = $result['message'];
        // Email verify দরকার হলে OTP page এ নিয়ে যাও
        if (!empty($result['need_verify'])) {
            header('Location: ' . APP_URL . '/index.php?page=verify-otp&email=' . urlencode($result['email']));
            exit;
        }
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<div class="auth-wrapper">
  <div class="auth-card">

    <div class="auth-logo">
      <h2>🏠 <?= APP_NAME ?></h2>
      <p>আপনার account এ login করুন</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               placeholder="example@email.com" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control"
               placeholder="আপনার password" required>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check">
          <input type="checkbox" name="remember" class="form-check-input" id="remember">
          <label class="form-check-label" for="remember">মনে রাখো (30 দিন)</label>
        </div>
        <a href="?page=forgot-password">Password ভুলে গেছেন?</a>
      </div>

      <button type="submit" class="btn btn-primary w-100">Login করুন</button>
    </form>

    <p class="text-center mt-3">
      নতুন user? <a href="?page=register">Register করুন</a>
    </p>

  </div>
</div>