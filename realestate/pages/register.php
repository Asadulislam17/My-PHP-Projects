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
<style>
.auth-3d-section {
  position: relative;
  background: #090d16;
  min-height: 100vh;
  overflow: hidden;
  font-family: 'Hind Siliguri', sans-serif;
}
.glow-3d-orb-auth {
  position: absolute;
  border-radius: 50%;
  filter: blur(140px);
  opacity: 0.12;
  z-index: 1;
}
.orb-auth-1 { width: 400px; height: 400px; background: #C5A059; top: -10%; left: -10%; }
.orb-auth-2 { width: 450px; height: 450px; background: #3b82f6; bottom: -10%; right: -10%; }
.auth-mesh-overlay {
  position: absolute;
  inset: 0;
  background-image: linear-gradient(rgba(255,255,255,0.01) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.01) 1px, transparent 1px);
  background-size: 30px 30px;
  z-index: 2;
}
.auth-card-3d {
  background: rgba(15, 23, 42, 0.55);
  border: 1px solid rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(25px);
  border-radius: 28px;
  padding: 40px;
  width: 100%;
  max-width: 520px;
  box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255,255,255,0.1);
  transform: perspective(1000px) rotateX(2deg);
  transition: transform 0.5s ease;
  z-index: 10;
}
.auth-card-3d:hover {
  transform: perspective(1000px) rotateX(0deg) translateY(-5px);
}
.text-muted-3d { color: #94a3b8; font-size: 14px; }
.input-block-3d-auth label { color: #94a3b8; font-size: 14px; margin-bottom: 8px; display: block; }
.field-input-3d-auth {
  width: 100%;
  background: rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.05);
  padding: 12px 16px;
  border-radius: 12px;
  color: #fff;
  outline: none;
  transition: all 0.3s ease;
}
.field-input-3d-auth:focus {
  border-color: #C5A059;
  background: rgba(0, 0, 0, 0.4);
  box-shadow: 0 0 0 4px rgba(197, 160, 89, 0.15);
}
.border-3d { border: 1px solid rgba(255, 255, 255, 0.06) !important; background: rgba(0,0,0,0.15) !important; }
.form-check-3d { display: inline-flex; align-items: center; gap: 8px; cursor: pointer; }
.radio-input-3d { appearance: none; width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.2); border-radius: 50%; outline: none; transition: all 0.2s; cursor: pointer; position: relative; }
.radio-input-3d:checked { border-color: #C5A059; background: #C5A059; box-shadow: 0 0 10px rgba(197, 160, 89, 0.5); }
.radio-input-3d:checked::after { content: ''; width: 6px; height: 6px; background: #0f172a; border-radius: 50%; position: absolute; top: 4px; left: 4px; }
.radio-label-3d { color: #fff; font-size: 14px; cursor: pointer; }
.auth-submit-btn-3d {
  background: linear-gradient(135deg, #fff 0%, #e2e8f0 100%);
  border: none; padding: 14px; border-radius: 12px; font-weight: 700; color: #0f172a;
  box-shadow: 0 15px 30px rgba(255,255,255,0.05);
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  cursor: pointer;
}
.auth-submit-btn-3d:hover {
  background: linear-gradient(135deg, #C5A059 0%, #a17f3f 100%);
  color: #fff; transform: translateY(-2px); box-shadow: 0 12px 25px rgba(197, 160, 89, 0.4);
}
.is-invalid-3d { border-color: #ef4444 !important; background: rgba(239, 64, 64, 0.05) !important; }
.invalid-feedback-3d { color: #ef4444; font-size: 12px; margin-top: 6px; display: block; }
.alert-danger-3d { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; padding: 12px; border-radius: 12px; font-size: 14px; }
</style>
