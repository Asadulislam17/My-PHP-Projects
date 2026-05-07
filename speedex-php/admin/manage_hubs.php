<?php
require_once __DIR__ . '/../config/db.php';
require_role('admin');
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $pdo->prepare("INSERT INTO hubs (name,address) VALUES (?,?)")
      ->execute([$_POST['name'], $_POST['address']]);
  header('Location: manage_hubs.php'); exit;
}
$hubs = $pdo->query("SELECT * FROM hubs")->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<h1>Manage Hubs</h1>
<form method="POST" class="inline-form">
  <input name="name" placeholder="Hub Name" required>
  <input name="address" placeholder="Address" required>
  <button type="submit">Add Hub</button>
</form>
<table class="table">
<thead><tr><th>ID</th><th>Name</th><th>Address</th></tr></thead>
<tbody>
<?php foreach ($hubs as $h): ?>
<tr><td><?= $h['id'] ?></td><td><?= htmlspecialchars($h['name']) ?></td><td><?= htmlspecialchars($h['address']) ?></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php include __DIR__ . '/../includes/footer.php'; ?>
