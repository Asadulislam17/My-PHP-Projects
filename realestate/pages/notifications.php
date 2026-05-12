<?php
/**
 * ══════════════════════════════════════════════
 * NOTIFICATIONS PAGE
 * pages/notifications.php
 * ══════════════════════════════════════════════
 */
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../config/Database.php';

$auth   = Auth::getInstance();
$auth->requireLogin();

$db     = Database::getInstance();
$userId = (int)$_SESSION['user_id'];
$role   = $_SESSION['user_role'];

/* ────────────────────────────────────────────────
   Ensure notifications table exists
──────────────────────────────────────────────── */
$db->execute("CREATE TABLE IF NOT EXISTS notifications (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    type        VARCHAR(50)  NOT NULL,
    title       VARCHAR(200) NOT NULL,
    body        TEXT,
    icon        VARCHAR(100) DEFAULT 'bell',
    link        VARCHAR(300) DEFAULT NULL,
    is_read     TINYINT(1)   DEFAULT 0,
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read),
    INDEX idx_date (created_at)
)");

/* ────────────────────────────────────────────────
   Handle Actions
──────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'mark_read' && !empty($_POST['id'])) {
        $db->execute(
            "UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?",
            [(int)$_POST['id'], $userId]
        );
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'mark_all_read') {
        $db->execute(
            "UPDATE notifications SET is_read=1 WHERE user_id=?",
            [$userId]
        );
        header('Location: ?page=notifications&done=1');
        exit;
    }

    if ($action === 'delete' && !empty($_POST['id'])) {
        $db->execute(
            "DELETE FROM notifications WHERE id=? AND user_id=?",
            [(int)$_POST['id'], $userId]
        );
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'delete_all') {
        $db->execute("DELETE FROM notifications WHERE user_id=?", [$userId]);
        header('Location: ?page=notifications&cleared=1');
        exit;
    }

    if ($action === 'delete_read') {
        $db->execute(
            "DELETE FROM notifications WHERE user_id=? AND is_read=1",
            [$userId]
        );
        header('Location: ?page=notifications&cleared=1');
        exit;
    }
}

/* ────────────────────────────────────────────────
   Seed demo notifications if table is empty
──────────────────────────────────────────────── */
$count = $db->queryOne("SELECT COUNT(*) c FROM notifications WHERE user_id=?", [$userId])['c'];

if ($count === 0) {
    $demos = [
        ['inquiry',    '🏠 নতুন Inquiry',              'আপনার "গুলশান ৩ BHK অ্যাপার্টমেন্ট" property তে একটি নতুন inquiry এসেছে।', 'chat-dots-fill',  '?page=my-inquiries', 0, '-2 minutes'],
        ['booking',    '📅 Tour Confirmed',             '"ধানমন্ডি ভিলা" এর tour ১৫ জুন সকাল ১০টায় confirm হয়েছে।',              'calendar-check-fill','?page=buyer-dashboard&tab=bookings', 0, '-1 hour'],
        ['wishlist',   '❤️ Price Drop Alert',           'আপনার wishlist এ থাকা "বনানী অফিস স্পেস" এর দাম ৫০,০০০ টাকা কমেছে!',    'heart-fill',      '?page=wishlist',     0, '-3 hours'],
        ['system',     '✅ Property Approved',          'আপনার "মিরপুর কমার্শিয়াল শপ" property admin অনুমোদন করেছেন।',            'house-check-fill','?page=agent-dashboard&tab=properties', 1, '-5 hours'],
        ['payment',    '💳 Payment Successful',         'Pro Plan subscription সফলভাবে সম্পন্ন হয়েছে। ৩০ দিন active।',            'credit-card-2-front-fill','?page=agent-dashboard&tab=subscription', 1, '-1 day'],
        ['review',     '⭐ নতুন Review',                'আপনার property তে ৫ star review পেয়েছেন! "খুবই সুন্দর property..."',      'star-fill',       '?page=agent-dashboard', 1, '-2 days'],
        ['inquiry',    '💬 Inquiry Reply',              'আপনার inquiry তে agent reply করেছেন। "হ্যাঁ, property এখনো available..."', 'chat-fill',       '?page=my-inquiries', 1, '-3 days'],
        ['system',     '🔔 Welcome!',                   'RealEstate BD তে স্বাগতম! আপনার profile সম্পূর্ণ করুন।',                  'bell-fill',       '?page=profile',      1, '-7 days'],
    ];

    foreach ($demos as $d) {
        $db->execute(
            "INSERT INTO notifications (user_id,type,title,body,icon,link,is_read,created_at)
             VALUES (?,?,?,?,?,?,?,DATE_ADD(NOW(), INTERVAL ? MINUTE))",
            [$userId, $d[0], $d[1], $d[2], $d[3], $d[4], $d[5],
             match($d[6]) {
                '-2 minutes' => -2,
                '-1 hour'    => -60,
                '-3 hours'   => -180,
                '-5 hours'   => -300,
                '-1 day'     => -1440,
                '-2 days'    => -2880,
                '-3 days'    => -4320,
                '-7 days'    => -10080,
                default      => 0
             }
            ]
        );
    }
}

