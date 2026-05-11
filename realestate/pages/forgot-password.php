<?php
require_once __DIR__ . '/../classes/Auth.php';
$auth = Auth::getInstance();
if ($auth->isLoggedIn()) { header('Location: ' . APP_URL); exit; }

$step    = $_GET['step'] ?? 'email'; // email → otp → reset
$email   = $_GET['email'] ?? '';
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = $_POST['step'] ?? 'email';

    if ($step === 'email') {
        $result = $auth->forgotPassword($_POST['email'] ?? '');
        if ($result['success']) {
            header('Location: ?page=forgot-password&step=otp&email=' . urlencode($_POST['email']));
            exit;
        }
        $error = $result['message'] ?? 'কিছু সমস্যা হয়েছে।';
    }

    if ($step === 'otp') {
        $result = $auth->verifyOTP($_POST['email'] ?? '', $_POST['otp'] ?? '');
        if ($result['success']) {
            header('Location: ?page=forgot-password&step=reset&email=' . urlencode($_POST['email']));
            exit;
        }
        $error = $result['message'];
    }

    if ($step === 'reset') {
        $result = $auth->resetPassword(
            $_POST['email']        ?? '',
            $_POST['otp']          ?? '',
            $_POST['new_password'] ?? ''
        );
        if ($result['success']) {
            header('Location: ?page=login&msg=' . urlencode('Password পরিবর্তন হয়েছে! এখন login করুন।'));
            exit;
        }
        $error = $result['message'];
    }
}

$currentStep = $_GET['step'] ?? $step;
$steps = ['email' => 1, 'otp' => 2, 'reset' => 3];
$stepNum = $steps[$currentStep] ?? 1;
?>

