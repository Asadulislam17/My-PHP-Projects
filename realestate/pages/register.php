<?php
require_once __DIR__ . '/../classes/Auth.php';

$auth = Auth::getInstance();

// Already logged in?
if ($auth->isLoggedIn()) {
    header('Location: ' . APP_URL . '/index.php?page=home');
    exit;
}

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token.');
    }

    $result = $auth->register($_POST);

    if ($result['success']) {
        $success = $result['message'];
        // OTP page এ পাঠাও
        header('Location: ' . APP_URL . '/index.php?page=verify-otp&email=' . urlencode($result['email']));
        exit;
    } else {
        $errors = $result['errors'];
    }
}

// CSRF token generate
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<div class="auth-wrapper">
  <div class="auth-card">

    <div class="auth-logo text-center mb-4">
      <h2>🏠 <?= APP_NAME ?></h2>
      <p class="text-muted">নতুন account তৈরি করুন</p>
    </div>

    <?php if (!empty($errors['general'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

      <!-- Name -->
      <div class="mb-3">
        <label class="form-label fw-bold">পূর্ণ নাম</label>
        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="আপনার নাম">
        <?php if (isset($errors['name'])): ?>
          <div class="invalid-feedback"><?= $errors['name'] ?></div>
        <?php endif; ?>
      </div>

      <!-- Email -->
      <div class="mb-3">
        <label class="form-label fw-bold">Email</label>
        <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="example@email.com">
        <?php if (isset($errors['email'])): ?>
          <div class="invalid-feedback"><?= $errors['email'] ?></div>
        <?php endif; ?>
      </div>

      <!-- Phone -->
      <div class="mb-3">
        <label class="form-label fw-bold">ফোন নম্বর</label>
        <input type="text" name="phone" class="form-control"
               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="01XXXXXXXXX">
      </div>

      <!-- Account Type (Role) Selection - Added Here -->
      <div class="mb-3 border p-3 rounded bg-light">
          <label class="form-label fw-bold">আপনি কি হিসেবে যোগ দিতে চান?</label>
          <div class="d-flex gap-4">
              <div class="form-check">
                  <input class="form-check-input" type="radio" name="role" id="roleBuyer" value="buyer" 
                         <?= ($_POST['role'] ?? 'buyer') === 'buyer' ? 'checked' : '' ?>>
                  <label class="form-check-label" for="roleBuyer">ক্রেতা (Buyer)</label>
              </div>
              <div class="form-check">
                  <input class="form-check-input" type="radio" name="role" id="roleAgent" value="agent"
                         <?= ($_POST['role'] ?? '') === 'agent' ? 'checked' : '' ?>>
                  <label class="form-check-label" for="roleAgent">এজেন্ট (Agent)</label>
              </div>
          </div>
          <?php if (isset($errors['role'])): ?>
              <div class="text-danger small mt-1"><?= $errors['role'] ?></div>
          <?php endif; ?>
      </div>

      <!-- Password -->
      <div class="mb-3">
        <label class="form-label fw-bold">Password</label>
        <input type="password" name="password" 
               class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
               placeholder="কমপক্ষে 6 character">
        <?php if (isset($errors['password'])): ?>
          <div class="invalid-feedback"><?= $errors['password'] ?></div>
        <?php endif; ?>
      </div>

      <!-- Confirm Password -->
      <div class="mb-3">
        <label class="form-label fw-bold">Password নিশ্চিত করুন</label>
        <input type="password" name="confirm_password"
               class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
               placeholder="Password আবার দিন">
        <?php if (isset($errors['confirm_password'])): ?>
          <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Register করুন</button>
    </form>

    <p class="text-center mt-3 text-muted">
      আগে থেকেই account আছে? <a href="?page=login" class="text-decoration-none">Login করুন</a>
    </p>

  </div>
</div>
