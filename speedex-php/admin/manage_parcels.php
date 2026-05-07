<?php
require_once __DIR__ . '/../config/db.php';
require_role('admin');
$parcels = $pdo->query("SELECT p.*, sh.name AS sh, rh.name AS rh FROM parcels p
  JOIN hubs sh ON sh.id=p.sender_hub_id JOIN hubs rh ON rh.id=p.receiver_hub_id
  ORDER BY p.id DESC")->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<h1>Manage Parcels</h1>
<table class="table">
<thead><tr><th>ID</th><th>Tracking</th><th>Sender</th><th>Receiver</th><th>Route</th><th>Status</th></tr></thead>
<tbody>
<?php foreach ($parcels as $p): ?>
<tr>
  <td><?= $p['id'] ?></td><td><?= $p['tracking_id'] ?></td>
  <td><?= htmlspecialchars($p['sender_name']) ?></td>
  <td><?= htmlspecialchars($p['receiver_name']) ?></td>
  <td><?= $p['sh'] ?> → <?= $p['rh'] ?></td>
  <td><span class="badge <?= $p['status'] ?>"><?= $p['status'] ?></span></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php include __DIR__ . '/../includes/footer.php'; ?>