/* ────────────────────────────────────────────────
   Fetch notifications with filter
──────────────────────────────────────────────── */
$filter = $_GET['filter'] ?? 'all';
$type   = $_GET['type']   ?? '';
$page   = max(1, (int)($_GET['p'] ?? 1));
$perPage= 15;
$offset = ($page - 1) * $perPage;

$where  = "user_id = $userId";
if ($filter === 'unread') $where .= " AND is_read = 0";
if ($filter === 'read')   $where .= " AND is_read = 1";
if ($type)                $where .= " AND type = '" . addslashes($type) . "'";

$total   = $db->queryOne("SELECT COUNT(*) c FROM notifications WHERE $where")['c'];
$notifs  = $db->query(
    "SELECT * FROM notifications WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset"
);
$unreadCount = $db->queryOne(
    "SELECT COUNT(*) c FROM notifications WHERE user_id=? AND is_read=0",
    [$userId]
)['c'];

$lastPage = (int)ceil($total / $perPage);

/* ── Type stats ── */
$typeStats = $db->query(
    "SELECT type, COUNT(*) cnt, SUM(is_read=0) unread
     FROM notifications WHERE user_id=? GROUP BY type",
    [$userId]
);

/* ── Time formatter ── */
function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'এইমাত্র';
    if ($diff < 3600)   return (int)($diff/60)   . ' মিনিট আগে';
    if ($diff < 86400)  return (int)($diff/3600)  . ' ঘণ্টা আগে';
    if ($diff < 604800) return (int)($diff/86400) . ' দিন আগে';
    return date('d M Y', strtotime($datetime));
}

/* ── Type config ── */
$typeConfig = [
    'inquiry' => ['color' => 'blue',   'label' => 'Inquiry',    'icon' => 'chat-dots-fill'],
    'booking' => ['color' => 'green',  'label' => 'Booking',    'icon' => 'calendar-check-fill'],
    'payment' => ['color' => 'gold',   'label' => 'Payment',    'icon' => 'credit-card-2-front-fill'],
    'system'  => ['color' => 'purple', 'label' => 'System',     'icon' => 'gear-fill'],
    'wishlist'=> ['color' => 'red',    'label' => 'Wishlist',   'icon' => 'heart-fill'],
    'review'  => ['color' => 'amber',  'label' => 'Review',     'icon' => 'star-fill'],
    'property'=> ['color' => 'teal',   'label' => 'Property',   'icon' => 'house-fill'],
];
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<?php /* header.php include করলে নিচের block দরকার নেই */ ?>
</head>

