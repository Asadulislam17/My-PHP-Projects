<?php
require_once __DIR__ . '/../config/db.php';
require_role('hub_manager');
$hub_id = $_SESSION['user']['hub_id'];
$stmt = $pdo->prepare("SELECT p.*, rh.name AS rh FROM parcels p JOIN hubs rh ON rh.id=p.receiver_hub_id
  WHERE p.sender_hub_id=? AND p.status IN ('received','transit') ORDER BY p.id DESC");
$stmt->execute([$hub_id]);
include __DIR__ . '/../includes/header.php';
?>
<h1>Outgoing Parcels</h1>
<table class="table">
<thead><tr><th>Tracking</th><th>To Hub</th><th>Receiver</th><th>Status</th><th>Date</th></tr></thead>
<tbody>
<?php foreach ($stmt->fetchAll() as $p): ?>
<tr><td><?= $p['tracking_id'] ?></td><td><?= $p['rh'] ?></td>
<td><?= htmlspecialchars($p['receiver_name']) ?></td>
<td><span class="badge <?= $p['status'] ?>"><?= $p['status'] ?></span></td>
<td><?= date('d M Y', strtotime($p['created_at'])) ?></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php include __DIR__ . '/../includes/footer.php'; ?>
