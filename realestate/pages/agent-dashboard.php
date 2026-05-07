<?php
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Property.php';

$auth = Auth::getInstance();
$auth->requireRole('agent');

$db     = Database::getInstance();
$userId = $_SESSION['user_id'];
$activeTab = $_GET['tab'] ?? 'overview';

// Agent Stats
$totalProps = $db->queryOne(
    "SELECT COUNT(*) AS cnt FROM properties WHERE user_id = ?", [$userId]
)['cnt'];

$approvedProps = $db->queryOne(
    "SELECT COUNT(*) AS cnt FROM properties WHERE user_id = ? AND status = 'approved'", [$userId]
)['cnt'];

$totalViews = $db->queryOne(
    "SELECT SUM(views_count) AS total FROM properties WHERE user_id = ?", [$userId]
)['total'] ?? 0;

$pendingInquiries = $db->queryOne(
    "SELECT COUNT(*) AS cnt FROM inquiries WHERE agent_id = ? AND status = 'pending'", [$userId]
)['cnt'];

// Agent's Properties
$myProperties = $db->query(
    "SELECT p.*, pt.name AS type_name, a.name AS area_name,
            (SELECT image_path FROM property_images WHERE property_id = p.id AND is_cover = 1 LIMIT 1) AS cover
     FROM properties p
     JOIN property_types pt ON pt.id = p.type_id
     JOIN areas a ON a.id = p.area_id
     WHERE p.user_id = ?
     ORDER BY p.created_at DESC",
    [$userId]
);

// Subscription
$subscription = $db->queryOne(
    "SELECT s.*, sp.name AS plan_name, sp.max_listings
     FROM subscriptions s
     JOIN subscription_plans sp ON sp.id = s.plan_id
     WHERE s.user_id = ? AND s.status = 'active'
     ORDER BY s.expires_at DESC LIMIT 1",
    [$userId]
);

// Inquiries
$inquiries = $db->query(
    "SELECT i.*, p.title AS prop_title, u.name AS sender_name, u.phone AS sender_phone
     FROM inquiries i
     JOIN properties p ON p.id = i.property_id
     JOIN users u ON u.id = i.sender_id
     WHERE i.agent_id = ?
     ORDER BY i.created_at DESC LIMIT 20",
    [$userId]
);
?>