<div class="notif-page">

  <!-- ══ 3D HEADER ══ -->
  <div class="notif-header">
    <div class="nh-bg">
      <div class="nh-orb nh-orb-1"></div>
      <div class="nh-orb nh-orb-2"></div>
      <div class="nh-grid"></div>
      <div class="nh-particles" id="nhParticles"></div>
    </div>
    <div class="container">
      <div class="nh-content">
        <div class="nh-left">
          <div class="nh-icon-wrap">
            <div class="nh-icon-ring">
              <i class="bi bi-bell-fill"></i>
              <?php if ($unreadCount > 0): ?>
              <div class="nh-badge"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></div>
              <?php endif; ?>
            </div>
          </div>
          <div>
            <h1 class="nh-title">Notifications</h1>
            <p class="nh-sub">
              <?php if ($unreadCount > 0): ?>
              <span class="nh-unread-pill"><?= $unreadCount ?> নতুন</span>
              <?php endif; ?>
              মোট <?= number_format($total) ?> টি notification
            </p>
          </div>
        </div>

        <!-- Stats Row -->
        <div class="nh-stats">
          <?php foreach ($typeStats as $ts):
            $cfg = $typeConfig[$ts['type']] ?? ['color'=>'purple','label'=>ucfirst($ts['type'])];
          ?>
          <div class="nh-stat color-<?= $cfg['color'] ?>">
            <strong><?= $ts['cnt'] ?></strong>
            <span><?= $cfg['label'] ?></span>
            <?php if ($ts['unread'] > 0): ?>
            <div class="nh-stat-dot"></div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Actions -->
        <div class="nh-actions">
          <?php if ($unreadCount > 0): ?>
          <form method="POST" style="display:inline">
            <input type="hidden" name="action" value="mark_all_read">
            <button type="submit" class="nh-action-btn">
              <i class="bi bi-check2-all me-1"></i>সব পড়া হয়েছে
            </button>
          </form>
          <?php endif; ?>
          <div class="dropdown">
            <button class="nh-action-btn outline" data-bs-toggle="dropdown">
              <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end notif-dropdown">
              <li>
                <form method="POST">
                  <input type="hidden" name="action" value="delete_read">
                  <button type="submit" class="dropdown-item">
                    <i class="bi bi-trash me-2"></i>পড়া notifications মুছুন
                  </button>
                </form>
              </li>
              <li>
                <form method="POST" onsubmit="return confirm('সব notification মুছে ফেলবেন?')">
                  <input type="hidden" name="action" value="delete_all">
                  <button type="submit" class="dropdown-item text-danger">
                    <i class="bi bi-trash3 me-2"></i>সব মুছুন
                  </button>
                </form>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ══ CONTENT ══ -->
  <div class="container py-4">

    <?php if (isset($_GET['done'])): ?>
    <div class="notif-flash success mb-3">
      <i class="bi bi-check-circle-fill me-2"></i>সব notification পড়া হয়েছে চিহ্নিত করা হয়েছে।
    </div>
    <?php endif; ?>
    <?php if (isset($_GET['cleared'])): ?>
    <div class="notif-flash success mb-3">
      <i class="bi bi-trash-fill me-2"></i>Notifications মুছে ফেলা হয়েছে।
    </div>
    <?php endif; ?>

    <div class="row g-4">

      <!-- ── LEFT: Filters ── -->
      <div class="col-lg-3">
        <div class="notif-filter-card">
          <div class="nfc-header">
            <h6><i class="bi bi-funnel me-2 text-accent"></i>Filter</h6>
          </div>

          <!-- Status Filter -->
          <div class="nfc-section">
            <span class="nfc-label">স্ট্যাটাস</span>
            <div class="nfc-btns">
              <?php foreach (['all'=>'সব','unread'=>'অপঠিত','read'=>'পঠিত'] as $f=>$l): ?>
              <a href="?page=notifications&filter=<?= $f ?>"
                 class="nfc-btn <?= $filter===$f?'active':'' ?>">
                <?= $l ?>
                <?php if ($f==='unread' && $unreadCount): ?>
                <span class="nfc-count"><?= $unreadCount ?></span>
                <?php endif; ?>
              </a>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Type Filter -->
          <div class="nfc-section">
            <span class="nfc-label">ধরন</span>
            <div class="nfc-type-list">
              <a href="?page=notifications&filter=<?= $filter ?>"
                 class="nfc-type <?= !$type?'active':'' ?>">
                <i class="bi bi-grid-3x3-gap me-2"></i>সব ধরন
              </a>
              <?php foreach ($typeConfig as $t => $cfg): ?>
              <?php $ts_item = current(array_filter($typeStats, fn($s)=>$s['type']===$t)); ?>
              <?php if ($ts_item): ?>
              <a href="?page=notifications&filter=<?= $filter ?>&type=<?= $t ?>"
                 class="nfc-type <?= $type===$t?'active':'' ?>">
                <i class="bi bi-<?= $cfg['icon'] ?> me-2 text-<?= $cfg['color'] ?>-custom"></i>
                <?= $cfg['label'] ?>
                <span class="nfc-type-count"><?= $ts_item['cnt'] ?></span>
                <?php if ($ts_item['unread'] > 0): ?>
                <span class="nfc-dot"></span>
                <?php endif; ?>
              </a>
              <?php endif; ?>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Push Notification Toggle -->
          <div class="nfc-section">
            <span class="nfc-label">Push Notification</span>
            <div class="push-status" id="pushStatus">
              <div class="push-icon"><i class="bi bi-bell"></i></div>
              <div class="push-info">
                <strong id="pushStatusText">Status checking...</strong>
                <small id="pushStatusDesc">Browser notification</small>
              </div>
              <button class="push-toggle-btn" id="pushToggleBtn" onclick="togglePushNotification()">
                চালু করুন
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ── RIGHT: Notification List ── -->
      <div class="col-lg-9">

        <!-- Search Bar -->
        <div class="notif-search-bar mb-3">
          <div class="nsb-inner">
            <i class="bi bi-search"></i>
            <input type="text" id="notifSearch"
                   placeholder="Notification খুঁজুন..."
                   oninput="searchNotifications(this.value)">
          </div>
          <span class="nsb-count"><?= $total ?> টি</span>
        </div>

        <?php if (empty($notifs)): ?>
        <!-- Empty State -->
        <div class="notif-empty">
          <div class="ne-animation">
            <div class="ne-bell"><i class="bi bi-bell"></i></div>
            <div class="ne-ring ne-ring-1"></div>
            <div class="ne-ring ne-ring-2"></div>
            <div class="ne-ring ne-ring-3"></div>
          </div>
          <h3>কোনো notification নেই</h3>
          <p>
            <?= $filter==='unread' ? 'সব notification পড়া হয়েছে! 🎉' : 'এখনো কোনো notification আসেনি।' ?>
          </p>
          <?php if ($filter !== 'all'): ?>
          <a href="?page=notifications" class="btn-accent-sm">সব দেখুন</a>
          <?php endif; ?>
        </div>

        <?php else: ?>

        <!-- Notification Groups by Date -->
        <?php
        $grouped = [];
        foreach ($notifs as $n) {
            $date = date('Y-m-d', strtotime($n['created_at']));
            $grouped[$date][] = $n;
        }
        $today     = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        ?>

        <div id="notifList">
          <?php foreach ($grouped as $date => $items):
            $groupLabel = match($date) {
                $today     => 'আজকে',
                $yesterday => 'গতকাল',
                default    => date('d F Y', strtotime($date))
            };
          ?>

          <div class="notif-group" data-date="<?= $date ?>">
            <div class="ng-label">
              <span><?= $groupLabel ?></span>
              <div class="ng-line"></div>
            </div>

            <?php foreach ($items as $n):
              $cfg = $typeConfig[$n['type']] ?? ['color'=>'purple','icon'=>'bell-fill','label'=>'System'];
            ?>
            <div class="notif-item <?= $n['is_read'] ? 'read' : 'unread' ?>"
                 id="notif-<?= $n['id'] ?>"
                 data-text="<?= strtolower(htmlspecialchars($n['title'] . ' ' . $n['body'])) ?>">

              <!-- Unread Dot -->
              <?php if (!$n['is_read']): ?>
              <div class="ni-unread-dot"></div>
              <?php endif; ?>

              <!-- Icon -->
              <div class="ni-icon color-<?= $cfg['color'] ?>">
                <i class="bi bi-<?= htmlspecialchars($n['icon'] ?? $cfg['icon']) ?>"></i>
              </div>

              <!-- Content -->
              <div class="ni-content">
                <div class="ni-title-row">
                  <h6 class="ni-title"><?= htmlspecialchars($n['title']) ?></h6>
                  <span class="ni-time">
                    <i class="bi bi-clock me-1"></i><?= timeAgo($n['created_at']) ?>
                  </span>
                </div>
                <p class="ni-body"><?= htmlspecialchars($n['body']) ?></p>
                <div class="ni-footer">
                  <span class="ni-type-badge color-<?= $cfg['color'] ?>">
                    <?= $cfg['label'] ?>
                  </span>
                  <?php if ($n['link']): ?>
                  <a href="<?= htmlspecialchars($n['link']) ?>"
                     class="ni-action-link"
                     onclick="markRead(<?= $n['id'] ?>)">
                    বিস্তারিত দেখুন <i class="bi bi-arrow-right ms-1"></i>
                  </a>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Actions -->
              <div class="ni-actions">
                <?php if (!$n['is_read']): ?>
                <button class="ni-btn" title="পড়া হয়েছে"
                        onclick="markRead(<?= $n['id'] ?>)">
                  <i class="bi bi-check2-circle"></i>
                </button>
                <?php endif; ?>
                <button class="ni-btn red" title="মুছুন"
                        onclick="deleteNotif(<?= $n['id'] ?>)">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($lastPage > 1): ?>
        <nav class="pagination-wrap mt-4">
          <ul class="pagination-custom">
            <?php if ($page > 1): ?>
            <li>
              <a href="?page=notifications&filter=<?=$filter?>&type=<?=$type?>&p=<?=$page-1?>"
                 class="page-btn"><i class="bi bi-chevron-left"></i></a>
            </li>
            <?php endif; ?>

            <?php for ($i = max(1,$page-2); $i <= min($lastPage,$page+2); $i++): ?>
            <li>
              <a href="?page=notifications&filter=<?=$filter?>&type=<?=$type?>&p=<?=$i?>"
                 class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>

            <?php if ($page < $lastPage): ?>
            <li>
              <a href="?page=notifications&filter=<?=$filter?>&type=<?=$type?>&p=<?=$page+1?>"
                 class="page-btn"><i class="bi bi-chevron-right"></i></a>
            </li>
            <?php endif; ?>
          </ul>
          <p class="pagination-info">
            <?= (($page-1)*$perPage)+1 ?>–<?= min($total,$page*$perPage) ?> / <?= $total ?> টি
          </p>
        </nav>
        <?php endif; ?>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>


