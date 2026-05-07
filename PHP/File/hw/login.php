<?php
session_start();
include('db.php');
$error = "";

if (isset($_SESSION['user'])) { header("Location: dashboard.php"); exit(); }

if (isset($_POST['login'])) {
    $user_input = mysqli_real_escape_string($conn, $_POST['user_input']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username='$user_input' OR email='$user_input' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user['username'];
            header("Location: dashboard.php");
            exit();
        } else { $error = "ভুল পাসওয়ার্ড!"; }
    } else { $error = "ইউজার পাওয়া যায়নি!"; }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Bootstrap</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        body { background: #f8f9fa; display: flex; align-items: center; min-height: 100vh; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow border-0">
                <div class="card-body p-4 text-center">
                    <h3 class="mb-4 text-primary">Login</h3>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger p-2 small"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3 text-start">
                            <label class="form-label">Username or Email</label>
                            <input type="text" name="user_input" class="form-control" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100 mb-3">Login</button>
                    </form>
                    <a href="registration.php" class="text-decoration-none small">নতুন একাউন্ট তৈরি করুন</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
