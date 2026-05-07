<?php
session_start();

$db_file = "users.txt";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // --- রেজিস্ট্রেশন সেকশন ---
    if (isset($_POST['register'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $pass = trim($_POST['password']);
        $re_pass = trim($_POST['repassword']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die("Invalid Email Format!");
        }
        if ($pass !== $re_pass) {
            die("Passwords do not match!");
        }

        // ডাটা ফরম্যাট: নাম (0) | ইমেইল (1) | পাসওয়ার্ড (2)
        $userData = $name . " | " . $email . " | " . $pass . PHP_EOL;

        // ফাইলে ডাটা সেভ করা
        file_put_contents($db_file, $userData, FILE_APPEND);

        header("Location: login.php?msg=registered");
        exit();
    }

    // --- লগইন সেকশন ---
    if (isset($_POST['login'])) {
        $inputEmail = trim($_POST['email']); // লগইন ফর্মে input name="name" থাকলে এটা কাজ করবে
        $inputPass = trim($_POST['password']);
        $isFound = false;
        $userName = "";

        if (file_exists($db_file)) {
            $allUsers = file($db_file);

            foreach ($allUsers as $line) {
                // ফাইল থেকে ডাটা আলাদা করা
                $data = explode(" | ", trim($line));
                
                // চেক: ইনডেক্স ১ (ইমেইল) এবং ইনডেক্স ২ (পাসওয়ার্ড)
                if (isset($data[1]) && isset($data[2])) {
                    if ($data[1] === $inputEmail && $data[2] === $inputPass) {
                        $isFound = true;
                        $userName = $data[0]; // ইউজারের নাম (Index 0) সংগ্রহ করা
                        break;
                    }
                }
            }
        }

        if ($isFound) {
            $_SESSION['user'] = $userName; // সেশনে আসল নাম সেভ করা হলো
            header("Location: upload.php");
            exit();
        } else {
            echo "দুঃখিত! ইমেইল অথবা পাসওয়ার্ড মেলেনি। <a href='login.php'>আবার চেষ্টা করুন</a>";
        }
    }
}
?>
