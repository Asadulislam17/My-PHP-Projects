<?php
require_once __DIR__ . '/../config/db.php';
require_role('admin');
$users = $pdo->query("SELECT u.*, h.name AS hub FROM users u LEFT JOIN hubs h ON h.id=u.hub_id ORDER BY u.id DESC")->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<h1>Manage Users</h1>
<table class="table">
<thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Hub</th></tr></thead>
<tbody>
<?php foreach ($users as $u): ?>
<tr><td><?= $u['id'] ?></td><td><?= htmlspecialchars($u['name']) ?></td>
<td><?= htmlspecialchars($u['email']) ?></td><td><?= $u['role'] ?></td>
<td><?= htmlspecialchars($u['hub'] ?? '-') ?></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php include __DIR__ . '/../includes/footer.php'; ?>