<!-- ══════════════════════════════════════════
     STYLES
══════════════════════════════════════════ -->
<style>
/* ── Page Base ── */
.notif-page { background: var(--bg); min-height: 100vh; }

/* ── 3D Header ── */
.notif-header {
  position: relative; padding: 50px 0 36px;
  background: linear-gradient(135deg, #0F172A 0%, #111827 60%, #0F172A 100%);
  overflow: hidden;
}
.nh-bg { position: absolute; inset: 0; }
.nh-orb {
  position: absolute; border-radius: 50%;
  filter: blur(90px); opacity: 0.12;
}
.nh-orb-1 {
  width: 500px; height: 500px; background: #C5A059;
  top: -200px; left: -150px;
  animation: nhOrb1 12s ease-in-out infinite;
}
.nh-orb-2 {
  width: 400px; height: 400px; background: #3B82F6;
  bottom: -150px; right: -100px;
  animation: nhOrb2 15s ease-in-out infinite;
}
@keyframes nhOrb1 { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(40px,-30px) scale(1.08)} }
@keyframes nhOrb2 { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(-30px,20px) scale(1.06)} }

.nh-grid {
  position: absolute; inset: 0;
  background-image:
    linear-gradient(rgba(255,255,255,0.018) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.018) 1px, transparent 1px);
  background-size: 48px 48px;
}

.nh-content {
  position: relative; z-index: 2;
  display: flex; align-items: center; gap: 24px; flex-wrap: wrap;
}
.nh-left {
  display: flex; align-items: center; gap: 20px; flex: 1; min-width: 200px;
}

