<?php
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Property.php';

$auth = Auth::getInstance();
$auth->requireRole('buyer');

$propClass = Property::getInstance();
$db        = Database::getInstance();
$userId    = $_SESSION['user_id'];
$successMsg = '';
$errorMsg   = '';

// ── প্রোফাইল আপডেট হ্যান্ডলার (POST Action) ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($name)) {
        if (!empty($password)) {
            // পাসওয়ার্ডসহ নাম আপডেট করার কুয়েরি
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $update = $db->execute("UPDATE users SET name = ?, password = ? WHERE id = ?", [$name, $hashedPassword, $userId]);
        } else {
            // শুধু নাম আপডেট করার কুয়েরি
            $update = $db->execute("UPDATE users SET name = ? WHERE id = ?", [$name, $userId]);
        }

        if ($update) {
            $_SESSION['user_name'] = $name; // সেশন ডাটা সাথে সাথে আপডেট
            $successMsg = 'আপনার প্রোফাইল তথ্য সফলভাবে আপডেট করা হয়েছে!';
        } else {
            $errorMsg = 'তথ্য আপডেট করতে সমস্যা হয়েছে। আবার চেষ্টা করুন।';
        }
    } else {
        $errorMsg = 'নামের ঘরটি খালি রাখা যাবে না।';
    }
}

// ── ডাটা রিট্রিভাল ───────────────────────────────────────────
$wishlist   = $propClass->getWishlist($userId) ?? [];
$inquiries  = $db->query(
    "SELECT i.*, p.title AS prop_title, p.id AS prop_id
     FROM inquiries i JOIN properties p ON p.id = i.property_id
     WHERE i.sender_id = ? ORDER BY i.created_at DESC LIMIT 10",
    [$userId]
) ?? [];

$bookings   = $db->query(
    "SELECT b.*, p.title AS prop_title
     FROM bookings b JOIN properties p ON p.id = b.property_id
     WHERE b.user_id = ? ORDER BY b.tour_date DESC LIMIT 10",
    [$userId]
) ?? [];

$recentViewed = $propClass->getRecentlyViewed($userId, 4) ?? [];

$activeTab = $_GET['tab'] ?? 'overview';
?>

