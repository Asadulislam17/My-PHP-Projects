<?php
session_start();

// আপনার আগের লজিক অপরিবর্তিত রাখা হয়েছে
require_once 'StorageInterface.php';
require_once 'FileStorage.php';
require_once 'UserManager.php';

$fileDb = new FileStorage('users.txt');
$userManager = new UserManager($fileDb);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['s'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    $userManager->login($name, $email);
    // লগইন সফল হলে রিডাইরেক্ট লজিক এখানে দিতে পারেন
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | My App</title>
    <!-- Tailwind CSS CDN for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://googleapis.com" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8 mx-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">স্বাগতম</h2>
            <p class="text-gray-500 mt-2">আপনার অ্যাকাউন্টে লগইন করুন</p>
        </div>

        <!-- Login Form -->
        <form method="post" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">আপনার নাম</label>
                <input type="text" name="name" 
                    placeholder="John Doe" 
                    required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ইমেইল এড্রেস</label>
                <input type="email" name="email" 
                    placeholder="example@mail.com" 
                    required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
            </div>

            <button type="submit" name="s" 
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:-translate-y-1">
                লগইন করুন
            </button>
        </form>

        <!-- Footer Info -->
        <div class="mt-8 text-center border-t pt-6">
            <p class="text-sm text-gray-600">
                অ্যাকাউন্ট নেই? <a href="#" class="text-blue-600 font-medium hover:underline">নিবন্ধন করুন</a>
            </p>
        </div>
    </div>

</body>
</html>
