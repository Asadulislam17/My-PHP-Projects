<?php
require_once __DIR__ . '/../config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $role = $_POST['role'] ?? '';

  $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND role=?");
  $stmt->execute([$email, $role]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user'] = $user;
    header('Location: /' . $role . '/dashboard.php'); exit;
  }
  $error = 'Invalid credentials';
}
include __DIR__ . '/../includes/header.php';
?>
<div class="auth-card">
  <h1>Welcome Back!</h1>
  <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="POST">
    <input name="email" type="email" placeholder="Email" required>
    <input name="password" type="password" placeholder="Password" required>
    <select name="role" required>
      <option value="">Select Role</option>
      <option value="admin">Admin</option>
      <option value="hub_manager">Hub Manager</option>
    </select>
    <button type="submit">Login</button>
  </form>
  <p><a href="register.php">Create an account</a></p>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
