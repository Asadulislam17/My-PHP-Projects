<?php
require_once __DIR__ . '/../classes/Auth.php';
$auth = Auth::getInstance();
$auth->requireLogin();
$db   = Database::getInstance();
$user = $auth->currentUser();
$msg  = ''; $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name  = trim($_POST['name']  ?? '');
        $phone = trim($_POST['phone'] ?? '');
        if (strlen($name) < 2) { $err = 'নাম কমপক্ষে ২ অক্ষর হতে হবে।'; }
        else {
            // Avatar upload
            $avatarName = $user['avatar'];
            if (!empty($_FILES['avatar']['name'])) {
                $allowed = ['image/jpeg','image/png','image/webp'];
                if (in_array($_FILES['avatar']['type'], $allowed) && $_FILES['avatar']['size'] < 2097152) {
                    $dir = UPLOAD_PATH . 'avatars/';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    $ext        = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                    $avatarName = 'av_' . $user['id'] . '_' . time() . '.webp';
                    // Convert to WebP
                    $src = match($_FILES['avatar']['type']) {
                        'image/jpeg' => imagecreatefromjpeg($_FILES['avatar']['tmp_name']),
                        'image/png'  => imagecreatefrompng($_FILES['avatar']['tmp_name']),
                        default      => imagecreatefromwebp($_FILES['avatar']['tmp_name']),
                    };
                    if ($src) { imagewebp($src, $dir.$avatarName, 85); imagedestroy($src); }
                }
            }
            $db->execute(
                "UPDATE users SET name=?,phone=?,avatar=?,updated_at=NOW() WHERE id=?",
                [$name,$phone,$avatarName,$user['id']]
            );
            $_SESSION['user_name'] = $name;
            $msg = 'Profile আপডেট হয়েছে!';
            $user = $auth->currentUser();
        }
    }

    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $db->queryOne("SELECT password_hash FROM users WHERE id=?",[$user['id']])['password_hash'])) {
            $err = 'বর্তমান password সঠিক নয়।';
        } elseif (strlen($new) < 6) {
            $err = 'নতুন password কমপক্ষে ৬ অক্ষর হতে হবে।';
        } elseif ($new !== $confirm) {
            $err = 'Password দুটি মিলছে না।';
        } else {
            $db->execute(
                "UPDATE users SET password_hash=?,updated_at=NOW() WHERE id=?",
                [password_hash($new, PASSWORD_BCRYPT, ['cost'=>12]), $user['id']]
            );
            $msg = 'Password পরিবর্তন হয়েছে!';
        }
    }
}

// Activity stats
$stats = [
    'wishlist'  => $db->queryOne("SELECT COUNT(*) c FROM wishlist  WHERE user_id=?",[$user['id']])['c'],
    'inquiries' => $db->queryOne("SELECT COUNT(*) c FROM inquiries WHERE sender_id=?",[$user['id']])['c'],
    'bookings'  => $db->queryOne("SELECT COUNT(*) c FROM bookings  WHERE user_id=?",[$user['id']])['c'],
];
if ($user['role'] === 'agent') {
    $stats['properties'] = $db->queryOne("SELECT COUNT(*) c FROM properties WHERE user_id=?",[$user['id']])['c'];
}
?>

