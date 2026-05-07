<?php
require_once 'StorageInterface.php';
require_once 'FileStorage.php';
require_once 'UserManager.php';

$fileDb = new FileStorage('users.txt');
$userManager = new UserManager($fileDb);



if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['s'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];

    $userManager->login($name, $email);
}
?>

<!DOCTYPE html>
<html>
<body>
    <h2>Login Form</h2>

    <form method="post">
        <input type="text" name="name" placeholder="Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <button type="submit" name="s">Login</button>
    </form>
</body>
</html>
