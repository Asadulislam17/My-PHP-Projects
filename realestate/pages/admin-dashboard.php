<?php
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../config/Database.php';

$auth = Auth::getInstance();
$auth->requireRole('admin');

$db        = Database::getInstance();
$activeTab = $_GET['tab'] ?? 'overview';

// ── Handle Actions ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    match($action) {
        'approve_property' => $db->execute(
            "UPDATE properties SET status='approved' WHERE id=?", [$id]),
        'reject_property'  => $db->execute(
            "UPDATE properties SET status='rejected' WHERE id=?", [$id]),
        'ban_user'         => $db->execute(
            "UPDATE users SET status='banned' WHERE id=?", [$id]),
        'activate_user'    => $db->execute(
            "UPDATE users SET status='active' WHERE id=?", [$id]),
        'toggle_featured'  => $db->execute(
            "UPDATE properties SET is_featured = NOT is_featured WHERE id=?", [$id]),
        'update_rate'      => $db->execute(
            "UPDATE material_rates SET rate=? WHERE id=?",
            [$_POST['rate'], $id]),
        default => null
    };
    header('Location: ?page=admin-dashboard&tab=' . $activeTab . '&saved=1');
    exit;
}

// ── Stats ────────────────────────────────────────
$stats = [
    'total_users'      => $db->queryOne("SELECT COUNT(*) c FROM users")['c'],
    'total_properties' => $db->queryOne("SELECT COUNT(*) c FROM properties")['c'],
    'pending_props'    => $db->queryOne("SELECT COUNT(*) c FROM properties WHERE status='pending'")['c'],
    'total_revenue'    => $db->queryOne("SELECT COALESCE(SUM(amount),0) c FROM transactions WHERE status='success'")['c'],
    'total_inquiries'  => $db->queryOne("SELECT COUNT(*) c FROM inquiries")['c'],
    'active_agents'    => $db->queryOne("SELECT COUNT(*) c FROM users WHERE role_id=2 AND status='active'")['c'],
];

// ── Chart Data (last 7 days) ─────────────────────
$viewsChart = $db->query(
    "SELECT DATE(viewed_at) AS d, COUNT(*) AS c
     FROM property_views
     WHERE viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
     GROUP BY DATE(viewed_at) ORDER BY d ASC"
);

$revenueChart = $db->query(
    "SELECT DATE(created_at) AS d, SUM(amount) AS c
     FROM transactions WHERE status='success'
     AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
     GROUP BY DATE(created_at) ORDER BY d ASC"
);

// ── Pending Properties ───────────────────────────
$pendingProps = $db->query(
    "SELECT p.*, u.name agent_name, pt.name type_name, a.name area_name,
            (SELECT image_path FROM property_images
             WHERE property_id=p.id AND is_cover=1 LIMIT 1) cover
     FROM properties p
     JOIN users u ON u.id=p.user_id
     JOIN property_types pt ON pt.id=p.type_id
     JOIN areas a ON a.id=p.area_id
     WHERE p.status='pending'
     ORDER BY p.created_at DESC"
);

// ── All Users ────────────────────────────────────
$users = $db->query(
    "SELECT u.*, r.name role_name,
            (SELECT COUNT(*) FROM properties WHERE user_id=u.id) prop_count
     FROM users u JOIN roles r ON r.id=u.role_id
     ORDER BY u.created_at DESC LIMIT 50"
);

// ── Material Rates ───────────────────────────────
$materialRates = $db->query("SELECT * FROM material_rates ORDER BY id");

// ── Recent Transactions ──────────────────────────
$transactions = $db->query(
    "SELECT t.*, u.name uname, u.email uemail
     FROM transactions t JOIN users u ON u.id=t.user_id
     ORDER BY t.created_at DESC LIMIT 20"
);

// ── Activity Logs ────────────────────────────────
$logs = $db->query(
    "SELECT l.*, u.name uname
     FROM activity_logs l
     LEFT JOIN users u ON u.id=l.user_id
     ORDER BY l.created_at DESC LIMIT 30"
);

$saved = isset($_GET['saved']);
?>

<!-- ══════════════════════════════════════════════
     ADMIN SHELL