<div class="profile-page">
  <!-- 3D Header -->
  <div class="profile-header">
    <div class="ph-bg">
      <div class="ph-orb ph-orb-1"></div>
      <div class="ph-orb ph-orb-2"></div>
      <div class="ph-grid"></div>
    </div>
    <div class="container">
      <div class="ph-inner">
        <!-- Avatar -->
        <div class="ph-avatar-wrap" id="avatarWrap">
          <div class="ph-avatar" id="avatarDisplay">
            <?php if ($user['avatar']): ?>
            <img src="<?= UPLOAD_URL ?>avatars/<?= htmlspecialchars($user['avatar']) ?>"
                 alt="avatar" id="avatarImg">
            <?php else: ?>
            <span><?= strtoupper(substr($user['name'],0,2)) ?></span>
            <?php endif; ?>
          </div>
          <div class="ph-avatar-status"></div>
          <label class="ph-avatar-edit" title="ছবি পরিবর্তন করুন">
            <i class="bi bi-camera-fill"></i>
            <input type="file" name="avatar" form="profileForm" id="avatarInput"
                   accept="image/*" style="display:none" onchange="previewAvatar(this)">
          </label>
        </div>

        <div class="ph-info">
          <div class="ph-role-badge role-<?= $user['role'] ?>">
            <i class="bi <?= match($user['role']){'admin'=>'bi-shield-check','agent'=>'bi-person-badge','buyer'=>'bi-person-check'} ?> me-1"></i>
            <?= ucfirst($user['role']) ?>
          </div>
          <h1 class="ph-name"><?= htmlspecialchars($user['name']) ?></h1>
          <p class="ph-email">
            <i class="bi bi-envelope me-2"></i><?= htmlspecialchars($user['email']) ?>
          </p>
          <?php if ($user['phone']): ?>
          <p class="ph-phone">
            <i class="bi bi-telephone me-2"></i><?= htmlspecialchars($user['phone']) ?>
          </p>
          <?php endif; ?>
        </div>

        <!-- Stats -->
        <div class="ph-stats">
          <?php foreach ($stats as $k => $v): ?>
          <div class="ph-stat">
            <strong><?= $v ?></strong>
            <span><?= match($k){'wishlist'=>'Wishlist','inquiries'=>'Inquiry','bookings'=>'Booking','properties'=>'Property'} ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Content -->
  <div class="container py-5">
    <?php if ($msg): ?><div class="profile-alert success"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="profile-alert error"><i class="bi bi-x-circle me-2"></i><?= htmlspecialchars($err) ?></div><?php endif; ?>

    <div class="row g-4">

      <!-- Tabs -->
      <div class="col-lg-3">
        <div class="profile-nav">
          <a href="#info"     class="pn-item active" data-tab="info">
            <i class="bi bi-person"></i> ব্যক্তিগত তথ্য
          </a>
          <a href="#password" class="pn-item" data-tab="password">
            <i class="bi bi-lock"></i> Password পরিবর্তন
          </a>
          <a href="#security" class="pn-item" data-tab="security">
            <i class="bi bi-shield-check"></i> নিরাপত্তা
          </a>
          <a href="#notifications" class="pn-item" data-tab="notifications">
            <i class="bi bi-bell"></i> Notification
          </a>
        </div>
      </div>

      <!-- Panels -->
      <div class="col-lg-9">

        <!-- Info Panel -->
        <div class="profile-panel active" id="panel-info">
          <div class="pp-header">
            <h3><i class="bi bi-person-circle me-2 text-accent"></i>ব্যক্তিগত তথ্য</h3>
          </div>
          <form method="POST" enctype="multipart/form-data" id="profileForm">
            <input type="hidden" name="action" value="update_profile">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="pf-field">
                  <label>পূর্ণ নাম <span class="req">*</span></label>
                  <input type="text" name="name" class="pf-input" required
                         value="<?= htmlspecialchars($user['name']) ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="pf-field">
                  <label>Email</label>
                  <input type="email" class="pf-input" disabled
                         value="<?= htmlspecialchars($user['email']) ?>">
                  <small class="pf-hint">Email পরিবর্তন করা যাবে না</small>
                </div>
              </div>
              <div class="col-md-6">
                <div class="pf-field">
                  <label>ফোন নম্বর</label>
                  <input type="tel" name="phone" class="pf-input"
                         placeholder="01XXXXXXXXX"
                         value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="pf-field">
                  <label>ভূমিকা</label>
                  <input type="text" class="pf-input" disabled
                         value="<?= ucfirst($user['role']) ?>">
                </div>
              </div>
            </div>
            <button type="submit" class="pp-save-btn">
              <i class="bi bi-check-circle me-2"></i>পরিবর্তন সংরক্ষণ করুন
              <div class="pp-btn-glow"></div>
            </button>
          </form>
        </div>

        <!-- Password Panel -->
        <div class="profile-panel" id="panel-password">
          <div class="pp-header">
            <h3><i class="bi bi-lock-fill me-2 text-accent"></i>Password পরিবর্তন</h3>
          </div>
          <form method="POST" class="pass-form">
            <input type="hidden" name="action" value="change_password">
            <div class="pf-field">
              <label>বর্তমান Password</label>
              <div class="pf-input-icon">
                <input type="password" name="current_password" class="pf-input"
                       placeholder="বর্তমান password" required id="curPass">
                <button type="button" class="pf-eye" onclick="toggleField('curPass',this)">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>
            <div class="pf-field">
              <label>নতুন Password</label>
              <div class="pf-input-icon">
                <input type="password" name="new_password" class="pf-input"
                       placeholder="কমপক্ষে ৬ অক্ষর" required id="newPass2"
                       oninput="strengthCheck(this.value)">
                <button type="button" class="pf-eye" onclick="toggleField('newPass2',this)">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
              <div class="strength-meter mt-2"><div class="strength-bar" id="sBar"></div></div>
              <small id="sLabel" style="color:var(--secondary)"></small>
            </div>
            <div class="pf-field">
              <label>Password নিশ্চিত করুন</label>
              <div class="pf-input-icon">
                <input type="password" name="confirm_password" class="pf-input"
                       placeholder="আবার দিন" required id="confPass2">
                <button type="button" class="pf-eye" onclick="toggleField('confPass2',this)">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>
            <button type="submit" class="pp-save-btn">
              <i class="bi bi-key me-2"></i>Password পরিবর্তন করুন
              <div class="pp-btn-glow"></div>
            </button>
          </form>
        </div>

        <!-- Security Panel -->
        <div class="profile-panel" id="panel-security">
          <div class="pp-header">
            <h3><i class="bi bi-shield-check me-2 text-accent"></i>নিরাপত্তা সেটিংস</h3>
          </div>
          <div class="security-list">
            <div class="security-item">
              <div class="si-icon green"><i class="bi bi-patch-check-fill"></i></div>
              <div class="si-info">
                <strong>Email যাচাই</strong>
                <small><?= $user['email'] ?></small>
              </div>
              <span class="si-status verified">যাচাইকৃত</span>
            </div>
            <div class="security-item">
              <div class="si-icon blue"><i class="bi bi-phone"></i></div>
              <div class="si-info">
                <strong>দুই-স্তরীয় যাচাই (2FA)</strong>
                <small>SMS OTP দিয়ে login সুরক্ষিত করুন</small>
              </div>
              <button class="si-toggle">শীঘ্রই আসছে</button>
            </div>
            <div class="security-item">
              <div class="si-icon gold"><i class="bi bi-clock-history"></i></div>
              <div class="si-info">
                <strong>সর্বশেষ Login</strong>
                <small>এই session থেকে</small>
              </div>
              <span class="si-status"><?= date('d M Y, H:i') ?></span>
            </div>
            <div class="security-item danger">
              <div class="si-icon red"><i class="bi bi-box-arrow-right"></i></div>
              <div class="si-info">
                <strong>সব ডিভাইস থেকে logout</strong>
                <small>সব session শেষ করুন</small>
              </div>
              <a href="?page=logout" class="si-btn-danger">Logout সব</a>
            </div>
          </div>
        </div>

        <!-- Notifications Panel -->
        <div class="profile-panel" id="panel-notifications">
          <div class="pp-header">
            <h3><i class="bi bi-bell me-2 text-accent"></i>Notification সেটিংস</h3>
          </div>
          <div class="notif-list">
            <?php
            $notifOptions = [
              ['icon'=>'bi-envelope','title'=>'Email Notification','desc'=>'Inquiry ও booking এর email পান','key'=>'email_notif','default'=>true],
              ['icon'=>'bi-phone','title'=>'SMS Notification','desc'=>'গুরুত্বপূর্ণ আপডেট SMS এ পান','key'=>'sms_notif','default'=>false],
              ['icon'=>'bi-bell-fill','title'=>'Push Notification','desc'=>'Browser notification চালু করুন','key'=>'push_notif','default'=>true],
              ['icon'=>'bi-house','title'=>'নতুন Property Alert','desc'=>'আপনার search এর নতুন property এলে জানান','key'=>'prop_alert','default'=>true],
              ['icon'=>'bi-chat','title'=>'Inquiry Reply','desc'=>'Agent reply দিলে notification','key'=>'inq_reply','default'=>true],
            ];
            foreach ($notifOptions as $opt):
            ?>
            <div class="notif-item">
              <div class="ni-icon"><i class="bi <?= $opt['icon'] ?>"></i></div>
              <div class="ni-info">
                <strong><?= $opt['title'] ?></strong>
                <small><?= $opt['desc'] ?></small>
              </div>
              <label class="toggle-switch">
                <input type="checkbox" <?= $opt['default'] ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
              </label>
            </div>
            <?php endforeach; ?>
          </div>
          <button class="pp-save-btn mt-4" onclick="showToast('Notification সেটিংস সংরক্ষিত!','success')">
            <i class="bi bi-check-circle me-2"></i>সংরক্ষণ করুন
            <div class="pp-btn-glow"></div>
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<style>
/* Profile 3D Page */
.profile-page { background: var(--bg); min-height: 100vh; }
.profile-header { position: relative; padding: 60px 0 40px; overflow: hidden; background: #0F172A; }
.ph-bg { position: absolute; inset: 0; }
.ph-orb { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.15; }
.ph-orb-1 { width: 400px; height: 400px; background: #C5A059; top: -150px; left: -100px; }
.ph-orb-2 { width: 300px; height: 300px; background: #3B82F6; bottom: -100px; right: -50px; }
.ph-grid { position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,0.02) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.02) 1px,transparent 1px); background-size: 40px 40px; }