/* Bell Icon */
.nh-icon-ring {
  position: relative; width: 64px; height: 64px; border-radius: 50%;
  border: 2px solid rgba(197,160,89,0.4);
  background: rgba(197,160,89,0.1);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.6rem; color: #C5A059;
  animation: nhBellPulse 3s ease-in-out infinite;
}
.nh-icon-wrap { flex-shrink: 0; }
@keyframes nhBellPulse {
  0%,100% { box-shadow: 0 0 0 0 rgba(197,160,89,0.3); }
  50%      { box-shadow: 0 0 0 14px rgba(197,160,89,0); }
}
.nh-badge {
  position: absolute; top: -4px; right: -4px;
  background: #ef4444; color: #fff;
  border: 2px solid #0F172A; border-radius: 50px;
  min-width: 22px; height: 22px; font-size: 0.65rem; font-weight: 800;
  display: flex; align-items: center; justify-content: center; padding: 0 4px;
}

.nh-title  { color: #fff; font-size: 1.6rem; font-weight: 800; margin: 0 0 6px; }
.nh-sub    { color: rgba(255,255,255,0.5); font-size: 0.85rem; display: flex; align-items: center; gap: 8px; }
.nh-unread-pill {
  background: rgba(239,68,68,0.2); color: #fca5a5;
  border: 1px solid rgba(239,68,68,0.3); padding: 2px 10px;
  border-radius: 50px; font-size: 0.72rem; font-weight: 700;
}

/* Stats */
.nh-stats {
  display: flex; gap: 10px; flex-wrap: wrap;
}
.nh-stat {
  padding: 10px 16px; border-radius: 10px; text-align: center;
  border: 1px solid rgba(255,255,255,0.08);
  background: rgba(255,255,255,0.04);
  position: relative; cursor: default;
  transition: all 0.2s;
}
.nh-stat:hover { background: rgba(255,255,255,0.08); }
.nh-stat strong { display: block; font-family: 'Montserrat',sans-serif; font-size: 1.2rem; font-weight: 800; color: #fff; }
.nh-stat span   { font-size: 0.7rem; color: rgba(255,255,255,0.45); }
.nh-stat-dot {
  position: absolute; top: 6px; right: 6px;
  width: 7px; height: 7px; border-radius: 50%; background: #ef4444;
}

/* Color variants for stats */
.nh-stat.color-blue   strong { color: #60A5FA; }
.nh-stat.color-green  strong { color: #4ADE80; }
.nh-stat.color-gold   strong { color: #C5A059; }
.nh-stat.color-purple strong { color: #A78BFA; }
.nh-stat.color-red    strong { color: #F87171; }
.nh-stat.color-amber  strong { color: #FBBF24; }
.nh-stat.color-teal   strong { color: #2DD4BF; }

/* Actions */
.nh-actions { display: flex; gap: 8px; flex-shrink: 0; }
.nh-action-btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 16px; border-radius: 8px; font-size: 0.82rem; font-weight: 600;
  border: none; cursor: pointer; transition: all 0.2s;
  background: rgba(197,160,89,0.15); color: #C5A059;
  border: 1px solid rgba(197,160,89,0.3);
}
.nh-action-btn:hover { background: rgba(197,160,89,0.25); }
.nh-action-btn.outline {
  background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.65);
  border-color: rgba(255,255,255,0.12);
}
.nh-action-btn.outline:hover { background: rgba(255,255,255,0.1); }

/* Dropdown */
.notif-dropdown {
  background: #1E293B; border: 1px solid rgba(255,255,255,0.08);
  border-radius: 10px; padding: 6px;
}
.notif-dropdown .dropdown-item {
  color: rgba(255,255,255,0.7); border-radius: 6px;
  padding: 8px 12px; font-size: 0.85rem;
}
.notif-dropdown .dropdown-item:hover { background: rgba(255,255,255,0.06); }
.notif-dropdown .dropdown-item.text-danger { color: #f87171; }
.notif-dropdown .dropdown-item.text-danger:hover { background: rgba(239,68,68,0.1); }

/* ── Flash ── */
.notif-flash {
  padding: 12px 16px; border-radius: 10px; font-size: 0.875rem;
  display: flex; align-items: center;
}
.notif-flash.success {
  background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2); color: #22C55E;
}

/* ── Filter Card ── */
.notif-filter-card {
  background: #fff; border: 1px solid var(--border);
  border-radius: 14px; padding: 20px;
  position: sticky; top: 80px;
}
.nfc-header { margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px solid var(--border); }
.nfc-header h6 { font-size: 0.95rem; font-weight: 700; margin: 0; }

.nfc-section { margin-bottom: 20px; }
.nfc-label {
  display: block; font-size: 0.7rem; font-weight: 700;
  color: var(--secondary); text-transform: uppercase;
  letter-spacing: 0.8px; margin-bottom: 10px;
}

.nfc-btns { display: flex; flex-direction: column; gap: 4px; }
.nfc-btn {
  display: flex; align-items: center; justify-content: space-between;
  padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border);
  text-decoration: none; font-size: 0.85rem; font-weight: 600;
  color: var(--secondary); transition: all 0.2s;
}
.nfc-btn:hover  { border-color: var(--accent); color: var(--accent); }
.nfc-btn.active { background: rgba(197,160,89,0.1); border-color: var(--accent); color: var(--accent); }
.nfc-count {
  background: #ef4444; color: #fff; border-radius: 50px;
  font-size: 0.68rem; padding: 1px 6px; font-weight: 700;
}

.nfc-type-list { display: flex; flex-direction: column; gap: 2px; }
.nfc-type {
  display: flex; align-items: center; padding: 7px 10px; border-radius: 8px;
  text-decoration: none; font-size: 0.82rem; color: var(--secondary); transition: all 0.2s;
  position: relative;
}
.nfc-type:hover  { background: var(--bg); color: var(--primary); }
.nfc-type.active { background: rgba(197,160,89,0.08); color: var(--primary); font-weight: 600; }
.nfc-type-count  { margin-left: auto; font-size: 0.72rem; color: var(--secondary); }
.nfc-dot {
  width: 7px; height: 7px; border-radius: 50%; background: #ef4444;
  margin-left: 6px; flex-shrink: 0;
}

/* Color classes for icons */
.text-blue-custom   { color: #3B82F6; }
.text-green-custom  { color: #22C55E; }
.text-gold-custom   { color: #C5A059; }
.text-purple-custom { color: #8B5CF6; }
.text-red-custom    { color: #EF4444; }
.text-amber-custom  { color: #F59E0B; }
.text-teal-custom   { color: #14B8A6; }

/* Push Status */
.push-status {
  display: flex; align-items: center; gap: 10px;
  padding: 12px; background: var(--bg); border: 1px solid var(--border);
  border-radius: 10px;
}
.push-icon { font-size: 1.1rem; color: var(--accent); }
.push-info { flex: 1; }
.push-info strong { display: block; font-size: 0.8rem; }
.push-info small  { color: var(--secondary); font-size: 0.72rem; }
.push-toggle-btn {
  background: var(--accent); color: var(--primary); border: none;
  padding: 5px 10px; border-radius: 6px; font-size: 0.72rem; font-weight: 700;
  cursor: pointer; white-space: nowrap; transition: all 0.2s;
}
.push-toggle-btn:hover { background: var(--accent-dark); }
.push-toggle-btn.enabled { background: rgba(34,197,94,0.15); color: #22C55E; border: 1px solid rgba(34,197,94,0.3); }

/* ── Search Bar ── */
.notif-search-bar {
  display: flex; align-items: center; gap: 12px;
  background: #fff; border: 1px solid var(--border);
  border-radius: 10px; padding: 10px 16px;
}
.nsb-inner {
  display: flex; align-items: center; gap: 8px; flex: 1;
}
.nsb-inner i { color: var(--secondary); }
.nsb-inner input {
  flex: 1; border: none; outline: none; font-size: 0.875rem;
  font-family: 'Inter', sans-serif; color: var(--primary); background: transparent;
}
.nsb-inner input::placeholder { color: #94A3B8; }
.nsb-count { font-size: 0.78rem; color: var(--secondary); flex-shrink: 0; }

/* ── Group ── */
.notif-group { margin-bottom: 24px; }
.ng-label {
  display: flex; align-items: center; gap: 12px; margin-bottom: 12px;
}
.ng-label span {
  font-size: 0.72rem; font-weight: 700; color: var(--secondary);
  text-transform: uppercase; letter-spacing: 1px; white-space: nowrap;
}
.ng-line { flex: 1; height: 1px; background: var(--border); }

/* ── Notification Item ── */
.notif-item {
  display: flex; align-items: flex-start; gap: 14px;
  padding: 16px 18px; border-radius: 12px;
  border: 1px solid var(--border); background: #fff;
  margin-bottom: 8px; position: relative;
  transition: all 0.25s; cursor: default;
}
.notif-item:hover {
  box-shadow: var(--shadow-md); transform: translateY(-1px);
  border-color: rgba(197,160,89,0.25);
}
.notif-item.unread {
  background: linear-gradient(135deg, #FFFDF7 0%, #fff 100%);
  border-color: rgba(197,160,89,0.2);
}
.notif-item.read { opacity: 0.78; }
.notif-item.removing {
  opacity: 0; transform: translateX(20px) scale(0.98);
  transition: all 0.35s ease;
}

/* Unread dot */
.ni-unread-dot {
  position: absolute; top: 18px; left: -4px;
  width: 8px; height: 8px; border-radius: 50%;
  background: var(--accent); border: 2px solid #fff;
  box-shadow: 0 0 0 2px rgba(197,160,89,0.3);
}

/* Icon */
.ni-icon {
  width: 44px; height: 44px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; flex-shrink: 0; transition: transform 0.2s;
}
.notif-item:hover .ni-icon { transform: scale(1.08); }

/* Color variants for icons */
.ni-icon.color-blue   { background: rgba(59,130,246,0.12);  color: #3B82F6; }
.ni-icon.color-green  { background: rgba(34,197,94,0.12);   color: #22C55E; }
.ni-icon.color-gold   { background: rgba(197,160,89,0.15);  color: #C5A059; }
.ni-icon.color-purple { background: rgba(139,92,246,0.12);  color: #8B5CF6; }
.ni-icon.color-red    { background: rgba(239,68,68,0.12);   color: #EF4444; }
.ni-icon.color-amber  { background: rgba(245,158,11,0.12);  color: #F59E0B; }
.ni-icon.color-teal   { background: rgba(20,184,166,0.12);  color: #14B8A6; }

/* Content */
.ni-content { flex: 1; min-width: 0; }
.ni-title-row {
  display: flex; justify-content: space-between;
  align-items: flex-start; gap: 12px; margin-bottom: 4px;
}
.ni-title {
  font-size: 0.9rem; font-weight: 700; color: var(--primary);
  margin: 0; line-height: 1.4;
}
.ni-time {
  font-size: 0.72rem; color: var(--secondary); white-space: nowrap;
  flex-shrink: 0; padding-top: 2px;
}
.ni-body {
  font-size: 0.83rem; color: var(--secondary); line-height: 1.6;
  margin: 0 0 8px; overflow: hidden;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
}
.ni-footer { display: flex; align-items: center; gap: 10px; }

/* Type Badge */
.ni-type-badge {
  display: inline-block; padding: 2px 9px; border-radius: 50px;
  font-size: 0.68rem; font-weight: 700;
}
.ni-type-badge.color-blue   { background: rgba(59,130,246,0.12);  color: #2563EB; }
.ni-type-badge.color-green  { background: rgba(34,197,94,0.12);   color: #16A34A; }
.ni-type-badge.color-gold   { background: rgba(197,160,89,0.15);  color: #B8912E; }
.ni-type-badge.color-purple { background: rgba(139,92,246,0.12);  color: #7C3AED; }
.ni-type-badge.color-red    { background: rgba(239,68,68,0.12);   color: #DC2626; }
.ni-type-badge.color-amber  { background: rgba(245,158,11,0.12);  color: #D97706; }
.ni-type-badge.color-teal   { background: rgba(20,184,166,0.12);  color: #0F766E; }

.ni-action-link {
  font-size: 0.8rem; font-weight: 600; color: var(--accent);
  text-decoration: none; display: flex; align-items: center; gap: 4px;
  transition: color 0.2s;
}
.ni-action-link:hover { color: var(--accent-dark); }

/* Item Actions */
.ni-actions {
  display: flex; flex-direction: column; gap: 6px;
  flex-shrink: 0; opacity: 0;
  transition: opacity 0.2s;
}
.notif-item:hover .ni-actions { opacity: 1; }
.ni-btn {
  width: 30px; height: 30px; border-radius: 8px;
  background: var(--bg); border: 1px solid var(--border);
  color: var(--secondary); display: flex; align-items: center;
  justify-content: center; font-size: 0.8rem; cursor: pointer;
  transition: all 0.2s;
}
.ni-btn:hover     { background: rgba(34,197,94,0.1);  color: #22C55E; border-color: rgba(34,197,94,0.3); }
.ni-btn.red:hover { background: rgba(239,68,68,0.1);  color: #EF4444; border-color: rgba(239,68,68,0.3); }

/* ── Empty State ── */
.notif-empty {
  text-align: center; padding: 80px 20px;
  background: #fff; border-radius: 16px; border: 1px solid var(--border);
}
.ne-animation {
  position: relative; width: 80px; height: 80px;
  margin: 0 auto 24px; display: flex; align-items: center; justify-content: center;
}
.ne-bell {
  font-size: 2rem; color: var(--secondary);
  animation: neBell 2s ease-in-out infinite;
  position: relative; z-index: 2;
}
@keyframes neBell {
  0%,100% { transform: rotate(0deg); }
  15%,45% { transform: rotate(12deg); }
  30%,60% { transform: rotate(-12deg); }
  75%      { transform: rotate(0deg); }
}
.ne-ring {
  position: absolute; border-radius: 50%;
  border: 2px solid var(--accent);
  animation: neRing 2s ease-out infinite;
}
.ne-ring-1 { width: 40px; height: 40px; opacity: 0.6; }
.ne-ring-2 { width: 60px; height: 60px; opacity: 0.35; animation-delay: 0.3s; }
.ne-ring-3 { width: 80px; height: 80px; opacity: 0.15; animation-delay: 0.6s; }
@keyframes neRing {
  0%   { transform: scale(0.8); opacity: 0.6; }
  100% { transform: scale(1.4); opacity: 0; }
}
.notif-empty h3 { color: var(--primary); margin-bottom: 8px; font-size: 1.2rem; }
.notif-empty p  { color: var(--secondary); margin-bottom: 20px; }

/* ── Responsive ── */
@media (max-width: 992px) {
  .nh-content  { flex-direction: column; align-items: flex-start; }
  .nh-stats    { width: 100%; }
  .ni-actions  { opacity: 1; }
}
@media (max-width: 576px) {
  .notif-item  { flex-wrap: wrap; }
  .ni-actions  { flex-direction: row; width: 100%; justify-content: flex-end; }
  .nh-stats    { display: none; }
}
</style>


<!-- ══════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════ -->
<script>
/* ── Mark as Read (AJAX) ── */
function markRead(id) {
  const item = document.getElementById('notif-' + id);
  if (!item) return;

  fetch('?page=notifications', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=mark_read&id=${id}`,
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      item.classList.remove('unread');
      item.classList.add('read');
      item.querySelector('.ni-unread-dot')?.remove();

      /* Update badge count */
      const badge = document.querySelector('.nh-badge');
      if (badge) {
        const current = parseInt(badge.textContent) || 0;
        if (current <= 1) badge.remove();
        else badge.textContent = current - 1;
      }
      const pill = document.querySelector('.nh-unread-pill');
      if (pill) {
        const n = parseInt(pill.textContent) || 0;
        if (n <= 1) pill.remove();
        else pill.textContent = (n - 1) + ' নতুন';
      }
    }
  })
  .catch(() => {});
}

/* ── Delete Notification (AJAX) ── */
function deleteNotif(id) {
  const item = document.getElementById('notif-' + id);
  if (!item) return;

  if (!confirm('এই notification মুছে ফেলবেন?')) return;

  fetch('?page=notifications', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=delete&id=${id}`,
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      item.classList.add('removing');
      setTimeout(() => {
        item.remove();
        /* Remove empty group */
        document.querySelectorAll('.notif-group').forEach(g => {
          if (!g.querySelector('.notif-item')) g.remove();
        });
      }, 380);
    }
  })
  .catch(() => {});
}

/* ── Search ── */
function searchNotifications(q) {
  q = q.toLowerCase().trim();
  document.querySelectorAll('.notif-item').forEach(item => {
    const text = item.dataset.text || '';
    item.style.display = (!q || text.includes(q)) ? '' : 'none';
  });
  /* Hide empty groups */
  document.querySelectorAll('.notif-group').forEach(g => {
    const visible = [...g.querySelectorAll('.notif-item')].some(i => i.style.display !== 'none');
    g.style.display = visible ? '' : 'none';
  });
}

/* ── Push Notification Status ── */
async function checkPushStatus() {
  const btn  = document.getElementById('pushToggleBtn');
  const text = document.getElementById('pushStatusText');
  const desc = document.getElementById('pushStatusDesc');

  if (!('Notification' in window)) {
    if (text) text.textContent = 'সাপোর্ট নেই';
    if (desc) desc.textContent = 'এই browser এ push notification নেই';
    if (btn)  btn.style.display = 'none';
    return;
  }

  const permission = Notification.permission;
  if (permission === 'granted') {
    if (text) text.textContent = 'চালু আছে ✓';
    if (desc) desc.textContent = 'Push notification active';
    if (btn)  { btn.textContent = 'বন্ধ করুন'; btn.classList.add('enabled'); }
  } else if (permission === 'denied') {
    if (text) text.textContent = 'ব্লক করা হয়েছে';
    if (desc) desc.textContent = 'Browser settings থেকে চালু করুন';
    if (btn)  btn.style.display = 'none';
  } else {
    if (text) text.textContent = 'বন্ধ আছে';
    if (desc) desc.textContent = 'চালু করতে বাটন ক্লিক করুন';
    if (btn)  btn.textContent = 'চালু করুন';
  }
}

async function togglePushNotification() {
  if (!('Notification' in window)) return;

  if (Notification.permission === 'granted') {
    /* Cannot programmatically deny — show instructions */
    showToast('Browser settings > Site permissions থেকে বন্ধ করুন', 'info');
    return;
  }

  const result = await Notification.requestPermission();
  if (result === 'granted') {
    showToast('Push notification চালু হয়েছে! 🔔', 'success');
    checkPushStatus();

    /* Test notification */
    setTimeout(() => {
      new Notification('RealEstate BD', {
        body: 'Push notification সফলভাবে চালু হয়েছে!',
        icon: '/assets/icons/icon-192.png',
        badge: '/assets/icons/badge-72.png',
        tag:  'test',
      });
    }, 800);
  } else {
    showToast('Permission দেওয়া হয়নি।', 'error');
  }
}

/* ── Header Particle Effect ── */
(function() {
  const container = document.getElementById('nhParticles');
  if (!container) return;
  container.style.cssText = 'position:absolute;inset:0;pointer-events:none;overflow:hidden';
  for (let i = 0; i < 25; i++) {
    const p   = document.createElement('div');
    const size= Math.random() * 3 + 1.5;
    p.style.cssText = `
      position:absolute; border-radius:50%;
      width:${size}px; height:${size}px;
      background:rgba(197,160,89,${Math.random()*0.25+0.05});
      left:${Math.random()*100}%;
      top:${Math.random()*100}%;
      animation:nhParticle ${Math.random()*12+8}s ease-in-out infinite;
      animation-delay:${Math.random()*6}s;
    `;
    container.appendChild(p);
  }
  if (!document.getElementById('nhParticleStyle')) {
    const s   = document.createElement('style');
    s.id      = 'nhParticleStyle';
    s.textContent = `@keyframes nhParticle{0%,100%{transform:translateY(0) scale(1);opacity:.15}50%{transform:translateY(-${Math.floor(Math.random()*30+15)}px) scale(1.3);opacity:.4}}`;
    document.head.appendChild(s);
  }
})();

/* ── Auto-mark as read after 3s ── */
let readTimer = null;
const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting && entry.target.classList.contains('unread')) {
      const id = entry.target.id.replace('notif-','');
      if (id) {
        clearTimeout(readTimer);
        readTimer = setTimeout(() => markRead(parseInt(id)), 3000);
      }
    }
  });
}, { threshold: 0.8 });

document.querySelectorAll('.notif-item.unread').forEach(item => observer.observe(item));

/* ── Init ── */
checkPushStatus();
</script>