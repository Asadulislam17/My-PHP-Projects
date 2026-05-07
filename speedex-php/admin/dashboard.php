<?php
require_once __DIR__ . '/../config/db.php';
require_role('admin');

$stats = [
  'total'    => $pdo->query("SELECT COUNT(*) FROM parcels")->fetchColumn(),
  'transit'  => $pdo->query("SELECT COUNT(*) FROM parcels WHERE status='transit'")->fetchColumn(),
  'delivered'=> $pdo->query("SELECT COUNT(*) FROM parcels WHERE status='delivered'")->fetchColumn(),
  'pending'  => $pdo->query("SELECT COUNT(*) FROM parcels WHERE status IN ('received','arrived','ready')")->fetchColumn(),
];
$parcels = $pdo->query("SELECT p.*, sh.name AS sh, rh.name AS rh FROM parcels p
  JOIN hubs sh ON sh.id=p.sender_hub_id JOIN hubs rh ON rh.id=p.receiver_hub_id
  ORDER BY p.id DESC LIMIT 20")->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<h1>Admin Dashboard</h1>
<div class="stats">
  <div class="stat"><span>Total Parcels</span><b><?= $stats['total'] ?></b></div>
  <div class="stat"><span>In Transit</span><b><?= $stats['transit'] ?></b></div>
  <div class="stat"><span>Delivered</span><b><?= $stats['delivered'] ?></b></div>
  <div class="stat"><span>Pending</span><b><?= $stats['pending'] ?></b></div>
</div>
<h2>Recent Parcels</h2>
<table class="table">
  <thead><tr><th>Tracking</th><th>Sender</th><th>Receiver</th><th>From</th><th>To</th><th>Status</th><th>Payment</th></tr></thead>
  <tbody>
  <?php foreach ($parcels as $p): ?>
    <tr>
      <td><?= $p['tracking_id'] ?></td>
      <td><?= htmlspecialchars($p['sender_name']) ?></td>
      <td><?= htmlspecialchars($p['receiver_name']) ?></td>
      <td><?= $p['sh'] ?></td>
      <td><?= $p['rh'] ?></td>
      <td><span class="badge <?= $p['status'] ?>"><?= $p['status'] ?></span></td>
      <td><?= $p['payment_type'] ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php include __DIR__ . '/../includes/footer.php'; ?>
