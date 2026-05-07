<?php
require_once 'StorageInterface.php';
require_once 'FileStorage.php';
require_once 'UserManager.php';

$fileDb = new FileStorage('users.txt');
$userManager = new UserManager($fileDb);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ইনপুট ক্লিন করা ভালো প্র্যাকটিস
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    $userManager->addUser($name, $email);
    header("Location: login.php"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration | Student Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://googleapis.com" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8 mx-4 border border-gray-100">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://w3.org" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">স্টুডেন্ট রেজিস্ট্রেশন</h2>
            <p class="text-gray-500 mt-2">নতুন অ্যাকাউন্ট তৈরি করতে তথ্য দিন</p>
        </div>

        <!-- Registration Form -->
        <form method="post" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">পুরো নাম</label>
                <input type="text" name="name" 
                    placeholder="আপনার নাম লিখুন" 
                    required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all duration-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ইমেইল এড্রেস</label>
                <input type="email" name="email" 
                    placeholder="example@mail.com" 
                    required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all duration-200">
            </div>

            <button type="submit" name="s" 
                class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:-translate-y-1">
                সেভ করুন
            </button>
        </form>

        <!-- Login Link -->
        <div class="mt-8 text-center border-t pt-6">
            <p class="text-sm text-gray-600">
                ইতিমধ্যেই অ্যাকাউন্ট আছে? <a href="login.php" class="text-blue-600 font-medium hover:underline">লগইন করুন</a>
            </p>
        </div>
    </div>

</body>
</html>
