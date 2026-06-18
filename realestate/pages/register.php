<?php
// ১. ব্যাকএন্ড লজিক ব্লক (ফাইলের একদম শুরুতে থাকতে হবে)
require_once __DIR__ . '/../classes/Auth.php';

$auth = Auth::getInstance();

// অলরেডি লগইন থাকলে হোম পেজে পাঠিয়ে দেবে
if ($auth->isLoggedIn()) {
    header('Location: ' . APP_URL . '/index.php?page=home');
    exit;
}

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF টোকেন ভ্যালিডেশন
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token.');
    }

    // রেজিস্ট্রেশন ফাংশন কল
    $result = $auth->register($_POST);

    if ($result['success']) {
        $success = $result['message'];
        // ওটিপি ভেরিফিকেশন পেজে রিডাইরেক্ট
        header('Location: ' . APP_URL . '/index.php?page=verify-otp&email=' . urlencode($result['email']));
        exit;
    } else {
        // এরর মেসেজগুলো অ্যারেতে জমা হবে
        $errors = $result['errors'];
    }
}

// নতুন CSRF টোকেন তৈরি
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!-- ২. ৩ডি ফ্রন্টএন্ড এইচটিএমএল ডিজাইন পার্ট -->
<section class="auth-3d-section">
  <!-- ব্যাকগ্রাউন্ডে ৩ডি ডাইনামিক লাইٹنگ গ্লো -->
  <div class="glow-3d-orb-auth orb-auth-1"></div>
  <div class="glow-3d-orb-auth orb-auth-2"></div>
  <div class="auth-mesh-overlay"></div>

  <div class="container position-relative d-flex justify-content-center align-items-center min-vh-100 py-5">
    <div class="auth-card-3d" data-aos="zoom-in">

      <div class="auth-logo text-center mb-4">
        <h2 class="text-white fw-bold"><i class="bi bi-buildings-fill text-accent-3d me-2"></i><?= APP_NAME ?></h2>
        <p class="text-muted-3d"><?= __('নতুন account তৈরি করুন', 'Create your new account') ?></p>
      </div>

      <!-- জেনারেল এরর ডিসপ্লে -->
      <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger-3d mb-4"><?= htmlspecialchars($errors['general']) ?></div>
      <?php endif; ?>

      <form method="POST" action="" class="form-3d">
        <!-- হিডেন CSRF টোকেন ইনপুট -->
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <!-- Name -->
        <div class="input-block-3d-auth mb-3">
          <label class="form-label"><i class="bi bi-person-fill text-accent-3d me-1"></i> <?= __('পূর্ণ নাম', 'Full Name') ?></label>
          <input type="text" name="name" class="field-input-3d-auth <?= isset($errors['name']) ? 'is-invalid-3d' : '' ?>"
                 value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="<?= __('আপনার নাম', 'Your name') ?>" required>
          <?php if (isset($errors['name'])): ?>
            <div class="invalid-feedback-3d"><?= $errors['name'] ?></div>
          <?php endif; ?>
        </div>

        <!-- Email -->
        <div class="input-block-3d-auth mb-3">
          <label class="form-label"><i class="bi bi-envelope-at-fill text-accent-3d me-1"></i> <?= __('ইমেইল', 'Email Address') ?></label>
          <input type="email" name="email" class="field-input-3d-auth <?= isset($errors['email']) ? 'is-invalid-3d' : '' ?>"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="example@email.com" required>
          <?php if (isset($errors['email'])): ?>
            <div class="invalid-feedback-3d"><?= $errors['email'] ?></div>
          <?php endif; ?>
        </div>

        <!-- Phone -->
        <div class="input-block-3d-auth mb-3">
          <label class="form-label"><i class="bi bi-telephone-fill text-accent-3d me-1"></i> <?= __('ফোন নম্বর', 'Phone Number') ?></label>
          <input type="text" name="phone" class="field-input-3d-auth"
                 value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="01XXXXXXXXX" required>
        </div>

        <!-- Account Type Selection (৩ডি রেডিও বক্স) -->
        <div class="input-block-3d-auth mb-4 border-3d p-3 rounded">
            <label class="form-label d-block mb-3"><i class="bi bi-people-fill text-accent-3d me-1"></i> <?= __('আপনি কি হিসেবে যোগ দিতে চান?', 'Join As') ?></label>
            <div class="d-flex gap-4">
                <div class="form-check-3d">
                    <input class="radio-input-3d" type="radio" name="role" id="roleBuyer" value="buyer" 
                           <?= ($_POST['role'] ?? 'buyer') === 'buyer' ? 'checked' : '' ?>>
                    <label class="radio-label-3d" for="roleBuyer"><?= __('ক্রেতা (Buyer)', 'Buyer') ?></label>
                </div>
                <div class="form-check-3d">
                    <input class="radio-input-3d" type="radio" name="role" id="roleAgent" value="agent"
                           <?= ($_POST['role'] ?? '') === 'agent' ? 'checked' : '' ?>>
                    <label class="radio-label-3d" for="roleAgent"><?= __('এজেন্ট (Agent)', 'Agent') ?></label>
                </div>
            </div>
            <?php if (isset($errors['role'])): ?>
                <div class="invalid-feedback-3d d-block mt-2"><?= $errors['role'] ?></div>
            <?php endif; ?>
        </div>

        <!-- Password -->
        <div class="input-block-3d-auth mb-3">
          <label class="form-label"><i class="bi bi-lock-fill text-accent-3d me-1"></i> <?= __('Password', 'Password') ?></label>
          <input type="password" name="password" 
                 class="field-input-3d-auth <?= isset($errors['password']) ? 'is-invalid-3d' : '' ?>"
                 placeholder="<?= __('কমপক্ষে 6 character', 'Minimum 6 characters') ?>" required>
          <?php if (isset($errors['password'])): ?>
            <div class="invalid-feedback-3d"><?= $errors['password'] ?></div>
          <?php endif; ?>
        </div>

        <!-- Confirm Password -->
        <div class="input-block-3d-auth mb-4">
          <label class="form-label"><i class="bi bi-shield-lock-fill text-accent-3d me-1"></i> <?= __('Password নিশ্চিত করুন', 'Confirm Password') ?></label>
          <input type="password" name="confirm_password"
                 class="field-input-3d-auth <?= isset($errors['confirm_password']) ? 'is-invalid-3d' : '' ?>"
                 placeholder="<?= __('Password আবার দিন', 'Repeat password') ?>" required>
          <?php if (isset($errors['confirm_password'])): ?>
            <div class="invalid-feedback-3d"><?= $errors['confirm_password'] ?></div>
          <?php endif; ?>
        </div>

        <button type="submit" class="auth-submit-btn-3d w-100"><?= __('Register করুন', 'Register Now') ?></button>
      </form>

      <p class="text-center mt-4 mb-0 text-muted-3d">
        <?= __('আগে থেকেই account আছে?', 'Already have an account?') ?> 
        <a href="?page=login" class="text-accent-3d text-decoration-none fw-bold ms-1"><?= __('Login করুন', 'Login here') ?></a>
      </p>

    </div>
  </div>
</section>


<!-- ============================================
     REGISTER 3D INLINE CSS
============================================ -->