<div class="fp-page">
  <!-- 3D Background -->
  <div class="fp-bg">
    <div class="fp-orb fp-orb-1"></div>
    <div class="fp-orb fp-orb-2"></div>
    <div class="fp-grid"></div>
    <div class="fp-particles" id="fpParticles"></div>
  </div>

  <div class="fp-wrapper">

    <!-- Brand -->
    <a href="?page=home" class="fp-brand">
      <div class="fp-brand-icon"><i class="bi bi-buildings-fill"></i></div>
      <span>Real<span>Estate</span> BD</span>
    </a>

    <!-- Progress Steps -->
    <div class="fp-steps">
      <?php foreach (['Email', 'OTP', 'নতুন Password'] as $i => $label): ?>
      <div class="fp-step <?= $stepNum > $i ? 'done' : ($stepNum === $i+1 ? 'active' : '') ?>">
        <div class="fp-step-num">
          <?php if ($stepNum > $i+1): ?>
            <i class="bi bi-check2"></i>
          <?php else: ?>
            <?= $i + 1 ?>
          <?php endif; ?>
        </div>
        <span><?= $label ?></span>
      </div>
      <?php if ($i < 2): ?>
      <div class="fp-step-line <?= $stepNum > $i+1 ? 'done' : '' ?>"></div>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <!-- Card -->
    <div class="fp-card">
      <div class="fp-card-glow"></div>

      <?php if ($error): ?>
      <div class="fp-alert error">
        <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <!-- STEP 1: Email -->
      <?php if ($currentStep === 'email'): ?>
      <div class="fp-card-icon">
        <div class="fp-icon-ring">
          <i class="bi bi-envelope-open"></i>
        </div>
      </div>
      <h2 class="fp-title">Password ভুলে গেছেন?</h2>
      <p class="fp-subtitle">আপনার email দিন। OTP পাঠানো হবে।</p>

      <form method="POST" class="fp-form" id="fpForm">
        <input type="hidden" name="step" value="email">
        <div class="fp-field">
          <label>Email Address</label>
          <div class="fp-input-wrap">
            <i class="bi bi-envelope"></i>
            <input type="email" name="email" class="fp-input"
                   placeholder="example@email.com" required
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>
        </div>
        <button type="submit" class="fp-btn">
          <span>OTP পাঠান</span>
          <i class="bi bi-arrow-right ms-2"></i>
          <div class="fp-btn-shine"></div>
        </button>
      </form>

      <!-- STEP 2: OTP -->
      <?php elseif ($currentStep === 'otp'): ?>
      <div class="fp-card-icon">
        <div class="fp-icon-ring gold">
          <i class="bi bi-shield-lock"></i>
        </div>
      </div>
      <h2 class="fp-title">OTP দিন</h2>
      <p class="fp-subtitle">
        <strong><?= htmlspecialchars($email) ?></strong> এ ৬ সংখ্যার OTP পাঠানো হয়েছে
      </p>

      <form method="POST" class="fp-form">
        <input type="hidden" name="step" value="otp">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

        <!-- OTP Input Boxes -->
        <div class="otp-boxes" id="otpBoxes">
          <?php for ($i = 0; $i < 6; $i++): ?>
          <input type="text" class="otp-box" maxlength="1"
                 pattern="[0-9]" inputmode="numeric" autocomplete="one-time-code">
          <?php endfor; ?>
        </div>
        <input type="hidden" name="otp" id="otpHidden">

        <div class="fp-resend">
          <span id="countdown">১৫:০০ এর মধ্যে ব্যবহার করুন</span>
          <a href="?page=forgot-password&step=email" class="fp-resend-link" id="resendBtn"
             style="display:none">আবার পাঠান</a>
        </div>

        <button type="submit" class="fp-btn" onclick="combineOTP()">
          <span>OTP যাচাই করুন</span>
          <i class="bi bi-check-circle ms-2"></i>
          <div class="fp-btn-shine"></div>
        </button>
      </form>

      <!-- STEP 3: Reset Password -->
      <?php elseif ($currentStep === 'reset'): ?>
      <div class="fp-card-icon">
        <div class="fp-icon-ring green">
          <i class="bi bi-key"></i>
        </div>
      </div>
      <h2 class="fp-title">নতুন Password দিন</h2>
      <p class="fp-subtitle">শক্তিশালী password বেছে নিন</p>

      <form method="POST" class="fp-form" id="resetForm">
        <input type="hidden" name="step" value="reset">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <input type="hidden" name="otp" value="<?= htmlspecialchars($_GET['otp'] ?? '') ?>">

        <div class="fp-field">
          <label>নতুন Password</label>
          <div class="fp-input-wrap">
            <i class="bi bi-lock"></i>
            <input type="password" name="new_password" id="newPass" class="fp-input"
                   placeholder="কমপক্ষে ৬ অক্ষর" required minlength="6"
                   oninput="checkPasswordStrength(this.value)">
            <button type="button" class="fp-eye-btn" onclick="togglePass('newPass',this)">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <!-- Strength Meter -->
          <div class="strength-meter" id="strengthMeter">
            <div class="strength-bar" id="strengthBar"></div>
          </div>
          <span class="strength-label" id="strengthLabel"></span>
        </div>

        <div class="fp-field">
          <label>Password নিশ্চিত করুন</label>
          <div class="fp-input-wrap">
            <i class="bi bi-lock-fill"></i>
            <input type="password" name="confirm_password" id="confirmPass" class="fp-input"
                   placeholder="Password আবার দিন" required
                   oninput="checkMatch()">
            <button type="button" class="fp-eye-btn" onclick="togglePass('confirmPass',this)">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <span class="match-label" id="matchLabel"></span>
        </div>

        <button type="submit" class="fp-btn" id="resetBtn" disabled>
          <span>Password পরিবর্তন করুন</span>
          <i class="bi bi-check-circle ms-2"></i>
          <div class="fp-btn-shine"></div>
        </button>
      </form>
      <?php endif; ?>

      <div class="fp-back">
        <a href="?page=login"><i class="bi bi-arrow-left me-1"></i>Login পেজে ফিরুন</a>
      </div>
    </div>

  </div>
</div>

