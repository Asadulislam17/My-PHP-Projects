<?php
session_start();
require_once 'StorageInterface.php';
require_once 'FileStorage.php';
require_once 'UserManager.php';

$fileDb = new FileStorage('users.txt');
$userManager = new UserManager($fileDb);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userManager->addUser($_POST['name'], $_POST['email']);
}
?>

<!DOCTYPE html>
<html>
<body>
    <form method="post">
        <input type="text" name="name" placeholder="Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <button type="submit" name="s">Save</button>
    </form>

    <h3>User List:</h3>
    <ul>
        <?php foreach ($userManager->getUsers() as $user): ?>
            <li><?php echo $user; ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