══════════════════════════════════════════════ -->
<div class="admin-shell">

  <!-- ── Sidebar ── -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-brand">
      <div class="admin-brand-icon">
        <i class="bi bi-buildings-fill"></i>
      </div>
      <span>RE<span class="text-accent">Admin</span></span>
    </div>

    <nav class="admin-nav">
      <div class="admin-nav-group">
        <span class="nav-group-label">মূল</span>
        <?php
        $navItems = [
          'overview'    => ['bi-speedometer2', 'ড্যাশবোর্ড'],
          'properties'  => ['bi-house-check',  'Property অনুমোদন', $stats['pending_props']],
          'users'       => ['bi-people',       'ব্যবহারকারী'],
          'transactions'=> ['bi-credit-card',  'লেনদেন'],
        ];
        foreach ($navItems as $key => [$icon, $label, $badge]):
        ?>
        <a href="?page=admin-dashboard&tab=<?= $key ?>"
           class="admin-nav-item <?= $activeTab===$key?'active':'' ?>">
          <i class="bi <?= $icon ?>"></i>
          <span><?= $label ?></span>
          <?php if (!empty($badge) && $badge > 0): ?>
          <span class="nav-badge"><?= $badge ?></span>
          <?php endif; ?>
        </a>
        <?php endforeach; ?>
      </div>

      <div class="admin-nav-group">
        <span class="nav-group-label">সেটিংস</span>
        <?php
        $navItems2 = [
          'rates'    => ['bi-cash-stack', 'Material Rates'],
          'logs'     => ['bi-journal-text','Activity Logs'],
          'settings' => ['bi-gear',       'সাইট সেটিংস'],
        ];
        foreach ($navItems2 as $key => [$icon, $label]):
        ?>
        <a href="?page=admin-dashboard&tab=<?= $key ?>"
           class="admin-nav-item <?= $activeTab===$key?'active':'' ?>">
          <i class="bi <?= $icon ?>"></i>
          <span><?= $label ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </nav>

    <a href="?page=logout" class="admin-nav-item danger mt-auto">
      <i class="bi bi-box-arrow-right"></i>
      <span>Logout</span>
    </a>
  </aside>

  <!-- ── Main ── -->
  <div class="admin-main">

    <!-- Top Bar -->
    <header class="admin-topbar">
      <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
      </button>
      <div class="topbar-title">
        <?= match($activeTab) {
          'overview'    => 'ড্যাশবোর্ড ওভারভিউ',
          'properties'  => 'Property অনুমোদন',
          'users'       => 'ব্যবহারকারী পরিচালনা',
          'transactions'=> 'লেনদেন',
          'rates'       => 'Material Rates',
          'logs'        => 'Activity Logs',
          default       => 'Admin Panel'
        } ?>
      </div>
      <div class="topbar-right">
        <?php if ($saved): ?>
        <span class="saved-toast"><i class="bi bi-check-circle me-1"></i>সংরক্ষিত!</span>
        <?php endif; ?>
        <div class="admin-user-chip">
          <div class="admin-avatar">A</div>
          <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
        </div>
      </div>
    </header>

    <div class="admin-content">

      <!-- ════ OVERVIEW ════ -->
      <?php if ($activeTab === 'overview'): ?>

      <!-- KPI Cards -->
      <div class="kpi-grid">
        <?php
        $kpis = [
          ['bi-people-fill',    number_format($stats['total_users']),      'মোট ব্যবহারকারী', '#3B82F6', '+12% এই মাসে'],
          ['bi-houses-fill',    number_format($stats['total_properties']),  'মোট Property',     '#C5A059', $stats['pending_props'].' টি pending'],
          ['bi-cash-coin',      '৳'.number_format($stats['total_revenue']), 'মোট রাজস্ব',       '#22C55E', 'সব সময়'],
          ['bi-chat-dots-fill', number_format($stats['total_inquiries']),   'মোট Inquiry',      '#8B5CF6', 'সব সময়'],
          ['bi-person-badge',   number_format($stats['active_agents']),     'সক্রিয় এজেন্ট',   '#F59E0B', ''],
          ['bi-hourglass-split',$stats['pending_props'],                    'অনুমোদন বাকি',    '#EF4444', 'দ্রুত ব্যবস্থা নিন'],
        ];
        foreach ($kpis as [$icon, $val, $label, $color, $sub]):
        ?>
        <div class="kpi-card" style="--kc:<?= $color ?>">
          <div class="kpi-icon"><i class="bi <?= $icon ?>"></i></div>
          <div class="kpi-info">
            <div class="kpi-val"><?= $val ?></div>
            <div class="kpi-label"><?= $label ?></div>
            <?php if ($sub): ?>
            <div class="kpi-sub"><?= $sub ?></div>
            <?php endif; ?>
          </div>
          <div class="kpi-glow"></div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Charts Row -->
      <div class="charts-row">
        <div class="chart-card wide">
          <div class="chart-card-header">
            <h5>সাপ্তাহিক Property ভিউ</h5>
            <span class="chart-badge">শেষ ৭ দিন</span>
          </div>
          <canvas id="viewsChart" height="100"></canvas>
        </div>
        <div class="chart-card">
          <div class="chart-card-header">
            <h5>Property Status</h5>
          </div>
          <canvas id="statusChart" height="200"></canvas>
        </div>
      </div>

      <!-- Quick Pending Approvals -->
      <?php if (!empty($pendingProps)): ?>
      <div class="admin-section">
        <div class="section-head">
          <h5><i class="bi bi-clock-history me-2 text-accent"></i>অনুমোদন বাকি</h5>
          <a href="?page=admin-dashboard&tab=properties" class="btn-link-accent">সব দেখুন →</a>
        </div>
        <div class="pending-cards">
          <?php foreach (array_slice($pendingProps, 0, 3) as $p): ?>
          <?= renderPendingCard($p) ?>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- ════ PROPERTIES ════ -->
      <?php elseif ($activeTab === 'properties'): ?>
      <div class="admin-section">
        <div class="section-head">
          <h5>সব Pending Property (<?= count($pendingProps) ?>)</h5>
          <div class="d-flex gap-2">
            <input type="text" class="admin-search" placeholder="খুঁজুন..."
                   oninput="filterRows(this.value,'propTable')">
          </div>
        </div>

        <?php if (empty($pendingProps)): ?>
        <div class="admin-empty">
          <i class="bi bi-check-circle-fill text-accent"></i>
          <p>সব Property approved!</p>
        </div>
        <?php else: ?>
        <div class="pending-cards" id="propTable">
          <?php foreach ($pendingProps as $p): ?>
          <?= renderPendingCard($p) ?>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- ════ USERS ════ -->
      <?php elseif ($activeTab === 'users'): ?>
      <div class="admin-section">
        <div class="section-head">
          <h5>সব ব্যবহারকারী (<?= count($users) ?>)</h5>
          <input type="text" class="admin-search"
                 placeholder="নাম বা email..."
                 oninput="filterRows(this.value,'userTable')">
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table" id="userTable">
            <thead>
              <tr>
                <th>ব্যবহারকারী</th>
                <th>ভূমিকা</th>
                <th>Property</th>
                <th>স্ট্যাটাস</th>
                <th>যোগদান</th>
                <th>অ্যাকশন</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
              <tr>
                <td>
                  <div class="user-cell">
                    <div class="user-cell-avatar">
                      <?= strtoupper(substr($u['name'],0,1)) ?>
                    </div>
                    <div>
                      <strong><?= htmlspecialchars($u['name']) ?></strong>
                      <small><?= htmlspecialchars($u['email']) ?></small>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="role-chip role-<?= $u['role_name'] ?>">
                    <?= ucfirst($u['role_name']) ?>
                  </span>
                </td>
                <td><?= $u['prop_count'] ?></td>
                <td>
                  <span class="status-dot status-<?= $u['status'] ?>">
                    <?= ucfirst($u['status']) ?>
                  </span>
                </td>
                <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                <td>
                  <div class="action-btns">
                    <?php if ($u['status'] === 'banned'): ?>
                    <form method="POST" style="display:inline">
                      <input type="hidden" name="action" value="activate_user">
                      <input type="hidden" name="id" value="<?= $u['id'] ?>">
                      <button class="act-btn green" title="Activate">
                        <i class="bi bi-check-circle"></i>
                      </button>
                    </form>
                    <?php elseif ($u['id'] != $_SESSION['user_id']): ?>
                    <form method="POST" style="display:inline"
                          onsubmit="return confirm('নিশ্চিতভাবে ban করবেন?')">
                      <input type="hidden" name="action" value="ban_user">
                      <input type="hidden" name="id" value="<?= $u['id'] ?>">
                      <button class="act-btn red" title="Ban">
                        <i class="bi bi-slash-circle"></i>
                      </button>
                    </form>
                    <?php endif; ?>
                    <a href="?page=user-detail&id=<?= $u['id'] ?>"
                       class="act-btn blue" title="বিস্তারিত">
                      <i class="bi bi-eye"></i>
                    </a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ════ TRANSACTIONS ════ -->
      <?php elseif ($activeTab === 'transactions'): ?>
      <div class="admin-section">
        <div class="section-head">
          <h5>লেনদেনের ইতিহাস</h5>
          <span class="kpi-sub">
            মোট: ৳<?= number_format($stats['total_revenue']) ?>
          </span>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>#</th><th>ব্যবহারকারী</th><th>ধরন</th>
                <th>পরিমাণ</th><th>Gateway</th><th>স্ট্যাটাস</th><th>তারিখ</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($transactions as $t): ?>
              <tr>
                <td><?= $t['id'] ?></td>
                <td>
                  <strong><?= htmlspecialchars($t['uname']) ?></strong><br>
                  <small><?= htmlspecialchars($t['uemail']) ?></small>
                </td>
                <td><?= ucfirst($t['type']) ?></td>
                <td class="fw-bold text-accent">৳<?= number_format($t['amount']) ?></td>
                <td><?= $t['gateway'] ?? '—' ?></td>
                <td>
                  <span class="status-dot status-<?= $t['status'] ?>">
                    <?= ucfirst($t['status']) ?>
                  </span>
                </td>
                <td><?= date('d M Y', strtotime($t['created_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ════ MATERIAL RATES ════ -->
      <?php elseif ($activeTab === 'rates'): ?>
      <div class="admin-section">
        <div class="section-head">
          <h5><i class="bi bi-cash-stack me-2 text-accent"></i>উপকরণের দাম নিয়ন্ত্রণ</h5>
          <small class="text-muted">পরিবর্তন করলে Cost Estimator এ auto-update হবে</small>
        </div>
        <div class="rates-grid">
          <?php foreach ($materialRates as $r): ?>
          <div class="rate-edit-card">
            <div class="rate-edit-name"><?= htmlspecialchars($r['name']) ?></div>
            <div class="rate-edit-unit">প্রতি <?= htmlspecialchars($r['unit']) ?></div>
            <form method="POST" class="rate-edit-form">
              <input type="hidden" name="action" value="update_rate">
              <input type="hidden" name="id"     value="<?= $r['id'] ?>">
              <div class="rate-input-wrap">
                <span>৳</span>
                <input type="number" name="rate" class="rate-input"
                       value="<?= $r['rate'] ?>" step="0.01" min="0">
              </div>
              <button type="submit" class="rate-save-btn">
                <i class="bi bi-check2"></i>
              </button>
            </form>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- ════ LOGS ════ -->
      <?php elseif ($activeTab === 'logs'): ?>
      <div class="admin-section">
        <div class="section-head">
          <h5>Activity Logs</h5>
          <input type="text" class="admin-search"
                 placeholder="action filter..."
                 oninput="filterRows(this.value,'logTable')">
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table" id="logTable">
            <thead>
              <tr><th>ব্যবহারকারী</th><th>Action</th><th>বিস্তারিত</th><th>IP</th><th>সময়</th></tr>
            </thead>
            <tbody>
              <?php foreach ($logs as $l): ?>
              <tr>
                <td><?= htmlspecialchars($l['uname'] ?? 'Guest') ?></td>
                <td><code class="action-code"><?= htmlspecialchars($l['action']) ?></code></td>
                <td><?= htmlspecialchars(substr($l['details'] ?? '', 0, 60)) ?></td>
                <td><?= htmlspecialchars($l['ip_address']) ?></td>
                <td><?= date('d M, H:i', strtotime($l['created_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

    </div><!-- /.admin-content -->
  </div><!-- /.admin-main -->
</div><!-- /.admin-shell -->

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Charts ────────────────────────────────────────
<?php if ($activeTab === 'overview'): ?>

const viewsDates = <?= json_encode(array_column($viewsChart,'d')) ?>;
const viewsCounts= <?= json_encode(array_column($viewsChart,'c')) ?>;

new Chart(document.getElementById('viewsChart'), {
  type: 'line',
  data: {
    labels: viewsDates,
    datasets: [{
      label: 'Views',
      data: viewsCounts,
      borderColor: '#C5A059',
      backgroundColor: 'rgba(197,160,89,0.08)',
      borderWidth: 2.5,
      fill: true,
      tension: 0.4,
      pointBackgroundColor: '#C5A059',
      pointRadius: 5,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#64748B' }},
      y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#64748B' }}
    }
  }
});

new Chart(document.getElementById('statusChart'), {
  type: 'doughnut',
  data: {
    labels: ['Approved','Pending','Rejected','Sold'],
    datasets: [{
      data: [
        <?= $db->queryOne("SELECT COUNT(*) c FROM properties WHERE status='approved'")['c'] ?>,
        <?= $stats['pending_props'] ?>,
        <?= $db->queryOne("SELECT COUNT(*) c FROM properties WHERE status='rejected'")['c'] ?>,
        <?= $db->queryOne("SELECT COUNT(*) c FROM properties WHERE status='sold'")['c'] ?>,
      ],
      backgroundColor: ['#22C55E','#F59E0B','#EF4444','#3B82F6'],
      borderWidth: 0,
      hoverOffset: 6,
    }]
  },
  options: {
    cutout: '65%',
    plugins: {
      legend: { position: 'bottom', labels: { color: '#64748B', padding: 16 }}
    }
  }
});
<?php endif; ?>

// ── Sidebar Toggle ────────────────────────────────
function toggleSidebar() {
  document.getElementById('adminSidebar').classList.toggle('collapsed');
}

// ── Filter Rows ───────────────────────────────────
function filterRows(q, tableId) {
  const rows = document.querySelectorAll('#' + tableId + ' tbody tr, #' + tableId + ' .pending-card');
  q = q.toLowerCase();
  rows.forEach(r => {
    r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}
</script>

<?php
// ── Helper: Pending Property Card ───────────────
function renderPendingCard(array $p): string {
  $cover = $p['cover']
    ? UPLOAD_URL . 'properties/' . $p['cover']
    : APP_URL . '/assets/images/no-image.webp';
  return "
  <div class='pending-card'>
    <img src='{$cover}' class='pending-card-img' alt=''>
    <div class='pending-card-body'>
      <div class='d-flex justify-content-between align-items-start flex-wrap gap-2'>
        <div>
          <h6>" . htmlspecialchars($p['title']) . "</h6>
          <small class='text-muted'>
            <i class='bi bi-geo-alt me-1'></i>" . htmlspecialchars($p['area_name']) . "
            &nbsp;·&nbsp;" . htmlspecialchars($p['type_name']) . "
            &nbsp;·&nbsp;<b>৳" . number_format($p['price']) . "</b>
          </small>
        </div>
        <small class='text-muted'>" . date('d M Y', strtotime($p['created_at'])) . "</small>
      </div>
      <div class='pending-agent'>
        <i class='bi bi-person-circle text-accent'></i>
        " . htmlspecialchars($p['agent_name']) . "
      </div>
    </div>
    <div class='pending-card-actions'>
      <a href='?page=property&id={$p['id']}' class='act-btn blue' target='_blank'>
        <i class='bi bi-eye'></i>
      </a>
      <form method='POST' style='display:inline'>
        <input type='hidden' name='action' value='approve_property'>
        <input type='hidden' name='id' value='{$p['id']}'>
        <input type='hidden' name='tab' value='properties'>
        <button class='act-btn green' title='Approve'>
          <i class='bi bi-check-lg'></i>
        </button>
      </form>
      <form method='POST' style='display:inline'
            onsubmit=\"return confirm('Reject করবেন?')\">
        <input type='hidden' name='action' value='reject_property'>
        <input type='hidden' name='id' value='{$p['id']}'>
        <input type='hidden' name='tab' value='properties'>
        <button class='act-btn red' title='Reject'>
          <i class='bi bi-x-lg'></i>
        </button>
      </form>
      <form method='POST' style='display:inline'>
        <input type='hidden' name='action' value='toggle_featured'>
        <input type='hidden' name='id' value='{$p['id']}'>
        <input type='hidden' name='tab' value='properties'>
        <button class='act-btn gold' title='Featured Toggle'>
          <i class='bi bi-star'></i>
        </button>
      </form>
    </div>
  </div>";
}
?>