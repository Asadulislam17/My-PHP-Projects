<?php
session_start();

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: login.php"); 
    exit;
}

$message = "";
$status_type = "error";

if(isset($_POST['submit'])) {
    $file = $_FILES['fileToUpload'];
    $filename = $file['name'];
    $tempname = $file['tmp_name'];
    $filesize = $file['size'];
    
    $allowed_extensions = array("jpg", "jpeg", "png", "gif");
    $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $max_size = 5 * 1024 * 1024;

    if (!in_array($file_extension, $allowed_extensions)) {
        $message = "দুঃখিত, শুধুমাত্র JPG, JPEG, PNG ও GIF ফাইল আপলোড করা যাবে।";
    } 
    elseif ($filesize > $max_size) {
        $message = "ফাইলটি অনেক বড়! ৫ মেগাবাইটের নিচের ফাইল আপলোড করুন।";
    } 
    else {
        $directory = "uploads/";
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        $new_filename = uniqid() . "_" . basename($filename);
        $target_path = $directory . $new_filename;

        if (move_uploaded_file($tempname, $target_path)) {
            $message = "সফলভাবে আপলোড হয়েছে!";
            $status_type = "success";
        } else {
            $message = "সার্ভারে ফাইল সেভ করতে সমস্যা হয়েছে।";
        }
    }
}


$images = glob("uploads/*.{jpg,jpeg,png,gif}", GLOB_BRACE);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Photo Gallery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://googleapis.com" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen font-[Inter]">

    <!-- Navbar -->
    <nav class="bg-white shadow-sm px-6 py-4 flex justify-between items-center sticky top-0 z-10">
        <h1 class="text-xl font-bold text-blue-600">Student Gallery</h1>
        <div class="flex items-center gap-4">
            <span class="text-gray-700 hidden sm:block">স্বাগতম, <b><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'ইউজার'); ?></b></span>
            <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition">লগআউট</a>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto mt-8 p-4">
        
        <!-- Upload Form -->
        <div class="max-w-xl mx-auto bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mb-12">
            <h2 class="text-lg font-semibold mb-4 text-gray-800 text-center">নতুন ছবি আপলোড করুন</h2>
            
            <?php if($message): ?>
                <div class="mb-4 p-3 rounded-lg <?php echo ($status_type == 'success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> text-sm text-center border">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="" method="post" enctype="multipart/form-data" class="space-y-4">
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-500 transition-colors cursor-pointer relative">
                    <input type="file" name="fileToUpload" id="fileToUpload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required>
                    <svg xmlns="http://w3.org" class="mx-auto h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">এখানে ক্লিক করে ফাইল সিলেক্ট করুন</p>
                </div>
                <button type="submit" name="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-xl transition shadow-lg shadow-blue-100">
                    Upload to Gallery
                </button>
            </form>
        </div>

        <!-- Gallery Grid -->
        <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">আপনার গ্যালারি</h3>
        
        <?php if(empty($images)): ?>
            <div class="text-center py-20 bg-white rounded-2xl border border-dashed border-gray-300">
                <p class="text-gray-400">এখনো কোনো ছবি আপলোড করা হয়নি।</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach(array_reverse($images) as $img): ?>
                    <div class="group bg-white p-2 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all">
                        <div class="overflow-hidden rounded-lg aspect-square">
                            <img src="<?php echo $img; ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <div class="mt-2 text-[10px] text-gray-400 truncate px-1">
                            <?php echo basename($img); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

</body>
</html>
