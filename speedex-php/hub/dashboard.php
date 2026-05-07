<?php
require_once __DIR__ . '/../config/db.php';
require_role('hub_manager');
$hub_id = $_SESSION['user']['hub_id'];

$incoming = $pdo->prepare("SELECT COUNT(*) FROM parcels WHERE receiver_hub_id=? AND status IN ('transit','arrived')");
$incoming->execute([$hub_id]);
$outgoing = $pdo->prepare("SELECT COUNT(*) FROM parcels WHERE sender_hub_id=? AND status='transit'");
$outgoing->execute([$hub_id]);
$delivered = $pdo->prepare("SELECT COUNT(*) FROM parcels WHERE receiver_hub_id=? AND status='delivered'");
$delivered->execute([$hub_id]);

$parcels = $pdo->prepare("SELECT p.*, sh.name AS sh FROM parcels p
  JOIN hubs sh ON sh.id=p.sender_hub_id
  WHERE p.receiver_hub_id=? OR p.sender_hub_id=?
  ORDER BY p.id DESC LIMIT 20");
$parcels->execute([$hub_id, $hub_id]);

include __DIR__ . '/../includes/header.php';
?>
<h1>Hub Dashboard</h1>
<div class="stats">
  <div class="stat"><span>Incoming</span><b><?= $incoming->fetchColumn() ?></b></div>
  <div class="stat"><span>Outgoing</span><b><?= $outgoing->fetchColumn() ?></b></div>
  <div class="stat"><span>Delivered</span><b><?= $delivered->fetchColumn() ?></b></div>
</div>
<h2>Recent Activity</h2>
<table class="table">
  <thead><tr><th>Tracking</th><th>From Hub</th><th>Receiver</th><th>Status</th><th>Action</th></tr></thead>
  <tbody>
  <?php foreach ($parcels->fetchAll() as $p): ?>
    <tr>
      <td><?= $p['tracking_id'] ?></td>
      <td><?= $p['sh'] ?></td>
      <td><?= htmlspecialchars($p['receiver_name']) ?></td>
      <td><span class="badge <?= $p['status'] ?>"><?= $p['status'] ?></span></td>
      <td>
        <form method="POST" action="/api/update_status.php" style="display:inline">
          <input type="hidden" name="parcel_id" value="<?= $p['id'] ?>">
          <input type="hidden" name="hub_id" value="<?= $hub_id ?>">
          <select name="status">
            <option value="transit">In Transit</option>
            <option value="arrived">Arrived</option>
            <option value="ready">Ready</option>
            <option value="delivered">Delivered</option>
          </select>
          <button type="submit">Update</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php include __DIR__ . '/../includes/footer.php'; ?>
