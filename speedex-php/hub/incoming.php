<?php
require_once __DIR__ . '/../config/db.php';
require_role('hub_manager');
$hub_id = $_SESSION['user']['hub_id'];
$stmt = $pdo->prepare("SELECT p.*, sh.name AS sh FROM parcels p JOIN hubs sh ON sh.id=p.sender_hub_id
  WHERE p.receiver_hub_id=? AND p.status IN ('transit','arrived','ready') ORDER BY p.id DESC");
$stmt->execute([$hub_id]);
include __DIR__ . '/../includes/header.php';
?>
<h1>Incoming Parcels</h1>
<table class="table">
<thead><tr><th>Tracking</th><th>From Hub</th><th>Sender</th><th>Receiver</th><th>Status</th><th>Date</th></tr></thead>
<tbody>
<?php foreach ($stmt->fetchAll() as $p): ?>
<tr><td><?= $p['tracking_id'] ?></td><td><?= $p['sh'] ?></td>
<td><?= htmlspecialchars($p['sender_name']) ?></td>
<td><?= htmlspecialchars($p['receiver_name']) ?></td>
<td><span class="badge <?= $p['status'] ?>"><?= $p['status'] ?></span></td>
<td><?= date('d M Y', strtotime($p['created_at'])) ?></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php include __DIR__ . '/../includes/footer.php'; ?>
