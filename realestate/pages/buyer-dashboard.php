<?php
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Property.php';

$auth = Auth::getInstance();
$auth->requireRole('buyer');

$propClass = Property::getInstance();
$db        = Database::getInstance();
$userId    = $_SESSION['user_id'];

$wishlist   = $propClass->getWishlist($userId);
$inquiries  = $db->query(
    "SELECT i.*, p.title AS prop_title, p.id AS prop_id
     FROM inquiries i JOIN properties p ON p.id = i.property_id
     WHERE i.sender_id = ? ORDER BY i.created_at DESC LIMIT 10",
    [$userId]
);
$bookings   = $db->query(
    "SELECT b.*, p.title AS prop_title
     FROM bookings b JOIN properties p ON p.id = b.property_id
     WHERE b.user_id = ? ORDER BY b.tour_date DESC LIMIT 10",
    [$userId]
);
$recentViewed = $propClass->getRecentlyViewed($userId, 4);

$activeTab = $_GET['tab'] ?? 'overview';
?>

<div class="dashboard-page">
  <div class="dashboard-sidebar">
    <div class="dash-user-info">
      <div class="dash-avatar">
        <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
      </div>
      <h6><?= htmlspecialchars($_SESSION['user_name']) ?></h6>
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

    <!-- ===== OVERVIEW ===== -->
    <?php if ($activeTab === 'overview'): ?>
    <div class="dash-header">
      <h2>স্বাগতম, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>!</h2>
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
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>প্রপার্টি</th>
            <th>বার্তা</th>
            <th>উত্তর</th>
            <th>স্ট্যাটাস</th>
            <th>তারিখ</th>
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
                } ?>
              </span>
            </td>
            <td><?= date('d M Y', strtotime($inq['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- ===== BOOKINGS ===== -->
    <?php elseif ($activeTab === 'bookings'): ?>
    <div class="dash-header">
      <h2><i class="bi bi-calendar-check me-2 text-accent"></i>আমার Bookings</h2>
    </div>
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>প্রপার্টি</th>
            <th>তারিখ</th>
            <th>সময়</th>
            <th>স্ট্যাটাস</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bookings as $b): ?>
          <tr>
            <td><?= htmlspecialchars($b['prop_title']) ?></td>
            <td><?= date('d M Y', strtotime($b['tour_date'])) ?></td>
            <td><?= $b['tour_time'] ?></td>
            <td>
              <span class="status-badge status-<?= $b['status'] ?>">
                <?= match($b['status']) {
                  'pending'   => 'অপেক্ষায়',
                  'confirmed' => 'নিশ্চিত',
                  'cancelled' => 'বাতিল',
                  'completed' => 'সম্পন্ন',
                } ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

  </div>
</div>