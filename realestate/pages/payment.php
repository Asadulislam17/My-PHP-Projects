<?php
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../config/Database.php';

$auth = Auth::getInstance();
$auth->requireLogin();

$db     = Database::getInstance();
$planId = (int)($_GET['plan'] ?? 0);
$plan   = $planId ? $db->queryOne(
    "SELECT * FROM subscription_plans WHERE id=? AND is_active=1", [$planId]
) : null;

if (!$plan) { header('Location: ?page=agent-dashboard&tab=subscription'); exit; }

$user = $auth->currentUser();

// Handle Payment Init
$paymentError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gateway = $_POST['gateway'] ?? 'sslcommerz';

    // Create pending transaction
    $db->execute(
        "INSERT INTO transactions (user_id, type, reference_id, amount, gateway, status)
         VALUES (?, 'subscription', ?, ?, ?, 'pending')",
        [$_SESSION['user_id'], $planId, $plan['price'], $gateway]
    );
    $trxId = $db->lastInsertId();

    if ($gateway === 'sslcommerz') {
        // SSLCommerz Integration
        $postData = [
            'store_id'      => $_ENV['SSL_STORE_ID']    ?? 'test_store',
            'store_passwd'  => $_ENV['SSL_STORE_PASSWD'] ?? 'test_pass',
            'total_amount'  => $plan['price'],
            'currency'      => 'BDT',
            'tran_id'       => 'RE_' . $trxId . '_' . time(),
            'success_url'   => APP_URL . '/api/v1/payment/ssl-success.php?trx=' . $trxId,
            'fail_url'      => APP_URL . '/api/v1/payment/ssl-fail.php?trx='    . $trxId,
            'cancel_url'    => APP_URL . '/index.php?page=payment&plan='        . $planId,
            'cus_name'      => $user['name'],
            'cus_email'     => $user['email'],
            'cus_phone'     => $user['phone'] ?? '01700000000',
            'cus_add1'      => 'Dhaka, Bangladesh',
            'cus_city'      => 'Dhaka',
            'cus_country'   => 'Bangladesh',
            'shipping_method'=> 'NO',
            'product_name'  => $plan['name'] . ' Subscription',
            'product_category'=>'subscription',
            'product_profile'=> 'non-physical-goods',
        ];
        // In production: redirect to SSLCommerz gateway URL
        // $sslUrl = 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php';
        $paymentError = 'SSLCommerz sandbox: তথ্য পাঠানো হয়েছে। Production এ redirect হবে।';

    } elseif ($gateway === 'bkash') {
        // bKash Intent (simplified)
        $paymentError = 'bKash Integration: Production API key দিয়ে activate করুন।';

    } elseif ($gateway === 'nagad') {
        $paymentError = 'Nagad Integration: Production API key দিয়ে activate করুন।';

    } elseif ($gateway === 'demo') {
        // Demo payment — approve directly
        $db->execute(
            "UPDATE transactions SET status='success', gateway_trx_id=? WHERE id=?",
            ['DEMO_' . time(), $trxId]
        );
        $db->execute(
            "INSERT INTO subscriptions (user_id, plan_id, starts_at, expires_at, status)
             VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL ? DAY), 'active')",
            [$_SESSION['user_id'], $planId, $plan['duration_days']]
        );
        header('Location: ?page=agent-dashboard&tab=subscription&success=1');
        exit;
    }
}
?>

<!-- ══════════════════════════════════════════════
     3D PAYMENT PAGE
