<?php
session_start();
include('db.php');
$errors = [];

if (isset($_POST['register'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $re_password = $_POST['re_password'];
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);

    if (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email)) { $errors[] = "ইমেইল ফরম্যাট সঠিক নয়।"; }
    if (!preg_match("/^(?=.*[A-Z])(?=.*\d).{8,}$/", $password)) { $errors[] = "পাসওয়ার্ড অন্তত ৮ অক্ষরের হতে হবে (১টি বড় হাতের অক্ষর ও সংখ্যাসহ)।"; }
    if ($password !== $re_password) { $errors[] = "পাসওয়ার্ড দুটি মিলেনি।"; }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (full_name, email, username, password, address, contact_number) VALUES ('$full_name', '$email', '$username', '$hashed_password', '$address', '$contact')";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['user'] = $username;
            header("Location: dashboard.php");
        } else { $errors[] = "ইউজারনেম বা ইমেইল ইতিমধ্যে ব্যবহার করা হয়েছে।"; }
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration - Bootstrap</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white text-center">
                    <h4>User Registration</h4>
                </div>
                <div class="card-body p-4">
                    <?php foreach ($errors as $error): ?>
                        <div class="alert alert-danger p-2 small"><?php echo $error; ?></div>
                    <?php endforeach; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" placeholder="John Doe" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" placeholder="johndoe123" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact</label>
                                <input type="text" name="contact" class="form-control" placeholder="017..." required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Re-type Password</label>
                                <input type="password" name="re_password" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" name="register" class="btn btn-primary w-100 shadow-sm">Register Now</button>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                    <p class="mb-0">ইতিমধ্যে একাউন্ট আছে? <a href="login.php">লগইন করুন</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
