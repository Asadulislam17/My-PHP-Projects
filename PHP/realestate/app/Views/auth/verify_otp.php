<?php $csrf = $_SESSION['_csrf_token'] ?? ''; ?>
<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-card__header">
            <div class="otp-icon"><i class="fa-solid fa-envelope-open-text"></i></div>
            <h1 class="auth-card__title">Verify Your Email</h1>
            <p class="auth-card__sub">
                We sent a 6-digit OTP to<br>
                <strong><?= htmlspecialchars($email ?? '') ?></strong>
            </p>
        </div>

        <form method="POST" action="/auth/verify-otp" novalidate>
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">

            <div class="form-group">
                <label class="form-label text-center" style="text-align:center;display:block">Enter OTP</label>
                <div class="otp-inputs" id="otp_boxes">
                    <input type="text" maxlength="1" class="otp-box" data-index="0" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-box" data-index="1" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-box" data-index="2" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-box" data-index="3" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-box" data-index="4" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-box" data-index="5" inputmode="numeric">
                </div>
                <!-- Hidden input that gets the combined OTP value -->
                <input type="hidden" name="otp" id="otp_value">
            </div>

            <div class="otp-timer">
                Code expires in <span id="countdown">15:00</span>
            </div>

            <button type="submit" class="btn btn--primary btn--block" id="verify_btn" disabled>
                <i class="fa-solid fa-shield-check"></i> Verify Email
            </button>
        </form>

        <form method="POST" action="/auth/resend-otp" style="margin-top:1rem">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
            <button type="submit" class="btn btn--ghost btn--block" id="resend_btn">
                <i class="fa-solid fa-paper-plane"></i> Resend OTP
            </button>
        </form>

        <p class="auth-card__footer">
            Wrong email? <a href="/auth/register">Start over</a>
        </p>
    </div>
</div>

<script>
// ── OTP boxes auto-advance ─────────────────────────────────────────── //
const boxes    = document.querySelectorAll('.otp-box');
const hidden   = document.getElementById('otp_value');
const verifyBtn = document.getElementById('verify_btn');

boxes.forEach((box, i) => {
    box.addEventListener('input', e => {
        const val = e.target.value.replace(/\D/, '');
        box.value = val;
        if (val && i < 5) boxes[i + 1].focus();
        syncOtp();
    });

    box.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && !box.value && i > 0) {
            boxes[i - 1].focus();
        }
    });

    box.addEventListener('paste', e => {
        e.preventDefault();
        const pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
        [...pasted].forEach((ch, idx) => { if (boxes[idx]) boxes[idx].value = ch; });
        syncOtp();
        if (pasted.length === 6) boxes[5].focus();
    });
});

function syncOtp() {
    const val = [...boxes].map(b => b.value).join('');
    hidden.value = val;
    verifyBtn.disabled = val.length !== 6;
}

// ── Countdown timer ───────────────────────────────────────────────── //
let seconds = 15 * 60;
const countdown = document.getElementById('countdown');
const timer = setInterval(() => {
    seconds--;
    if (seconds <= 0) {
        clearInterval(timer);
        countdown.textContent = 'Expired';
        countdown.style.color = '#ef4444';
        verifyBtn.disabled = true;
        return;
    }
    const m = String(Math.floor(seconds / 60)).padStart(2, '0');
    const s = String(seconds % 60).padStart(2, '0');
    countdown.textContent = `${m}:${s}`;
}, 1000);

boxes[0].focus();
</script>
