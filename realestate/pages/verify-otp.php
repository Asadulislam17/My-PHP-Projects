<?php
require_once __DIR__ . '/../classes/Auth.php';

$auth  = Auth::getInstance();
$email = $_GET['email'] ?? '';
$error = '';
$success = '';

if (empty($email)) {
    header('Location: ' . APP_URL . '/index.php?page=register');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? 'verify';

    if ($action === 'resend') {
        $result = $auth->resendOTP($email);
        $success = $result['message'];
    } else {
        $result = $auth->verifyOTP($email, $_POST['otp'] ?? '');
        if ($result['success']) {
            header('Location: ' . APP_URL . '/index.php?page=login&msg=' . urlencode($result['message']));
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>

<div class="auth-wrapper">
  <div class="auth-card text-center">

    <div class="auth-logo">
      <h2>📧 OTP Verify</h2>
      <p><strong><?= htmlspecialchars($email) ?></strong> এ OTP পাঠানো হয়েছে</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="action" value="verify">

      <div class="mb-3">
        <label class="form-label">6-digit OTP দিন</label>
        <input type="text" name="otp" class="form-control form-control-lg text-center"
               maxlength="6" placeholder="_ _ _ _ _ _"
               style="letter-spacing: 10px; font-size: 1.5rem;" required>
      </div>

      <button type="submit" class="btn btn-primary w-100 mb-2">Verify করুন</button>
    </form>

    <form method="POST">
      <input type="hidden" name="action" value="resend">
      <button type="submit" class="btn btn-outline-secondary w-100">
        OTP আসেনি? আবার পাঠান
      </button>
    </form>

  </div>
</div>