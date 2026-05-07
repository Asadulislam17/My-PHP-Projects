<?php
// Shared CSRF token helper
$csrf = $_SESSION['_csrf_token'] ?? '';
?>
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
            <h1 class="auth-card__title">Create Account</h1>
            <p class="auth-card__sub">Join thousands of buyers and agents</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="flash flash--error" style="margin-bottom:1rem">
                <i class="fa-solid fa-circle-xmark"></i>
                <ul style="margin:0;padding-left:1.2rem">
                    <?php foreach ($errors as $field => $msgs): ?>
                        <?php foreach ((array)$msgs as $msg): ?>
                            <li><?= htmlspecialchars($msg) ?></li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="/realestate/public/auth/register" novalidate>
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">

            <!-- Account type -->
            <div class="form-group">
                <label class="form-label">I want to</label>
                <div class="role-toggle">
                    <label class="role-toggle__option <?= ($old['role'] ?? 'buyer') === 'buyer' ? 'active' : '' ?>">
                        <input type="radio" name="role" value="buyer"
                               <?= ($old['role'] ?? 'buyer') === 'buyer' ? 'checked' : '' ?>>
                        <i class="fa-solid fa-house"></i> Buy / Rent
                    </label>
                    <label class="role-toggle__option <?= ($old['role'] ?? '') === 'agent' ? 'active' : '' ?>">
                        <input type="radio" name="role" value="agent"
                               <?= ($old['role'] ?? '') === 'agent' ? 'checked' : '' ?>>
                        <i class="fa-solid fa-briefcase"></i> List as Agent
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                       placeholder="Enter your full name"
                       value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
                <?php if (!empty($errors['name'])): ?>
                    <div class="form-error"><?= htmlspecialchars($errors['name'][0]) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                       placeholder="you@example.com"
                       value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                <?php if (!empty($errors['email'])): ?>
                    <div class="form-error"><?= htmlspecialchars($errors['email'][0]) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone (optional)</label>
                <input type="tel" id="phone" name="phone" class="form-control"
                       placeholder="+880 1700 000000"
                       value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-icon-wrap">
                    <input type="password" id="password" name="password"
                           class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                           placeholder="Min. 8 characters" required>
                    <button type="button" class="input-icon-btn" onclick="togglePassword('password')">
                        <i class="fa-solid fa-eye" id="password_eye"></i>
                    </button>
                </div>
                <div class="password-strength" id="pwd_strength"></div>
                <?php if (!empty($errors['password'])): ?>
                    <div class="form-error"><?= htmlspecialchars($errors['password'][0]) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirm">Confirm Password</label>
                <div class="input-icon-wrap">
                    <input type="password" id="password_confirm" name="password_confirm"
                           class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>"
                           placeholder="Repeat password" required>
                    <button type="button" class="input-icon-btn" onclick="togglePassword('password_confirm')">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <?php if (!empty($errors['password_confirm'])): ?>
                    <div class="form-error"><?= htmlspecialchars($errors['password_confirm'][0]) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="terms" required>
                    I agree to the <a href="/terms" target="_blank">Terms of Service</a> and
                    <a href="/privacy" target="_blank">Privacy Policy</a>
                </label>
            </div>

            <button type="submit" class="btn btn--primary btn--block">
                <i class="fa-solid fa-user-plus"></i> Create Account
            </button>
        </form>

        <p class="auth-card__footer">
            Already have an account? <a href="/realestate/public/auth/login">Sign in</a>
        </p>
    </div>
</div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

// Password strength indicator
document.getElementById('password').addEventListener('input', function () {
    const val = this.value;
    const bar = document.getElementById('pwd_strength');
    let strength = 0;
    if (val.length >= 8) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[^A-Za-z0-9]/.test(val)) strength++;

    const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    const colors = ['', '#ef4444', '#f97316', '#22c55e', '#2563eb'];
    bar.textContent = val ? 'Strength: ' + (labels[strength] || 'Weak') : '';
    bar.style.color = colors[strength] || '#ef4444';
});

// Role toggle styling
document.querySelectorAll('.role-toggle__option input').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.role-toggle__option').forEach(el => el.classList.remove('active'));
        radio.closest('.role-toggle__option').classList.add('active');
    });
});
</script>