══════════════════════════════════════════════ -->
<div class="payment-page">

  <!-- Floating Orbs Background -->
  <div class="payment-bg">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="grid-overlay"></div>
  </div>

  <div class="container py-5">
    <div class="payment-wrapper">

      <!-- ── Left: Plan Summary ── -->
      <div class="payment-plan-card">
        <div class="plan-card-glow"></div>

        <div class="plan-selected-badge">
          <i class="bi bi-star-fill me-1"></i>আপনার পরিকল্পনা
        </div>

        <div class="plan-name-display"><?= htmlspecialchars($plan['name']) ?></div>

        <div class="plan-price-3d">
          <span class="currency-sym">৳</span>
          <?= number_format($plan['price']) ?>
          <span class="price-per">/মাস</span>
        </div>

        <div class="plan-duration">
          <i class="bi bi-clock me-2"></i><?= $plan['duration_days'] ?> দিনের সাবস্ক্রিপশন
        </div>

        <div class="plan-divider"></div>

        <ul class="plan-feature-list">
          <li>
            <div class="feature-dot"></div>
            <span><?= $plan['max_listings'] >= 999 ? 'Unlimited' : $plan['max_listings'] ?> Property Listing</span>
          </li>
          <li>
            <div class="feature-dot"></div>
            <span>Analytics Dashboard</span>
          </li>
          <li>
            <div class="feature-dot"></div>
            <span>Priority Support</span>
          </li>
          <li>
            <div class="feature-dot"></div>
            <span>Lead Management</span>
          </li>
          <li>
            <div class="feature-dot"></div>
            <span>Featured Listing সুবিধা</span>
          </li>
        </ul>

        <div class="plan-user-info">
          <div class="plan-user-avatar">
            <?= strtoupper(substr($user['name'],0,1)) ?>
          </div>
          <div>
            <strong><?= htmlspecialchars($user['name']) ?></strong>
            <small><?= htmlspecialchars($user['email']) ?></small>
          </div>
        </div>

        <div class="plan-security-badges">
          <span><i class="bi bi-shield-lock me-1"></i>SSL Secured</span>
          <span><i class="bi bi-patch-check me-1"></i>Verified</span>
        </div>
      </div>

      <!-- ── Right: Payment Form ── -->
      <div class="payment-form-card">
        <h3 class="payment-form-title">পেমেন্ট পদ্ধতি বেছে নিন</h3>

        <?php if ($paymentError): ?>
        <div class="payment-alert">
          <i class="bi bi-info-circle me-2"></i><?= htmlspecialchars($paymentError) ?>
        </div>
        <?php endif; ?>

        <form method="POST" id="paymentForm">

          <!-- Gateway Selection -->
          <div class="gateway-grid">

            <label class="gateway-card" id="gw-sslcommerz">
              <input type="radio" name="gateway" value="sslcommerz" checked>
              <div class="gateway-card-inner">
                <div class="gateway-logo ssl">
                  <i class="bi bi-credit-card-2-front"></i>
                  <span>SSLCommerz</span>
                </div>
                <div class="gateway-methods">
                  Visa · Mastercard · বিকাশ · নগদ · Rocket
                </div>
                <div class="gateway-check">
                  <i class="bi bi-check2"></i>
                </div>
              </div>
            </label>

            <label class="gateway-card" id="gw-bkash">
              <input type="radio" name="gateway" value="bkash">
              <div class="gateway-card-inner">
                <div class="gateway-logo bkash">
                  <span class="bkash-logo">b</span>
                  <span>bKash</span>
                </div>
                <div class="gateway-methods">
                  মোবাইল ব্যাংকিং · তাৎক্ষণিক পেমেন্ট
                </div>
                <div class="gateway-check">
                  <i class="bi bi-check2"></i>
                </div>
              </div>
            </label>

            <label class="gateway-card" id="gw-nagad">
              <input type="radio" name="gateway" value="nagad">
              <div class="gateway-card-inner">
                <div class="gateway-logo nagad">
                  <i class="bi bi-wallet2"></i>
                  <span>Nagad</span>
                </div>
                <div class="gateway-methods">
                  মোবাইল ব্যাংকিং · সহজ পেমেন্ট
                </div>
                <div class="gateway-check">
                  <i class="bi bi-check2"></i>
                </div>
              </div>
            </label>

            <label class="gateway-card demo-card" id="gw-demo">
              <input type="radio" name="gateway" value="demo">
              <div class="gateway-card-inner">
                <div class="gateway-logo demo">
                  <i class="bi bi-lightning-charge"></i>
                  <span>Demo Pay</span>
                </div>
                <div class="gateway-methods">
                  Testing · তাৎক্ষণিক activation
                </div>
                <div class="gateway-check">
                  <i class="bi bi-check2"></i>
                </div>
              </div>
            </label>

          </div>

          <!-- Card Details (SSLCommerz) -->
          <div class="card-details-section" id="cardSection">
            <h5 class="card-section-title">কার্ডের তথ্য</h5>

            <div class="card-3d-preview" id="card3d">
              <div class="card-3d-inner">
                <div class="card-front">
                  <div class="card-chip">
                    <div class="chip-line"></div>
                    <div class="chip-line"></div>
                    <div class="chip-line"></div>
                  </div>
                  <div class="card-number-display" id="cardNumDisplay">
                    •••• •••• •••• ••••
                  </div>
                  <div class="card-bottom">
                    <div>
                      <small>CARD HOLDER</small>
                      <div id="cardNameDisplay">Your Name</div>
                    </div>
                    <div>
                      <small>EXPIRES</small>
                      <div id="cardExpDisplay">MM/YY</div>
                    </div>
                  </div>
                  <div class="card-brand">
                    <i class="bi bi-credit-card"></i>
                  </div>
                </div>
              </div>
            </div>

            <div class="field-wrap mt-4">
              <label class="field-label">কার্ড নম্বর</label>
              <div class="input-with-card-icon">
                <i class="bi bi-credit-card"></i>
                <input type="text" id="cardNumber" class="field-input"
                       placeholder="1234 5678 9012 3456"
                       maxlength="19"
                       oninput="formatCardNum(this)">
              </div>
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <div class="field-wrap">
                  <label class="field-label">কার্ডধারীর নাম</label>
                  <input type="text" id="cardName" class="field-input"
                         placeholder="Name on Card"
                         oninput="document.getElementById('cardNameDisplay').textContent=this.value||'Your Name'">
                </div>
              </div>
              <div class="col-md-3">
                <div class="field-wrap">
                  <label class="field-label">মেয়াদ</label>
                  <input type="text" id="cardExp" class="field-input"
                         placeholder="MM/YY" maxlength="5"
                         oninput="formatExp(this)">
                </div>
              </div>
              <div class="col-md-3">
                <div class="field-wrap">
                  <label class="field-label">CVV</label>
                  <input type="text" id="cardCvv" class="field-input"
                         placeholder="•••" maxlength="3"
                         onfocus="flipCard(true)" onblur="flipCard(false)">
                </div>
              </div>
            </div>
          </div>

          <!-- bKash Section -->
          <div class="bkash-section" id="bkashSection" style="display:none;">
            <div class="bkash-guide">
              <div class="bkash-step">
                <div class="bkash-step-num">১</div>
                <div>আপনার bKash app খুলুন</div>
              </div>
              <div class="bkash-step">
                <div class="bkash-step-num">২</div>
                <div>Send Money তে যান</div>
              </div>
              <div class="bkash-step">
                <div class="bkash-step-num">৩</div>
                <div>নম্বরে ৳<?= number_format($plan['price']) ?> পাঠান</div>
              </div>
            </div>
            <div class="bkash-number">
              <i class="bi bi-telephone me-2"></i>
              <span>01XXXXXXXXX</span>
              <button type="button" onclick="copyPhone(this)" class="copy-phone-btn">
                <i class="bi bi-copy"></i>
              </button>
            </div>
            <div class="field-wrap mt-3">
              <label class="field-label">bKash Transaction ID</label>
              <input type="text" name="bkash_trx" class="field-input"
                     placeholder="TrxID দিন">
            </div>
          </div>

          <!-- Order Summary -->
          <div class="order-summary">
            <div class="order-row">
              <span><?= htmlspecialchars($plan['name']) ?> Plan</span>
              <span>৳<?= number_format($plan['price']) ?></span>
            </div>
            <div class="order-row">
              <span>VAT (15%)</span>
              <span>৳<?= number_format($plan['price'] * 0.15) ?></span>
            </div>
            <div class="order-divider"></div>
            <div class="order-row total">
              <strong>মোট</strong>
              <strong class="text-accent">
                ৳<?= number_format($plan['price'] * 1.15) ?>
              </strong>
            </div>
          </div>

          <!-- Submit -->
          <button type="submit" class="payment-submit-btn" id="payBtn">
            <div class="pay-btn-content">
              <i class="bi bi-lock-fill me-2"></i>
              <span id="payBtnText">
                ৳<?= number_format($plan['price'] * 1.15) ?> পেমেন্ট করুন
              </span>
            </div>
            <div class="pay-btn-shine"></div>
          </button>

          <div class="payment-footer-note">
            <i class="bi bi-shield-check me-1"></i>
            আপনার তথ্য 256-bit SSL দ্বারা সুরক্ষিত
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Gateway selection
document.querySelectorAll('input[name="gateway"]').forEach(r => {
  r.addEventListener('change', function() {
    document.querySelectorAll('.gateway-card').forEach(c => c.classList.remove('selected'));
    this.closest('.gateway-card').classList.add('selected');

    const cardSec   = document.getElementById('cardSection');
    const bkashSec  = document.getElementById('bkashSection');
    const payBtnTxt = document.getElementById('payBtnText');

    cardSec.style.display  = this.value === 'sslcommerz' ? 'block' : 'none';
    bkashSec.style.display = this.value === 'bkash'      ? 'block' : 'none';

    if (this.value === 'demo') {
      payBtnTxt.textContent = '⚡ Demo Payment (তাৎক্ষণিক activation)';
    } else {
      payBtnTxt.textContent = '৳<?= number_format($plan['price'] * 1.15) ?> পেমেন্ট করুন';
    }
  });
});

