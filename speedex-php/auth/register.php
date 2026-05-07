<?php
require_once __DIR__ . '/../config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $role = $_POST['role'] ?? 'hub_manager';
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $password = $_POST['password'] ?? '';
  $hub_id = $_POST['hub_id'] ?? null;

  if ($role === 'admin') {
    if (($_POST['admin_secret_code'] ?? '') !== ADMIN_SECRET_CODE) {
      $error = 'Invalid admin secret code';
    }
  }

  if (!$error && $name && $email && $password) {
    try {
      $stmt = $pdo->prepare("INSERT INTO users (name,email,phone,password,role,hub_id) VALUES (?,?,?,?,?,?)");
      $stmt->execute([$name, $email, $phone, password_hash($password, PASSWORD_BCRYPT),
                      $role, $role === 'hub_manager' ? $hub_id : null]);
      header('Location: login.php?registered=1'); exit;
    } catch (PDOException $e) {
      $error = 'Email already exists';
    }
  }
}

$hubs = $pdo->query("SELECT * FROM hubs")->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="auth-card">
  <h1>Create Account</h1>
  <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="POST">
    <label>Role</label>
    <select name="role" id="roleSelect" onchange="document.getElementById('hubFields').style.display=this.value==='hub_manager'?'block':'none';document.getElementById('adminFields').style.display=this.value==='admin'?'block':'none'">
      <option value="hub_manager">Hub Manager</option>
      <option value="admin">Admin</option>
    </select>
    <input name="name" placeholder="Full Name" required>
    <input name="email" type="email" placeholder="Email" required>
    <input name="phone" placeholder="Phone">
    <input name="password" type="password" placeholder="Password" required>
    <div id="hubFields">
      <select name="hub_id">
        <?php foreach ($hubs as $h): ?>
          <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div id="adminFields" style="display:none">
      <input name="admin_secret_code" placeholder="Admin Secret Code">
    </div>
    <button type="submit">Register</button>
  </form>
  <p><a href="login.php">Already have an account? Login</a></p>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