.ph-inner { position: relative; z-index: 2; display: flex; align-items: flex-end; gap: 28px; flex-wrap: wrap; }

.ph-avatar-wrap { position: relative; flex-shrink: 0; }
.ph-avatar {
  width: 100px; height: 100px; border-radius: 50%;
  background: linear-gradient(135deg, #C5A059, #b8912e);
  border: 3px solid rgba(197,160,89,0.4);
  display: flex; align-items: center; justify-content: center;
  font-weight: 800; font-size: 1.8rem; color: #0F172A;
  box-shadow: 0 8px 32px rgba(0,0,0,0.4); overflow: hidden;
}
.ph-avatar img { width: 100%; height: 100%; object-fit: cover; }
.ph-avatar-status {
  position: absolute; bottom: 6px; right: 6px;
  width: 16px; height: 16px; border-radius: 50%;
  background: #22C55E; border: 2px solid #0F172A;
}
.ph-avatar-edit {
  position: absolute; bottom: 0; right: 0;
  width: 30px; height: 30px; border-radius: 50%;
  background: #C5A059; color: #0F172A; font-size: 0.75rem;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all 0.2s; border: 2px solid #0F172A;
}
.ph-avatar-edit:hover { transform: scale(1.1); }

.ph-role-badge {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 4px 12px; border-radius: 50px; font-size: 0.72rem; font-weight: 700;
  margin-bottom: 8px;
}
.role-admin { background: rgba(239,68,68,0.2);  color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }
.role-agent { background: rgba(197,160,89,0.2); color: #C5A059; border: 1px solid rgba(197,160,89,0.3); }
.role-buyer { background: rgba(34,197,94,0.2);  color: #22C55E; border: 1px solid rgba(34,197,94,0.3); }

.ph-name  { color: #fff; font-size: 1.6rem; font-weight: 800; margin-bottom: 6px; }
.ph-email,.ph-phone { color: rgba(255,255,255,0.5); font-size: 0.85rem; margin-bottom: 4px; }

.ph-stats { margin-left: auto; display: flex; gap: 20px; flex-wrap: wrap; }
.ph-stat  { text-align: center; padding: 12px 20px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; }
.ph-stat strong { display: block; font-family: 'Montserrat',sans-serif; font-size: 1.5rem; font-weight: 800; color: #C5A059; }
.ph-stat span   { font-size: 0.75rem; color: rgba(255,255,255,0.45); }

/* Alert */
.profile-alert { padding: 12px 18px; border-radius: 10px; font-size: 0.875rem; margin-bottom: 20px; display: flex; align-items: center; }
.profile-alert.success { background: rgba(34,197,94,0.1);  border: 1px solid rgba(34,197,94,0.2);  color: #22C55E; }
.profile-alert.error   { background: rgba(239,68,68,0.1);  border: 1px solid rgba(239,68,68,0.2);  color: #ef4444; }

/* Nav */
.profile-nav { background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 8px; position: sticky; top: 80px; }
.pn-item {
  display: flex; align-items: center; gap: 10px; padding: 11px 14px;
  border-radius: 10px; color: var(--secondary); text-decoration: none;
  font-size: 0.875rem; font-weight: 600; transition: all 0.2s; margin-bottom: 2px;
}
.pn-item:hover  { background: var(--bg); color: var(--primary); }
.pn-item.active { background: rgba(197,160,89,0.1); color: var(--accent); }

/* Panel */
.profile-panel { display: none; background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 28px; }
.profile-panel.active { display: block; }
.pp-header { margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border); }
.pp-header h3 { font-size: 1rem; font-weight: 700; margin: 0; }

/* Fields */
.pf-field { margin-bottom: 18px; }
.pf-field label { display: block; font-size: 0.78rem; font-weight: 700; color: var(--secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
.req { color: #ef4444; }
.pf-input { width: 100%; border: 1.5px solid var(--border); border-radius: 10px; padding: 11px 14px; font-size: 0.925rem; outline: none; transition: border-color 0.2s; font-family: 'Inter', sans-serif; }
.pf-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(197,160,89,0.1); }
.pf-input:disabled { background: var(--bg); color: var(--secondary); cursor: not-allowed; }
.pf-hint { font-size: 0.72rem; color: var(--secondary); margin-top: 4px; display: block; }
.pf-input-icon { position: relative; }
.pf-input-icon .pf-input { padding-right: 44px; }
.pf-eye { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--secondary); cursor: pointer; font-size: 0.9rem; }

/* Strength */
.strength-meter { height: 4px; background: var(--border); border-radius: 2px; }
.strength-bar   { height: 100%; border-radius: 2px; transition: all 0.3s; width: 0; }

/* Save Button */
.pp-save-btn {
  background: linear-gradient(135deg, #C5A059, #b8912e);
  color: #0F172A; border: none; padding: 12px 28px; border-radius: 10px;
  font-weight: 800; font-size: 0.925rem; cursor: pointer;
  position: relative; overflow: hidden; transition: all 0.2s;
  display: inline-flex; align-items: center;
}
.pp-save-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(197,160,89,0.4); }
.pp-btn-glow { position: absolute; inset: 0; background: linear-gradient(135deg,rgba(255,255,255,0.2),transparent); pointer-events: none; }

/* Security */
.security-list { display: flex; flex-direction: column; gap: 12px; }
.security-item { display: flex; align-items: center; gap: 14px; padding: 16px; background: var(--bg); border: 1px solid var(--border); border-radius: 12px; }
.si-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
.si-icon.green  { background: rgba(34,197,94,0.12);  color: #22C55E; }
.si-icon.blue   { background: rgba(59,130,246,0.12); color: #3B82F6; }
.si-icon.gold   { background: rgba(197,160,89,0.12); color: #C5A059; }
.si-icon.red    { background: rgba(239,68,68,0.12);  color: #ef4444; }
.si-info { flex: 1; }
.si-info strong { display: block; font-size: 0.9rem; }
.si-info small  { color: var(--secondary); font-size: 0.78rem; }
.si-status { font-size: 0.78rem; font-weight: 600; color: var(--secondary); }
.si-status.verified { color: #22C55E; }
.si-toggle { font-size: 0.75rem; color: var(--secondary); background: var(--border); border: none; padding: 4px 10px; border-radius: 6px; cursor: not-allowed; }
.si-btn-danger { font-size: 0.78rem; color: #ef4444; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); padding: 6px 12px; border-radius: 8px; text-decoration: none; white-space: nowrap; }

/* Notifications */
.notif-list { display: flex; flex-direction: column; gap: 12px; }
.notif-item { display: flex; align-items: center; gap: 14px; padding: 16px; background: var(--bg); border: 1px solid var(--border); border-radius: 12px; }
.ni-icon { width: 40px; height: 40px; border-radius: 10px; background: rgba(197,160,89,0.12); color: var(--accent); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
.ni-info { flex: 1; }
.ni-info strong { display: block; font-size: 0.9rem; }
.ni-info small  { color: var(--secondary); font-size: 0.78rem; }

.toggle-switch { display: inline-flex; align-items: center; cursor: pointer; }
.toggle-switch input { display: none; }
.toggle-slider { width: 44px; height: 24px; background: var(--border); border-radius: 50px; position: relative; transition: all 0.2s; }
.toggle-slider::after { content:''; width:18px; height:18px; background:#fff; border-radius:50%; position:absolute; top:3px; left:3px; transition:all 0.2s; box-shadow:0 1px 4px rgba(0,0,0,0.2); }
.toggle-switch input:checked + .toggle-slider { background: var(--accent); }
.toggle-switch input:checked + .toggle-slider::after { left: 23px; }
</style>

<script>
// Tab switching
document.querySelectorAll('.pn-item').forEach(item => {
  item.addEventListener('click', e => {
    e.preventDefault();
    const tab = item.dataset.tab;
    document.querySelectorAll('.pn-item').forEach(i => i.classList.remove('active'));
    document.querySelectorAll('.profile-panel').forEach(p => p.classList.remove('active'));
    item.classList.add('active');
    document.getElementById('panel-' + tab)?.classList.add('active');
  });
});

// Avatar preview
function previewAvatar(input) {
  if (!input.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    const disp = document.getElementById('avatarDisplay');
    disp.innerHTML = `<img src="${e.target.result}" alt="avatar" id="avatarImg">`;
  };
  reader.readAsDataURL(input.files[0]);
}

// Password toggle
function toggleField(id, btn) {
  const f = document.getElementById(id);
  f.type  = f.type === 'text' ? 'password' : 'text';
  btn.querySelector('i').className = f.type === 'text' ? 'bi bi-eye-slash' : 'bi bi-eye';
}

// Strength checker
function strengthCheck(val) {
  const bar   = document.getElementById('sBar');
  const label = document.getElementById('sLabel');
  if (!bar) return;
  let s = 0;
  if (val.length>=6) s++;
  if (val.length>=10) s++;
  if (/[A-Z]/.test(val)) s++;
  if (/[0-9]/.test(val)) s++;
  if (/[^A-Za-z0-9]/.test(val)) s++;
  const levels = [
    {pct:'20%',color:'#ef4444',text:'খুব দুর্বল'},
    {pct:'40%',color:'#f97316',text:'দুর্বল'},
    {pct:'60%',color:'#eab308',text:'মাঝারি'},
    {pct:'80%',color:'#3b82f6',text:'ভালো'},
    {pct:'100%',color:'#22c55e',text:'শক্তিশালী'},
  ];
  const l = levels[Math.min(s-1,4)] || levels[0];
  bar.style.width = val ? l.pct : '0';
  bar.style.background = l.color;
  label.textContent = val ? l.text : '';
  label.style.color = l.color;
}
</script>