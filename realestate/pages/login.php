<?php
require_once __DIR__ . '/../classes/Auth.php';

$auth = Auth::getInstance();

if ($auth->isLoggedIn()) {
    header('Location: ' . APP_URL . '/index.php?page=home');
    exit;
}

// ল্যাঙ্গুয়েজ সেটআপ (Default: 'bn') - হেডার থেকে আসা সেশন রিড করবে
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'] === 'en' ? 'en' : 'bn';
    header('Location: ' . APP_URL . '/index.php?page=login'); // URL ক্লিন রাখার জন্য
    exit;
}
$lang = $_SESSION['lang'] ?? 'bn';

// অনুবাদ ডিকশনারি (Translation Dictionary)
$trans = [
    'bn' => [
        'subtitle'    => 'আপনার account এ login করুন',
        'email_lbl'   => 'Email Address',
        'pass_lbl'    => 'Password',
        'pass_ph'     => 'আপনার password',
        'remember'    => 'মনে রাখো (30 দিন)',
        'forgot_pass' => 'Password ভুলে গেছেন?',
        'login_btn'   => 'Login করুন',
        'new_user'    => 'নতুন user?',
        'register'    => 'Register করুন',
    ],
    'en' => [
        'subtitle'    => 'Login to your account',
        'email_lbl'   => 'Email Address',
        'pass_lbl'    => 'Password',
        'pass_ph'     => 'Enter your password',
        'remember'    => 'Remember me (30 days)',
        'forgot_pass' => 'Forgot Password?',
        'login_btn'   => 'Login',
        'new_user'    => 'New user?',
        'register'    => 'Register here',
    ]
];

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
        if (!empty($result['need_verify'])) {
            header('Location: ' . APP_URL . '/index.php?page=verify-otp&email=' . urlencode($result['email']));
            exit;
        }
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!-- গুগল ফন্ট ইমপোর্ট (Hind Siliguri এবং Inter) -->
<link href="https://googleapis.com" rel="stylesheet">

<div class="auth-3d-section d-flex align-items-center justify-content-center <?= $lang === 'en' ? 'font-en' : 'font-bn' ?>">
  
  <!-- ব্যাকগ্রাউন্ড গ্লো ইফেক্ট -->
  <div class="glow-3d-orb-auth orb-auth-1"></div>
  <div class="glow-3d-orb-auth orb-auth-2"></div>
  <div class="auth-mesh-overlay"></div>

  <!-- মেইন ৩D কার্ড -->
  <div class="auth-card-3d">
    <div class="text-center mb-4">
      <h2 style="color: #fff; font-weight: 700; margin-bottom: 8px;">🏠 <?= APP_NAME ?></h2>
      <p class="text-muted-3d"><?= $trans[$lang]['subtitle'] ?></p>
    </div>

    <!-- অ্যালার্ট মেসেজসমূহ -->
    <?php if ($error): ?>
      <div class="alert-danger-3d mb-3"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert-danger-3d mb-3" style="background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.2); color: #10b981;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- লগইন ফর্ম -->
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

      <div class="input-block-3d-auth mb-3">
        <label><?= $trans[$lang]['email_lbl'] ?></label>
        <input type="email" name="email" class="field-input-3d-auth <?= $error ? 'is-invalid-3d' : '' ?>"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               placeholder="example@email.com" required>
      </div>

      <div class="input-block-3d-auth mb-3">
        <label><?= $trans[$lang]['pass_lbl'] ?></label>
        <input type="password" name="password" class="field-input-3d-auth"
               placeholder="<?= $trans[$lang]['pass_ph'] ?>" required>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-4 layout-fix">
        <label class="form-check-3d">
          <input type="checkbox" name="remember" class="radio-input-3d" id="remember">
          <span class="radio-label-3d"><?= $trans[$lang]['remember'] ?></span>
        </label>
        <a href="?page=forgot-password" style="color: #C5A059; text-decoration: none; font-size: 14px;"><?= $trans[$lang]['forgot_pass'] ?></a>
      </div>

      <button type="submit" class="auth-submit-btn-3d w-100"><?= $trans[$lang]['login_btn'] ?></button>
    </form>

    <p class="text-center mt-4 text-muted-3d" style="margin-bottom: 0;">
      <?= $trans[$lang]['new_user'] ?> <a href="?page=register" style="color: #C5A059; text-decoration: none; font-weight: 500;"><?= $trans[$lang]['register'] ?></a>
    </p>
  </div>
</div>