<style>
/* ── Forgot Password 3D Page ── */
.fp-page {
  min-height: 100vh; display: flex; align-items: center;
  justify-content: center; position: relative; overflow: hidden;
  background: #060D1A; padding: 40px 20px;
}
.fp-bg { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
.fp-orb {
  position: absolute; border-radius: 50%;
  filter: blur(100px); opacity: 0.12;
}
.fp-orb-1 { width: 600px; height: 600px; background: #C5A059; top: -200px; left: -200px; animation: fpOrb 10s ease-in-out infinite; }
.fp-orb-2 { width: 500px; height: 500px; background: #3B82F6; bottom: -200px; right: -200px; animation: fpOrb 12s ease-in-out infinite reverse; }
@keyframes fpOrb { 0%,100%{transform:translate(0,0)} 50%{transform:translate(30px,-30px)} }

.fp-grid {
  position: absolute; inset: 0;
  background-image: linear-gradient(rgba(255,255,255,0.015) 1px,transparent 1px),
                    linear-gradient(90deg,rgba(255,255,255,0.015) 1px,transparent 1px);
  background-size: 60px 60px;
}

.fp-wrapper {
  position: relative; z-index: 1; width: 100%; max-width: 440px;
  display: flex; flex-direction: column; align-items: center; gap: 24px;
}

/* Brand */
.fp-brand {
  display: flex; align-items: center; gap: 10px; text-decoration: none;
  font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 1.1rem; color: #fff;
}
.fp-brand-icon {
  width: 36px; height: 36px; border-radius: 8px;
  background: linear-gradient(135deg, #C5A059, #b8912e);
  display: flex; align-items: center; justify-content: center;
  color: #0F172A; font-size: 1rem;
}
.fp-brand span span { color: #C5A059; }

/* Steps */
.fp-steps {
  display: flex; align-items: center; gap: 8px; width: 100%;
}
.fp-step {
  display: flex; flex-direction: column; align-items: center; gap: 6px;
  flex: 1;
}
.fp-step-num {
  width: 36px; height: 36px; border-radius: 50%;
  border: 2px solid rgba(255,255,255,0.15);
  background: rgba(255,255,255,0.04);
  color: rgba(255,255,255,0.3); font-weight: 700; font-size: 0.875rem;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.3s ease;
}
.fp-step span { font-size: 0.72rem; color: rgba(255,255,255,0.3); white-space: nowrap; }
.fp-step.active .fp-step-num { border-color: #C5A059; background: rgba(197,160,89,0.15); color: #C5A059; }
.fp-step.active span { color: #C5A059; }
.fp-step.done .fp-step-num { border-color: #22C55E; background: rgba(34,197,94,0.15); color: #22C55E; }
.fp-step.done span { color: #22C55E; }
.fp-step-line { flex: 1; height: 2px; background: rgba(255,255,255,0.08); border-radius: 2px; }
.fp-step-line.done { background: #22C55E; }

/* Card */
.fp-card {
  width: 100%; background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 24px; padding: 36px 32px;
  backdrop-filter: blur(20px); position: relative; overflow: hidden;
}
.fp-card-glow {
  position: absolute; top: -80px; right: -80px; width: 200px; height: 200px;
  background: radial-gradient(circle, rgba(197,160,89,0.12), transparent 70%);
  pointer-events: none;
}

/* Icon */
.fp-card-icon { text-align: center; margin-bottom: 20px; }
.fp-icon-ring {
  width: 72px; height: 72px; border-radius: 50%;
  border: 2px solid rgba(59,130,246,0.4);
  background: rgba(59,130,246,0.1);
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto; font-size: 1.8rem; color: #3B82F6;
  animation: iconPulse 2s ease-in-out infinite;
}
.fp-icon-ring.gold  { border-color: rgba(197,160,89,0.4); background: rgba(197,160,89,0.1); color: #C5A059; }
.fp-icon-ring.green { border-color: rgba(34,197,94,0.4);  background: rgba(34,197,94,0.1);  color: #22C55E; }
@keyframes iconPulse { 0%,100%{box-shadow:0 0 0 0 rgba(59,130,246,0.3)} 50%{box-shadow:0 0 0 12px rgba(59,130,246,0)} }

.fp-title    { color: #fff; font-size: 1.4rem; font-weight: 800; text-align: center; margin-bottom: 8px; }
.fp-subtitle { color: rgba(255,255,255,0.5); font-size: 0.875rem; text-align: center; margin-bottom: 28px; }

/* Alert */
.fp-alert {
  padding: 12px 16px; border-radius: 10px; font-size: 0.85rem;
  margin-bottom: 16px; display: flex; align-items: center;
}
.fp-alert.error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #FCA5A5; }

/* Form */
.fp-form { display: flex; flex-direction: column; gap: 16px; }
.fp-field label {
  display: block; font-size: 0.75rem; font-weight: 700;
  color: rgba(255,255,255,0.5); text-transform: uppercase;
  letter-spacing: 0.8px; margin-bottom: 8px;
}
.fp-input-wrap {
  display: flex; align-items: center; gap: 0;
  border: 1.5px solid rgba(255,255,255,0.1); border-radius: 10px;
  background: rgba(255,255,255,0.05); transition: border-color 0.2s;
  overflow: hidden;
}
.fp-input-wrap:focus-within { border-color: #C5A059; box-shadow: 0 0 0 3px rgba(197,160,89,0.1); }
.fp-input-wrap > i { padding: 12px 14px; color: rgba(255,255,255,0.3); font-size: 0.95rem; flex-shrink: 0; }
.fp-input {
  flex: 1; background: transparent; border: none; outline: none;
  color: #fff; font-size: 0.925rem; padding: 12px 0; font-family: 'Inter', sans-serif;
}
.fp-input::placeholder { color: rgba(255,255,255,0.2); }
.fp-eye-btn { background: none; border: none; color: rgba(255,255,255,0.3); cursor: pointer; padding: 12px 14px; transition: color 0.2s; }
.fp-eye-btn:hover { color: #C5A059; }

/* OTP Boxes */
.otp-boxes {
  display: flex; gap: 10px; justify-content: center; margin: 8px 0 16px;
}
.otp-box {
  width: 50px; height: 58px; border: 2px solid rgba(255,255,255,0.1);
  border-radius: 10px; background: rgba(255,255,255,0.05);
  color: #fff; font-size: 1.4rem; font-weight: 800;
  text-align: center; outline: none; transition: all 0.2s;
  font-family: 'Montserrat', sans-serif;
}
.otp-box:focus { border-color: #C5A059; box-shadow: 0 0 0 3px rgba(197,160,89,0.15); background: rgba(197,160,89,0.05); }
.otp-box.filled { border-color: rgba(34,197,94,0.5); }

.fp-resend { text-align: center; font-size: 0.82rem; color: rgba(255,255,255,0.4); }
.fp-resend-link { color: #C5A059; text-decoration: none; font-weight: 600; margin-left: 8px; }

/* Password Strength */
.strength-meter { height: 4px; background: rgba(255,255,255,0.08); border-radius: 2px; margin-top: 8px; }
.strength-bar { height: 100%; border-radius: 2px; transition: all 0.3s; width: 0; }
.strength-label { font-size: 0.72rem; margin-top: 4px; display: block; }

.match-label { font-size: 0.75rem; margin-top: 4px; display: block; }

/* Button */
.fp-btn {
  width: 100%; padding: 14px; border-radius: 12px; border: none;
  background: linear-gradient(135deg, #C5A059, #b8912e);
  color: #0F172A; font-weight: 800; font-size: 1rem;
  cursor: pointer; position: relative; overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
  display: flex; align-items: center; justify-content: center;
}
.fp-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(197,160,89,0.5); }
.fp-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.fp-btn-shine {
  position: absolute; top: 0; left: -100%; width: 50%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
  animation: btnShine 3s infinite;
}
@keyframes btnShine { 0%{left:-100%} 100%{left:200%} }

/* Back Link */
.fp-back { text-align: center; margin-top: 20px; }
.fp-back a { color: rgba(255,255,255,0.4); text-decoration: none; font-size: 0.85rem; transition: color 0.2s; }
.fp-back a:hover { color: #C5A059; }
</style>

<script>
// ── OTP Box Handler ──
const otpBoxes = document.querySelectorAll('.otp-box');

otpBoxes.forEach((box, i) => {
  box.addEventListener('input', e => {
    const val = e.target.value.replace(/[^0-9]/g,'');
    e.target.value = val.slice(-1);
    if (val && i < otpBoxes.length - 1) otpBoxes[i+1].focus();
    box.classList.toggle('filled', !!val);
    combineOTP();
  });
  box.addEventListener('keydown', e => {
    if (e.key === 'Backspace' && !box.value && i > 0) otpBoxes[i-1].focus();
    if (e.key === 'ArrowLeft'  && i > 0) otpBoxes[i-1].focus();
    if (e.key === 'ArrowRight' && i < otpBoxes.length-1) otpBoxes[i+1].focus();
  });
  box.addEventListener('paste', e => {
    e.preventDefault();
    const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'');
    pasted.split('').slice(0,6).forEach((d,j) => {
      if (otpBoxes[j]) { otpBoxes[j].value = d; otpBoxes[j].classList.add('filled'); }
    });
    combineOTP();
    if (otpBoxes[Math.min(pasted.length, 5)]) otpBoxes[Math.min(pasted.length,5)].focus();
  });
});

function combineOTP() {
  const hidden = document.getElementById('otpHidden');
  if (hidden) hidden.value = Array.from(otpBoxes).map(b=>b.value).join('');
}

// ── OTP Countdown ──
let timeLeft = 15 * 60;
const countdownEl = document.getElementById('countdown');
const resendBtn   = document.getElementById('resendBtn');

if (countdownEl) {
  const timer = setInterval(() => {
    timeLeft--;
    const m = Math.floor(timeLeft / 60);
    const s = timeLeft % 60;
    countdownEl.textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')} এর মধ্যে ব্যবহার করুন`;
    if (timeLeft <= 0) {
      clearInterval(timer);
      countdownEl.textContent = 'OTP মেয়াদ শেষ';
      if (resendBtn) resendBtn.style.display = 'inline';
    }
  }, 1000);
}

// ── Password Strength ──
function checkPasswordStrength(val) {
  const bar   = document.getElementById('strengthBar');
  const label = document.getElementById('strengthLabel');
  if (!bar) return;

  let score = 0;
  if (val.length >= 6) score++;
  if (val.length >= 10) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;

  const levels = [
    { pct:'20%', color:'#ef4444', text:'খুব দুর্বল' },
    { pct:'40%', color:'#f97316', text:'দুর্বল' },
    { pct:'60%', color:'#eab308', text:'মাঝারি' },
    { pct:'80%', color:'#3b82f6', text:'ভালো' },
    { pct:'100%',color:'#22c55e', text:'শক্তিশালী' },
  ];
  const lvl = levels[Math.min(score-1, 4)] || levels[0];
  bar.style.width     = lvl.pct;
  bar.style.background= lvl.color;
  label.textContent   = val ? lvl.text : '';
  label.style.color   = lvl.color;
  checkMatch();
}

function checkMatch() {
  const pass    = document.getElementById('newPass')?.value;
  const confirm = document.getElementById('confirmPass')?.value;
  const label   = document.getElementById('matchLabel');
  const btn     = document.getElementById('resetBtn');
  if (!label || !confirm) return;

  if (confirm === '') { label.textContent = ''; return; }
  const match = pass === confirm && pass.length >= 6;
  label.textContent = match ? '✓ Password মিলেছে' : '✗ Password মিলেনি';
  label.style.color = match ? '#22C55E' : '#ef4444';
  if (btn) btn.disabled = !match;
}

function togglePass(id, btn) {
  const input = document.getElementById(id);
  if (!input) return;
  const isText = input.type === 'text';
  input.type   = isText ? 'password' : 'text';
  btn.querySelector('i').className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
}

// ── Particles ──
(function() {
  const container = document.getElementById('fpParticles');
  if (!container) return;
  for (let i = 0; i < 30; i++) {
    const p = document.createElement('div');
    p.style.cssText = `
      position:absolute; border-radius:50%;
      width:${Math.random()*4+2}px; height:${Math.random()*4+2}px;
      background:rgba(197,160,89,${Math.random()*0.3+0.05});
      left:${Math.random()*100}%; top:${Math.random()*100}%;
      animation: fpFloat ${Math.random()*10+8}s ease-in-out infinite;
      animation-delay:${Math.random()*5}s;
    `;
    container.appendChild(p);
  }
  const style = document.createElement('style');
  style.textContent = `@keyframes fpFloat{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(${Math.random()*30-15}px,${Math.random()*30-15}px) scale(1.2)}}`;
  document.head.appendChild(style);
})();
</script>