<?php
session_start();

if (isset($_POST['submit_upload'])) {
    $title = $_POST['file_name'];
    $file = $_FILES['myFile'];

    
    $fileName = $file['name'];
    $fileTmp  = $file['tmp_name'];
    $fileSize = $file['size'];
    $uploadDir = 'uploads/';

    
    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    $maxSize = 2 * 1024 * 1024; 

    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($fileTmp);

    if (!in_array($mimeType, $allowedTypes)) {
        die("Error: Only JPG, PNG, and PDF files are allowed!");
    }

    
    if ($fileSize > $maxSize) {
        die("Error: File size must be less than 2MB!");
    }

  
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }


    $newFileName = time() . '_' . $fileName;
    $targetPath = $uploadDir . $newFileName;

    if (move_uploaded_file($fileTmp, $targetPath)) {
       
        $_SESSION['last_file'] = $targetPath;
        $_SESSION['last_title'] = $title;
        header("Location: upload.php?status=success");
    } else {
        echo "Upload failed. Please try again.";
    }
} else {
    header("Location: upload.php");
}
?>
