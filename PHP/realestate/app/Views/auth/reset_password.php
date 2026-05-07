<?php $csrf = $_SESSION['_csrf_token'] ?? ''; ?>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-card__header">
            <div class="otp-icon" style="background:#dcfce7;color:#15803d">
                <i class="fa-solid fa-key"></i>
            </div>
            <h1 class="auth-card__title">Reset Password</h1>
            <p class="auth-card__sub">Enter the OTP from your email and your new password</p>
        </div>

        <form method="POST" action="/realestate/public/auth/reset-password" novalidate>
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">

            <div class="form-group">
                <label class="form-label">6-Digit OTP</label>
                <div class="otp-inputs" id="otp_boxes">
                    <?php for ($i = 0; $i < 6; $i++): ?>
                        <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="otp" id="otp_value">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">New Password</label>
                <div class="input-icon-wrap">
                    <input type="password" id="password" name="password"
                           class="form-control" placeholder="Min. 8 characters" required>
                    <button type="button" class="input-icon-btn" onclick="togglePassword('password')">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirm">Confirm New Password</label>
                <div class="input-icon-wrap">
                    <input type="password" id="password_confirm" name="password_confirm"
                           class="form-control" placeholder="Repeat password" required>
                    <button type="button" class="input-icon-btn" onclick="togglePassword('password_confirm')">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn--primary btn--block">
                <i class="fa-solid fa-rotate"></i> Reset Password
            </button>
        </form>

        <p class="auth-card__footer">
            <a href="/realestate/public/auth/forgot-password">← Resend OTP</a>
        </p>
    </div>
</div>

<script>
const boxes  = document.querySelectorAll('.otp-box');
const hidden = document.getElementById('otp_value');

boxes.forEach((box, i) => {
    box.addEventListener('input', e => {
        box.value = e.target.value.replace(/\D/, '');
        if (box.value && i < 5) boxes[i + 1].focus();
        hidden.value = [...boxes].map(b => b.value).join('');
    });
    box.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && !box.value && i > 0) boxes[i - 1].focus();
    });
});

function togglePassword(id) {
    const el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
}

boxes[0].focus();
</script>
