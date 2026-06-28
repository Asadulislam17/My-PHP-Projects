<?php
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Property.php';

$auth = Auth::getInstance();
$auth->requireRole('agent');

$db     = Database::getInstance();
$userId = $_SESSION['user_id'];
$activeTab = $_GET['tab'] ?? 'overview';
$successMsg = '';
$errorMsg   = '';

// ── ১. ইনকোয়ারি উত্তর সেভ করার হ্যান্ডলার (POST) ──────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inquiry_id'])) {
    $inqId = (int)$_POST['inquiry_id'];
    $reply = trim($_POST['reply'] ?? '');

    if (!empty($reply)) {
        $db->execute("UPDATE inquiries SET reply = ?, status = 'replied' WHERE id = ? AND agent_id = ?", [$reply, $inqId, $userId]);
        $successMsg = 'ইনকোয়ারির উত্তর সফলভাবে পাঠানো হয়েছে!';
    }
}

// ── ২. প্রোফাইল আপডেট করার হ্যান্ডলার (POST) ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_agent_profile') {
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($name)) {
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $db->execute("UPDATE users SET name = ?, password = ? WHERE id = ?", [$name, $hashedPassword, $userId]);
        } else {
            $db->execute("UPDATE users SET name = ? WHERE id = ?", [$name, $userId]);
        }
        $_SESSION['user_name'] = $name;
        $successMsg = 'আপনার প্রোফাইল তথ্য সফলভাবে আপডেট করা হয়েছে!';
    }
}

// ── Agent Stats ────────────────────────────────────────────────
$totalProps = $db->queryOne("SELECT COUNT(*) AS cnt FROM properties WHERE user_id = ?", [$userId])['cnt'] ?? 0;
$approvedProps = $db->queryOne("SELECT COUNT(*) AS cnt FROM properties WHERE user_id = ? AND status = 'approved'", [$userId])['cnt'] ?? 0;
$totalViews = $db->queryOne("SELECT SUM(views_count) AS total FROM properties WHERE user_id = ?", [$userId])['total'] ?? 0;
$pendingInquiries = $db->queryOne("SELECT COUNT(*) AS cnt FROM inquiries WHERE agent_id = ? AND status = 'pending'", [$userId])['cnt'] ?? 0;

// Agent's Properties
$myProperties = $db->query(
    "SELECT p.*, pt.name AS type_name, a.name AS area_name,
            (SELECT image_path FROM property_images WHERE property_id = p.id AND is_cover = 1 LIMIT 1) AS cover
     FROM properties p
     JOIN property_types pt ON pt.id = p.type_id
     JOIN areas a ON a.id = p.area_id
     WHERE p.user_id = ? ORDER BY p.created_at DESC", [$userId]
) ?? [];

// Subscription
$subscription = $db->queryOne(
    "SELECT s.*, sp.name AS plan_name, sp.max_listings
     FROM subscriptions s JOIN subscription_plans sp ON sp.id = s.plan_id
     WHERE s.user_id = ? AND s.status = 'active' ORDER BY s.expires_at DESC LIMIT 1", [$userId]
);

// Inquiries
$inquiries = $db->query(
    "SELECT i.*, p.title AS prop_title, u.name AS sender_name, u.phone AS sender_phone
     FROM inquiries i JOIN properties p ON p.id = i.property_id JOIN users u ON u.id = i.sender_id
     WHERE i.agent_id = ? ORDER BY i.created_at DESC LIMIT 20", [$userId]
) ?? [];

// Bookings (নতুন কুয়েরি সংযোজন)
$bookings = $db->query(
    "SELECT b.*, p.title AS prop_title, u.name AS buyer_name, u.phone AS buyer_phone
     FROM bookings b JOIN properties p ON p.id = b.property_id JOIN users u ON u.id = b.user_id
     WHERE p.user_id = ? ORDER BY b.tour_date DESC LIMIT 20", [$userId]
) ?? [];
?>

