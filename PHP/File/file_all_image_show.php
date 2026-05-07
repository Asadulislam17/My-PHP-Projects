<?php
session_start();
$directory = "uploads/";

if(isset($_POST['submit'])) {
    $filename = $_FILES['fileToUpload']['name'];
    $tempname = $_FILES['fileToUpload']['tmp_name'];
    
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    $target_path = $directory . $filename;

    if (move_uploaded_file($tempname, $target_path)) {
        echo "<b>নতুন ফাইলটি সফলভাবে আপলোড হয়েছে।</b><br>";
    }
}
?>

<!DOCTYPE html>
<html>
<body>
    <form action="" method="post" enctype="multipart/form-data">
        ইমেজ সিলেক্ট করুন:
        <input type="file" name="fileToUpload" required>
        <input type="submit" value="Upload & Show" name="submit">
    </form>

    <hr>
    <h2>ফোল্ডারের সকল ইমেজ:</h2>

    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
        <?php
        $images = glob($directory . "*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);

        if ($images) {
            foreach ($images as $image) {
                echo "<div>
                        <img src='$image' width='150' style='border:1px solid #ccc; padding:5px;'>
                        <br><small>".basename($image)."</small>
                      </div>";
            }
        } else {
            echo "কোনো ইমেজ পাওয়া যায়নি।";
        }
        ?>
    </div>
</body>
</html>
