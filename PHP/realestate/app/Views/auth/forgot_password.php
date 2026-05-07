<?php $csrf = $_SESSION['_csrf_token'] ?? ''; ?>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-card__header">
            <div class="otp-icon" style="background:#fef3c7;color:#d97706">
                <i class="fa-solid fa-lock-open"></i>
            </div>
            <h1 class="auth-card__title">Forgot Password</h1>
            <p class="auth-card__sub">Enter your email and we'll send a reset OTP</p>
        </div>

        <form method="POST" action="/realestate/public/auth/forgot-password" novalidate>
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control"
                       placeholder="you@example.com" autofocus required>
            </div>

            <button type="submit" class="btn btn--primary btn--block">
                <i class="fa-solid fa-paper-plane"></i> Send Reset OTP
            </button>
        </form>

        <p class="auth-card__footer">
            Remembered it? <a href="/realestate/public/auth/login">Sign in</a>
        </p>
    </div>
</div>
