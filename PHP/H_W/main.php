<?php
session_start();

if (!isset($_SESSION['is_logged_in'])) {
    header('Location: login.php'); 
    exit;
}
?>

<h1>স্বাগতম, <?php echo $_SESSION['user_name']; ?>!</h1>
<a href="logout.php">লগআউট করুন</a>
