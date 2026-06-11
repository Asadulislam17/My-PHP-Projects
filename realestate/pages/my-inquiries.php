<?php
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../config/Database.php';

$auth = Auth::getInstance();
$auth->requireLogin();

$db     = Database::getInstance();
$userId = $_SESSION['user_id'];
$role   = $_SESSION['user_role'];

// =========================
// FETCH INQUIRIES
// =========================
if ($role === 'buyer') {
    $inquiries = $db->query(
        "SELECT i.*,p.title prop_title,p.id prop_id,
                a.name agent_name,a.phone agent_phone,a.email agent_email,
                (SELECT image_path FROM property_images WHERE property_id=p.id AND is_cover=1 LIMIT 1) cover
         FROM inquiries i
         JOIN properties p ON p.id=i.property_id
         JOIN users a ON a.id=i.agent_id
         WHERE i.sender_id=?
         ORDER BY i.created_at DESC",
        [$userId]
    );
} else {
    $inquiries = $db->query(
        "SELECT i.*,p.title prop_title,p.id prop_id,
                s.name sender_name,s.phone sender_phone,s.email sender_email,
                (SELECT image_path FROM property_images WHERE property_id=p.id AND is_cover=1 LIMIT 1) cover
         FROM inquiries i
         JOIN properties p ON p.id=i.property_id
         JOIN users s ON s.id=i.sender_id
         WHERE i.agent_id=?
         ORDER BY i.created_at DESC",
        [$userId]
    );
}

// =========================
// REPLY HANDLER
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role !== 'buyer') {
    $inqId = (int)($_POST['inquiry_id'] ?? 0);
    $reply = trim($_POST['reply'] ?? '');

    if ($inqId && $reply) {
        $db->execute(
            "UPDATE inquiries 
             SET reply=?, status='replied', replied_at=NOW() 
             WHERE id=? AND agent_id=?",
            [$reply, $inqId, $userId]
        );

        $_SESSION['flash'] = "Reply পাঠানো হয়েছে!";
        header("Location: ?page=my-inquiries");
        exit;
    }
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// =========================
// STATS
// =========================
$stats = [
    'total'   => count($inquiries),
    'pending' => count(array_filter($inquiries, fn($i)=>$i['status']==='pending')),
    'replied' => count(array_filter($inquiries, fn($i)=>$i['status']==='replied')),
    'closed'  => count(array_filter($inquiries, fn($i)=>$i['status']==='closed')),
];
?>

<!-- ================= HEADER ================= -->
<div class="inner-page-header">
  <div class="container">
    <div class="iph-content">
      <div>
        <h1>💬 <?= $role === 'buyer' ? 'আমার Inquiries' : 'Inquiry Dashboard' ?></h1>
        <p><?= $stats['total'] ?> total inquiries</p>
      </div>

      <div class="d-flex gap-2 flex-wrap">
        <span class="stat-chip pending"><?= $stats['pending'] ?> Pending</span>
        <span class="stat-chip replied"><?= $stats['replied'] ?> Replied</span>
        <span class="stat-chip closed"><?= $stats['closed'] ?> Closed</span>
      </div>
    </div>
  </div>
</div>

<div class="container py-5">

<?php if ($flash): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<!-- ================= FILTER ================= -->
<div class="inq-filter mb-4 d-flex gap-2 flex-wrap">
  <button class="inq-tab active" onclick="filterInquiries('all',this)">All</button>
  <button class="inq-tab" onclick="filterInquiries('pending',this)">Pending</button>
  <button class="inq-tab" onclick="filterInquiries('replied',this)">Replied</button>
  <button class="inq-tab" onclick="filterInquiries('closed',this)">Closed</button>

  <input type="text" class="inq-search ms-auto"
         placeholder="Search..."
         oninput="searchInquiries(this.value)">
</div>

<?php if (empty($inquiries)): ?>
  <div class="text-center py-5">
    <h3>No inquiries found</h3>
  </div>

<?php else: ?>

<div id="inquiriesList">

<?php foreach ($inquiries as $inq): ?>

<?php
$cover = $inq['cover']
  ? UPLOAD_URL . 'properties/' . $inq['cover']
  : APP_URL . '/assets/images/no-image.webp';

$text = strtolower($inq['prop_title']);
?>

<div class="inq-card" data-status="<?= $inq['status'] ?>" data-text="<?= htmlspecialchars($text) ?>">

  <!-- HEADER -->
  <div class="inq-card-header">
    <img src="<?= $cover ?>" class="inq-thumb">

    <div class="flex-grow-1">
      <a href="?page=property&id=<?= $inq['prop_id'] ?>" class="inq-title">
        <?= htmlspecialchars($inq['prop_title']) ?>
      </a>

      <div class="inq-meta">
        <?php if ($role === 'buyer'): ?>
          <span><?= htmlspecialchars($inq['agent_name']) ?></span>
        <?php else: ?>
          <span><?= htmlspecialchars($inq['sender_name']) ?></span>
        <?php endif; ?>
        <span><?= date('d M Y', strtotime($inq['created_at'])) ?></span>
      </div>
    </div>

    <span class="inq-status status-<?= $inq['status'] ?>">
      <?= ucfirst($inq['status']) ?>
    </span>
  </div>

  <!-- MESSAGE -->
  <div class="inq-msg">
    <div class="msg sent"><?= nl2br(htmlspecialchars($inq['message'])) ?></div>

    <?php if ($inq['reply']): ?>
    <div class="msg reply"><?= nl2br(htmlspecialchars($inq['reply'])) ?></div>
    <?php endif; ?>
  </div>

  <!-- REPLY -->
  <?php if ($role !== 'buyer' && $inq['status']==='pending'): ?>
  <form method="POST" class="inq-reply">
    <input type="hidden" name="inquiry_id" value="<?= $inq['id'] ?>">
    <textarea name="reply" placeholder="Write reply..." required></textarea>
    <button>Send</button>
  </form>
  <?php endif; ?>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

</div>

<!-- ================= JS ================= -->
<script>
function filterInquiries(status, el){
  document.querySelectorAll('.inq-tab').forEach(t=>t.classList.remove('active'));
  el.classList.add('active');

  document.querySelectorAll('.inq-card').forEach(card=>{
    card.style.display = (status==='all' || card.dataset.status===status)
      ? 'block'
      : 'none';
  });
}

function searchInquiries(q){
  q = q.toLowerCase();
  document.querySelectorAll('.inq-card').forEach(card=>{
    card.style.display = card.dataset.text.includes(q) ? 'block' : 'none';
  });
}
</script>