<div class="dashboard-page">
  <div class="dashboard-sidebar">
    <div class="dash-user-info">
      <div class="dash-avatar">
        <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
      </div>
      <h6><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></h6>
      <small class="text-accent">Buyer</small>
    </div>
    <nav class="dash-nav">
      <?php
      $tabs = [
        'overview'  => ['bi-speedometer2', 'ওভারভিউ'],
        'wishlist'  => ['bi-heart',         'Wishlist (' . count($wishlist) . ')'],
        'inquiries' => ['bi-chat-dots',     'Inquiry (' . count($inquiries) . ')'],
        'bookings'  => ['bi-calendar-check','বুকিং (' . count($bookings) . ')'],
        'searches'  => ['bi-search',        'Saved Searches'],
        'profile'   => ['bi-person',        'প্রোফাইল'],
      ];
      foreach ($tabs as $key => [$icon, $label]):
      ?>
      <a href="?page=buyer-dashboard&tab=<?= $key ?>"
         class="dash-nav-item <?= $activeTab === $key ? 'active' : '' ?>">
        <i class="bi <?= $icon ?>"></i> <?= $label ?>
      </a>
      <?php endforeach; ?>
      <a href="?page=logout" class="dash-nav-item text-danger">
        <i class="bi bi-box-arrow-right"></i> Logout
      </a>
    </nav>
  </div>

  <div class="dashboard-main">
    <!-- সফল বা ব্যর্থতার মেসেজ অ্যালার্ট -->
    <?php if (!empty($successMsg)): ?>
      <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i><?= $successMsg ?></div>
    <?php endif; ?>
    <?php if (!empty($errorMsg)): ?>
      <div class="alert alert-danger mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= $errorMsg ?></div>
    <?php endif; ?>
    <!-- ===== OVERVIEW ===== -->
        <!-- ===== OVERVIEW ===== -->
    <?php if ($activeTab === 'overview'): ?>
    <div class="dash-header">
      <h2>স্বাগতম, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'] ?? 'User')[0]) ?>!</h2>
      <p>আপনার রিয়েল এস্টেট যাত্রার সারসংক্ষেপ</p>
    </div>

    <div class="row g-3 mb-4">
      <?php
      $cards = [
        ['bi-heart',         count($wishlist),   'Wishlist',      'gold'],
        ['bi-chat-dots',     count($inquiries),  'Inquiries',     'blue'],
        ['bi-calendar-check',count($bookings),   'Bookings',      'green'],
        ['bi-eye',           count($recentViewed),'সম্প্রতি দেখা','purple'],
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

    <!-- Recently Viewed -->
    <?php if (!empty($recentViewed)): ?>
    <h5 class="mb-3">সম্প্রতি দেখেছেন</h5>
    <div class="row g-3">
      <?php foreach ($recentViewed as $prop): ?>
      <div class="col-md-6 col-lg-3">
        <?= renderPropertyCard($prop, $auth) ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ===== WISHLIST ===== -->
    <?php elseif ($activeTab === 'wishlist'): ?>
    <div class="dash-header">
      <h2><i class="bi bi-heart me-2 text-accent"></i>আমার Wishlist</h2>
    </div>
    <?php if (empty($wishlist)): ?>
    <div class="empty-dash">
      <i class="bi bi-heart"></i>
      <h5>Wishlist খালি</h5>
      <p>পছন্দের প্রপার্টিতে ♥ চাপুন</p>
      <a href="?page=listing" class="btn-accent-sm">প্রপার্টি দেখুন</a>
    </div>
    <?php else: ?>
    <div class="row g-3">
      <?php foreach ($wishlist as $prop): ?>
      <div class="col-md-6 col-lg-4">
        <?= renderPropertyCard($prop, $auth) ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ===== INQUIRIES ===== -->
    <?php elseif ($activeTab === 'inquiries'): ?>
    <div class="dash-header">
      <h2><i class="bi bi-chat-dots me-2 text-accent"></i>আমার Inquiries</h2>
    </div>
    <?php if (empty($inquiries)): ?>
    <div class="empty-dash">
      <i class="bi bi-chat-dots"></i>
      <h5>কোনো Inquiry পাওয়া যায়নি</h5>
      <p>প্রপার্টি নিয়ে জানতে এজেন্টের সাথে যোগাযোগ করুন</p>
    </div>
    <?php else: ?>
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>প্রপার্টি</th><th>বার্তা</th><th>উত্তর</th><th>স্ট্যাটাস</th><th>তারিখ</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($inquiries as $inq): ?>
          <tr>
            <td>
              <a href="?page=property&id=<?= $inq['prop_id'] ?>">
                <?= htmlspecialchars(substr($inq['prop_title'], 0, 30)) ?>...
              </a>
            </td>
            <td><?= htmlspecialchars(substr($inq['message'], 0, 40)) ?>...</td>
            <td>
              <?= $inq['reply']
                ? htmlspecialchars(substr($inq['reply'], 0, 40)) . '...'
                : '<span class="text-muted">উত্তর আসেনি</span>' ?>
            </td>
            <td>
              <span class="status-badge status-<?= $inq['status'] ?>">
                <?= match($inq['status']) {
                  'pending' => 'অপেক্ষায়',
                  'replied' => 'উত্তর হয়েছে',
                  'closed'  => 'বন্ধ',
                  default   => htmlspecialchars($inq['status'])
                } ?>
              </span>
            </td>
            <td><?= date('d M Y', strtotime($inq['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <!-- ===== BOOKINGS ===== -->
    <?php elseif ($activeTab === 'bookings'): ?>
    <div class="dash-header">
      <h2><i class="bi bi-calendar-check me-2 text-accent"></i>আমার Bookings</h2>
    </div>
    <?php if (empty($bookings)): ?>
    <div class="empty-dash">
      <i class="bi bi-calendar-check"></i>
      <h5>কোনo বুকিং রিকোয়েস্ট নেই</h5>
      <p>প্রপার্টি ভিজিট করতে আজই বুকিং করুন</p>
    </div>
    <?php else: ?>
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>প্রপার্টি</th><th>তারিখ</th><th>সময়</th><th>স্ট্যাটাস</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bookings as $b): ?>
          <tr>
            <td><?= htmlspecialchars($b['prop_title']) ?></td>
            <td><?= date('d M Y', strtotime($b['tour_date'])) ?></td>
            <td><?= htmlspecialchars($b['tour_time']) ?></td>
            <td>
              <span class="status-badge status-<?= $b['status'] ?>">
                <?= match($b['status']) {
                  'pending'   => 'অপেক্ষায়',
                  'confirmed' => 'নিশ্চিত',
                  'cancelled' => 'বাতিল',
                  'completed' => 'সম্পন্ন',
                  default     => htmlspecialchars($b['status'])
                } ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <!-- ===== SAVED SEARCHES ===== -->
    <?php elseif ($activeTab === 'searches'): ?>
    <div class="dash-header">
      <h2><i class="bi bi-search me-2 text-accent"></i>Saved Searches</h2>
    </div>
    <div class="empty-dash">
      <i class="bi bi-search"></i>
      <h5>কোনো সার্চ সেভ করা নেই</h5>
      <p>আপনার পছন্দের ফিল্টারগুলো সেভ করে রাখলে এখানে দেখতে পাবেন।</p>
    </div>

    <!-- ===== PROFILE ===== -->
    <?php elseif ($activeTab === 'profile'): ?>
    <div class="dash-header">
      <h2><i class="bi bi-person me-2 text-accent"></i>প্রোফাইল সেটিংস</h2>
    </div>
    <div class="card p-4" style="background: var(--bg-surface, #fff); border: 1px solid var(--border-color, #eee); border-radius: 8px;">
      <form method="POST" action="">
        <input type="hidden" name="update_profile" value="1">
        <div class="mb-3">
          <label class="form-label fw-semibold">সম্পূর্ণ নাম</label>
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">ইমেইল ঠিকানা</label>
          <input type="email" class="form-control" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '—') ?>" disabled>
          <small class="text-muted">নিরাপত্তার স্বার্থে ইমেইল পরিবর্তন করা যাবে না।</small>
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