<div class="dashboard-page">
  <div class="dashboard-sidebar">
    <div class="dash-user-info">
      <div class="dash-avatar agent">
        <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
      </div>
      <h6><?= htmlspecialchars($_SESSION['user_name']) ?></h6>
      <small class="text-accent">Agent</small>
    </div>
    <nav class="dash-nav">
      <?php
      $tabs = [
        'overview'   => ['bi-speedometer2',   'ওভারভিউ'],
        'properties' => ['bi-house',          'আমার Property'],
        'add'        => ['bi-plus-circle',    'Property যোগ করুন'],
        'inquiries'  => ['bi-chat-dots',      'Inquiries (' . $pendingInquiries . ')'],
        'bookings'   => ['bi-calendar',       'Bookings'],
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
      <a href="?page=logout" class="dash-nav-item text-danger">
        <i class="bi bi-box-arrow-right"></i> Logout
      </a>
    </nav>
  </div>

  <div class="dashboard-main">

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
            <div class="sub-bar-fill"
                 style="width:<?= min(100, ($approvedProps / $subscription['max_listings']) * 100) ?>%">
            </div>
          </div>
        </div>
      </div>
      <a href="?page=agent-dashboard&tab=subscription" class="btn-accent-sm">আপগ্রেড</a>
    </div>
    <?php else: ?>
    <div class="alert-custom warning">
      <i class="bi bi-exclamation-triangle me-2"></i>
      কোনো active subscription নেই।
      <a href="?page=agent-dashboard&tab=subscription" class="text-accent fw-bold">এখনই নিন →</a>
    </div>
    <?php endif; ?>

    <!-- Recent Properties Performance -->
    <h5 class="mt-4 mb-3">Property Performance</h5>
    <div class="performance-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>প্রপার্টি</th>
            <th>স্ট্যাটাস</th>
            <th>ভিউ</th>
            <th>মূল্য</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (array_slice($myProperties, 0, 5) as $p): ?>
          <tr>
            <td>
              <a href="?page=property&id=<?= $p['id'] ?>">
                <?= htmlspecialchars(substr($p['title'], 0, 35)) ?>...
              </a>
            </td>
            <td>
              <span class="status-badge status-<?= $p['status'] ?>">
                <?= match($p['status']) {
                  'pending'  => 'Pending',
                  'approved' => 'Approved',
                  'rejected' => 'Rejected',
                  'sold'     => 'Sold',
                  default    => $p['status']
                } ?>
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
      <a href="?page=add-property" class="btn-accent-sm">
        <i class="bi bi-plus me-1"></i>নতুন যোগ করুন
      </a>
    </div>
    <div class="row g-3">
      <?php foreach ($myProperties as $p): ?>
      <div class="col-md-6">
        <div class="agent-prop-card">
          <?php
          $cover = $p['cover']
            ? UPLOAD_URL . 'properties/' . $p['cover']
            : APP_URL . '/assets/images/no-image.webp';
          ?>
          <img src="<?= $cover ?>" alt="" class="agent-prop-img">
          <div class="agent-prop-info">
            <div class="d-flex justify-content-between align-items-start">
              <h6><?= htmlspecialchars(substr($p['title'], 0, 40)) ?>...</h6>
              <span class="status-badge status-<?= $p['status'] ?>">
                <?= ucfirst($p['status']) ?>
              </span>
            </div>
            <small class="text-muted">
              <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($p['area_name']) ?>
            </small>
            <div class="fw-bold text-accent mt-1">৳<?= number_format($p['price']) ?></div>
            <div class="agent-prop-actions">
              <a href="?page=property&id=<?= $p['id'] ?>" class="btn-xs">
                <i class="bi bi-eye"></i> দেখুন
              </a>
              <a href="?page=edit-property&id=<?= $p['id'] ?>" class="btn-xs blue">
                <i class="bi bi-pencil"></i> Edit
              </a>
              <button onclick="deleteProperty(<?= $p['id'] ?>)" class="btn-xs red">
                <i class="bi bi-trash"></i> Delete
              </button>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ===== INQUIRIES ===== -->
    <?php elseif ($activeTab === 'inquiries'): ?>
    <div class="dash-header">
      <h2>Inquiries পরিচালনা করুন</h2>
    </div>
    <?php foreach ($inquiries as $inq): ?>
    <div class="inquiry-item">
      <div class="inquiry-sender">
        <div class="inquiry-avatar">
          <?= strtoupper(substr($inq['sender_name'], 0, 1)) ?>
        </div>
        <div>
          <strong><?= htmlspecialchars($inq['sender_name']) ?></strong>
          <small><?= htmlspecialchars($inq['sender_phone'] ?? '') ?></small>
        </div>
        <span class="status-badge status-<?= $inq['status'] ?> ms-auto">
          <?= ucfirst($inq['status']) ?>
        </span>
      </div>
      <div class="inquiry-prop">
        <i class="bi bi-house me-1 text-accent"></i>
        <?= htmlspecialchars($inq['prop_title']) ?>
      </div>
      <p class="inquiry-msg"><?= htmlspecialchars($inq['message']) ?></p>
      <?php if ($inq['status'] === 'pending'): ?>
      <form method="POST" class="reply-form">
        <input type="hidden" name="inquiry_id" value="<?= $inq['id'] ?>">
        <div class="d-flex gap-2">
          <textarea name="reply" class="form-control" rows="2"
                    placeholder="উত্তর লিখুন..."></textarea>
          <button type="submit" class="btn-accent-sm">পাঠান</button>
        </div>
      </form>
      <?php elseif ($inq['reply']): ?>
      <div class="inquiry-reply">
        <i class="bi bi-reply me-1 text-accent"></i>
        <em><?= htmlspecialchars($inq['reply']) ?></em>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <!-- ===== SUBSCRIPTION ===== -->
    <?php elseif ($activeTab === 'subscription'): ?>
    <div class="dash-header">
      <h2>Subscription Plans</h2>
    </div>
    <?php
    $plans = $db->query("SELECT * FROM subscription_plans WHERE is_active = 1");
    ?>
    <div class="row g-4">
      <?php foreach ($plans as $plan): ?>
      <div class="col-md-4">
        <div class="plan-card <?= $plan['name'] === 'Pro' ? 'featured-plan' : '' ?>">
          <?php if ($plan['name'] === 'Pro'): ?>
          <div class="plan-badge">সবচেয়ে জনপ্রিয়</div>
          <?php endif; ?>
          <h4><?= htmlspecialchars($plan['name']) ?></h4>
          <div class="plan-price">
            ৳<?= number_format($plan['price']) ?>
            <small>/মাস</small>
          </div>
          <ul class="plan-features">
            <li><i class="bi bi-check2 text-accent"></i>
              <?= $plan['max_listings'] >= 999 ? 'Unlimited' : $plan['max_listings'] ?> Listings
            </li>
            <li><i class="bi bi-check2 text-accent"></i><?= $plan['duration_days'] ?> দিন</li>
            <li><i class="bi bi-check2 text-accent"></i>Analytics Dashboard</li>
            <li><i class="bi bi-check2 text-accent"></i>Priority Support</li>
          </ul>
          <a href="?page=payment&plan=<?= $plan['id'] ?>" class="btn-accent w-100">
            এখনই নিন
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</div>