<div class="dashboard-page">
  <div class="dashboard-sidebar">
    <div class="dash-user-info">
      <div class="dash-avatar agent"><?= strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)) ?></div>
      <h6><?= htmlspecialchars($_SESSION['user_name'] ?? 'Agent') ?></h6>
      <small class="text-accent">Agent</small>
    </div>
    <nav class="dash-nav">
      <?php
      $tabs = [
        'overview'   => ['bi-speedometer2',   'ওভারভিউ'],
        'properties' => ['bi-house',          'আমার Property'],
        'add'        => ['bi-plus-circle',    'Property যোগ করুন'],
        'inquiries'  => ['bi-chat-dots',      'Inquiries (' . $pendingInquiries . ')'],
        'bookings'   => ['bi-calendar',       'Bookings (' . count($bookings) . ')'],
        'subscription'=>['bi-credit-card',    'Subscription'],
        'profile'    => ['bi-person',         'প্রোফাইল'],
      ];
      foreach ($tabs as $key => [$icon, $label]):
      ?>
      <a href="<?= $key === 'add' ? '?page=add-property' : '?page=agent-dashboard&tab=' . $key ?>"
         class="dash-nav-item <?= $activeTab === $key ? 'active' : '' ?>">
        <i class="bi <?= $icon ?>"></i> <?= $label ?>
      </a>
      <?php endforeach; ?>
      <a href="?page=logout" class="dash-nav-item text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </nav>
  </div>

  <div class="dashboard-main">
    <?php if (!empty($successMsg)): ?>
      <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= $successMsg ?></div>
    <?php endif; ?>
    <!-- ===== OVERVIEW ===== -->
    <?php if ($activeTab === 'overview'): ?>
    <div class="dash-header">
      <h2>Agent Dashboard</h2>
      <p><?= date('d F Y') ?> — আপনার কার্যক্রমের সারসংক্ষেপ</p>
    </div>

    <div class="row g-3 mb-4">
      <?php
      $cards = [
        ['bi-house',        $totalProps,       'মোট Property',    'gold'],
        ['bi-check-circle', $approvedProps,    'Approved',        'green'],
        ['bi-eye',          number_format($totalViews), 'মোট ভিউ','blue'],
        ['bi-chat-dots',    $pendingInquiries, 'নতুন Inquiry',    'purple'],
      ];
      foreach ($cards as [$icon, $count, $label, $color]):
      ?>
      <div class="col-6 col-md-3">
        <div class="stat-card color-<?= $color ?>">
          <i class="bi <?= $icon ?>"></i>
          <h3><?= $count ?></h3>
          <p><?= $label ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Subscription Status -->
    <?php if ($subscription): ?>
    <div class="subscription-status-card">
      <div class="sub-icon"><i class="bi bi-credit-card-2-front"></i></div>
      <div class="sub-info">
        <h6><?= htmlspecialchars($subscription['plan_name']) ?> Plan</h6>
        <small>মেয়াদ: <?= date('d M Y', strtotime($subscription['expires_at'])) ?> পর্যন্ত</small>
        <div class="sub-usage">
          <span><?= $approvedProps ?>/<?= $subscription['max_listings'] ?> Listings</span>
          <div class="sub-bar">
            <div class="sub-bar-fill" style="width:<?= min(100, ($approvedProps / $subscription['max_listings']) * 100) ?>%"></div>
          </div>
        </div>
      </div>
      <a href="?page=agent-dashboard&tab=subscription" class="btn-accent-sm">আপগ্রেড</a>
    </div>
    <?php else: ?>
    <div class="alert-custom warning">
      <i class="bi bi-exclamation-triangle me-2"></i> কোনো active subscription নেই।
      <a href="?page=agent-dashboard&tab=subscription" class="text-accent fw-bold">এখনই নিন →</a>
    </div>
    <?php endif; ?>

    <!-- Recent Properties Performance -->
    <h5 class="mt-4 mb-3">Property Performance</h5>
    <div class="performance-table-wrap">
      <table class="dash-table">
        <thead>
          <tr><th>প্রপার্টি</th><th>স্ট্যাটাস</th><th>ভিউ</th><th>মূল্য</th></tr>
        </thead>
        <tbody>
          <?php foreach (array_slice($myProperties, 0, 5) as $p): ?>
          <tr>
            <td><a href="?page=property&id=<?= $p['id'] ?>"><?= htmlspecialchars(substr($p['title'], 0, 35)) ?>...</a></td>
            <td>
              <span class="status-badge status-<?= $p['status'] ?>">
                <?= match($p['status']) { 'pending'=>'Pending', 'approved'=>'Approved', 'rejected'=>'Rejected', 'sold'=>'Sold', default=>$p['status'] } ?>
              </span>
            </td>
            <td><?= number_format($p['views_count']) ?></td>
            <td>৳<?= number_format($p['price']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- ===== PROPERTIES ===== -->
    <?php elseif ($activeTab === 'properties'): ?>
    <div class="dash-header">
      <div>
        <h2>আমার Properties</h2>
        <p><?= count($myProperties) ?> টি property</p>
      </div>
      <a href="?page=add-property" class="btn-accent-sm"><i class="bi bi-plus me-1"></i>নতুন যোগ করুন</a>
    </div>
    <div class="row g-3">
      <?php foreach ($myProperties as $p): ?>
      <div class="col-md-6">
        <div class="agent-prop-card">
          <?php $cover = $p['cover'] ? UPLOAD_URL . 'properties/' . $p['cover'] : APP_URL . '/assets/images/no-image.webp'; ?>
          <img src="<?= $cover ?>" alt="" class="agent-prop-img">
          <div class="agent-prop-info">
            <div class="d-flex justify-content-between align-items-start">
              <h6><?= htmlspecialchars(substr($p['title'], 0, 40)) ?>...</h6>
              <span class="status-badge status-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span>
            </div>
            <small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($p['area_name']) ?></small>
            <div class="fw-bold text-accent mt-1">৳<?= number_format($p['price']) ?></div>
            <div class="agent-prop-actions">
              <a href="?page=property&id=<?= $p['id'] ?>" class="btn-xs"><i class="bi bi-eye"></i> দেখুন</a>
              <a href="?page=edit-property&id=<?= $p['id'] ?>" class="btn-xs blue"><i class="bi bi-pencil"></i> Edit</a>
              <button onclick="deleteProperty(<?= $p['id'] ?>)" class="btn-xs red"><i class="bi bi-trash"></i> Delete</button>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <!-- ===== INQUIRIES ===== -->
    <?php elseif ($activeTab === 'inquiries'): ?>
    <div class="dash-header"><h2>Inquiries পরিচালনা করুন</h2></div>
    <?php if (empty($inquiries)): ?>
      <p class="text-muted">কোনো ইনকোয়ারি পাওয়া যায়নি।</p>
    <?php endif; ?>
    <?php foreach ($inquiries as $inq): ?>
    <div class="inquiry-item">
      <div class="inquiry-sender">
        <div class="inquiry-avatar"><?= strtoupper(substr($inq['sender_name'], 0, 1)) ?></div>
        <div>
          <strong><?= htmlspecialchars($inq['sender_name']) ?></strong>
          <small><?= htmlspecialchars($inq['sender_phone'] ?? '') ?></small>
        </div>
        <span class="status-badge status-<?= $inq['status'] ?> ms-auto"><?= ucfirst($inq['status']) ?></span>
      </div>
      <div class="inquiry-prop"><i class="bi bi-house me-1 text-accent"></i><?= htmlspecialchars($inq['prop_title']) ?></div>
      <p class="inquiry-msg"><?= htmlspecialchars($inq['message']) ?></p>
      <?php if ($inq['status'] === 'pending'): ?>
      <form method="POST" class="reply-form">
        <input type="hidden" name="inquiry_id" value="<?= $inq['id'] ?>">
        <div class="d-flex gap-2">
          <textarea name="reply" class="form-control" rows="2" placeholder="উত্তর লিখুন..." required></textarea>
          <button type="submit" class="btn-accent-sm">পাঠান</button>
        </div>
      </form>
      <?php elseif ($inq['reply']): ?>
      <div class="inquiry-reply"><i class="bi bi-reply me-1 text-accent"></i><em><?= htmlspecialchars($inq['reply']) ?></em></div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <!-- ===== BOOKINGS (নতুন সংযোজন) ===== -->
    <?php elseif ($activeTab === 'bookings'): ?>
    <div class="dash-header"><h2>গ্রাহকদের বুকিং রিকোয়েস্ট</h2></div>
    <div class="performance-table-wrap">
      <table class="dash-table">
        <thead>
          <tr><th>প্রপার্টি</th><th>গ্রাহকের নাম</th><th>ফোন</th><th>তারিখ</th><th>সময়</th><th>স্ট্যাটাস</th></tr>
        </thead>
        <tbody>
          <?php if (empty($bookings)): ?><tr><td colspan="6" class="text-center text-muted">কোনো বুকিং রিকোয়েস্ট নেই</td></tr><?php endif; ?>
          <?php foreach ($bookings as $b): ?>
          <tr>
            <td><?= htmlspecialchars($b['prop_title']) ?></td>
            <td><?= htmlspecialchars($b['buyer_name']) ?></td>
            <td><?= htmlspecialchars($b['buyer_phone'] ?? '—') ?></td>
            <td><?= date('d M Y', strtotime($b['tour_date'])) ?></td>
            <td><?= htmlspecialchars($b['tour_time']) ?></td>
            <td><span class="status-badge status-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- ===== SUBSCRIPTION ===== -->
    <?php elseif ($activeTab === 'subscription'): ?>
    <div class="dash-header"><h2>Subscription Plans</h2></div>
    <?php $plans = $db->query("SELECT * FROM subscription_plans WHERE is_active = 1") ?? []; ?>
    <div class="row g-4">
      <?php foreach ($plans as $plan): ?>
      <div class="col-md-4">
        <div class="plan-card <?= $plan['name'] === 'Pro' ? 'featured-plan' : '' ?>">
          <?php if ($plan['name'] === 'Pro'): ?><div class="plan-badge">সবচেয়ে জনপ্রিয়</div><?php endif; ?>
          <h4><?= htmlspecialchars($plan['name']) ?></h4>
          <div class="plan-price">৳<?= number_format($plan['price']) ?><small>/মাস</small></div>
          <ul class="plan-features">
            <li><i class="bi bi-check2 text-accent"></i><?= $plan['max_listings'] >= 999 ? 'Unlimited' : $plan['max_listings'] ?> Listings</li>
            <li><i class="bi bi-check2 text-accent"></i><?= $plan['duration_days'] ?> দিন</li>
            <li><i class="bi bi-check2 text-accent"></i>Analytics Dashboard</li>
            <li><i class="bi bi-check2 text-accent"></i>Priority Support</li>
          </ul>
          <a href="?page=payment&plan=<?= $plan['id'] ?>" class="btn-accent w-100">এখনই নিন</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ===== PROFILE (নতুন সংযোজন) ===== -->
    <?php elseif ($activeTab === 'profile'): ?>
    <div class="dash-header mb-4">
      <h2><i class="bi bi-person me-2 text-accent"></i>প্রোফাইল সেটিংস</h2>
      <p>আপনার ব্যক্তিগত তথ্য ও নিরাপত্তা নিশ্চিত করুন</p>
    </div>
    <div class="card p-4" style="background: var(--bg-surface, #fff); border: 1px solid var(--border-color, #eee); border-radius: 8px;">
      <form method="POST" action="">
        <input type="hidden" name="action" value="update_agent_profile">
        <div class="row g-3">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">সম্পূর্ণ নাম</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">ইমেইল ঠিকানা</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '—') ?>" disabled>
            <small class="text-muted">ইমেইল পরিবর্তন করা যাবে না।</small>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">নতুন পাসওয়ার্ড (পরিবর্তন করতে চাইলে)</label>
          <input type="password" name="password" class="form-control" placeholder="নতুন পাসওয়ার্ড লিখুন">
          <small class="text-muted">পাসওয়ার্ড পরিবর্তন না করতে চাইলে ঘরটি ফাঁকা রাখুন।</small>
        </div>
        <button type="submit" class="btn btn-primary px-4" style="background-color: #C5A059; border: none;">তথ্য আপডেট করুন</button>
      </form>
    </div>
    <?php endif; ?>

  </div><!-- /.dashboard-main -->
</div><!-- /.dashboard-page -->
