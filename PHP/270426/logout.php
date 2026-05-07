<?php
session_start();
session_unset(); // সব সেশন ভেরিয়েবল মুছে ফেলা
session_destroy(); // সেশন ধ্বংস করা

// লগইন পেজে রিডাইরেক্ট
header("Location: login.php");
exit();
?>
