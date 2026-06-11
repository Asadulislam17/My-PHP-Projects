<?php
// সেশন ডেটা ক্লিয়ার করা
$_SESSION = [];

// সেশনটি পুরোপুরি ধ্বংস করা
if (session_id()) {
    session_destroy();
}

// লগইন পেজে রিডাইরেক্ট করা
echo "<script>
        window.location.href = '" . APP_URL . "/index.php?page=login';
      </script>";
exit;
