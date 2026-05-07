<!-- ফর্মের ঠিক শুরুতে বা ভেতরে যেকোনো জায়গায় এটি দিন -->
<?php $csrf = $_SESSION['_csrf_token'] ?? ''; ?>
<input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">

<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-card__header">
            <a href="/" class="auth-card__brand">
                <i class="fa-solid fa-building"></i>
                <?php
                    if (!class_exists('\core\App')) {
                        require_once dirname(__DIR__, 3) . '/core/App.php';
                    }
                ?>

            </a>
            <h1 class="auth-card__title">Welcome Back</h1>
            <p class="auth-card__sub">Sign in to your account</p>
        </div>

        <form method="POST" action="http://localhost/realestate/public/auth/login" novalidate>

           <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                       placeholder="you@example.com"
                       value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                       autofocus required>
                <?php if (!empty($errors['email'])): ?>
                    <div class="form-error"><?= htmlspecialchars($errors['email'][0]) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">
                    Password
                    <a href="/realestate/public/auth/forgot-password" class="form-label__link">Forgot password?</a>
                </label>
                <div class="input-icon-wrap">
                    <input type="password" id="password" name="password"
                           class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                           placeholder="Your password" required>
                    <button type="button" class="input-icon-btn" onclick="togglePassword('password')">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <?php if (!empty($errors['password'])): ?>
                    <div class="form-error"><?= htmlspecialchars($errors['password'][0]) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group form-group--inline">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" value="1">
                    Remember me for 30 days
                </label>
            </div>

            <button type="submit" class="btn btn--primary btn--block">
                <i class="fa-solid fa-right-to-bracket"></i> Sign In
            </button>
        </form>

        <div class="auth-divider"><span>or</span></div>

        <p class="auth-card__footer">
            Don't have an account? <a href="/realestate/public/auth/register">Create one free</a>
        </p>
    </div>
</div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