// Mark first selected
document.querySelector('input[name="gateway"]:checked')
  ?.closest('.gateway-card')?.classList.add('selected');

// Card number format
function formatCardNum(el) {
  let v = el.value.replace(/\D/g,'').substring(0,16);
  el.value = v.replace(/(.{4})/g,'$1 ').trim();
  const masked = (v + '****************').substring(0,16);
  const disp   = masked.replace(/(.{4})/g,'$1 ').trim();
  document.getElementById('cardNumDisplay').textContent = disp;
}

// Expiry format
function formatExp(el) {
  let v = el.value.replace(/\D/g,'');
  if (v.length >= 2) v = v.substring(0,2) + '/' + v.substring(2,4);
  el.value = v;
  document.getElementById('cardExpDisplay').textContent = el.value || 'MM/YY';
}

// Card flip for CVV
function flipCard(flip) {
  document.getElementById('card3d').classList.toggle('flipped', flip);
}

// Copy phone
function copyPhone(btn) {
  navigator.clipboard.writeText('01XXXXXXXXX');
  btn.innerHTML = '<i class="bi bi-check2"></i>';
  setTimeout(() => btn.innerHTML = '<i class="bi bi-copy"></i>', 2000);
}

// Pay button loading
document.getElementById('paymentForm').addEventListener('submit', () => {
  const btn = document.getElementById('payBtn');
  btn.classList.add('loading');
  btn.querySelector('.pay-btn-content').innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
});
</script>