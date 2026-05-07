<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>File Upload & Display</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; background: #f9f9f9; }
        .upload-box { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px #ddd; max-width: 500px; }
        .display-box { margin-top: 30px; }
        img { max-width: 100%; height: auto; border: 2px solid #ccc; border-radius: 5px; }
        .logout-btn { color: red; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

    <div class="upload-box">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?>!</h2>
        <a href="logout.php" class="logout-btn">Logout</a>
        <hr>
        
        <form action="upload_action.php" method="POST" enctype="multipart/form-data">
            <label>File Name / Description:</label><br>
            <input type="text" name="file_name" placeholder="Enter file name" required style="width:100%; padding:8px; margin:10px 0;"><br>
            
            <label>Choose File (JPG, PNG, PDF | Max 2MB):</label><br>
            <input type="file" name="myFile" required style="margin:10px 0;"><br>
            
            <button type="submit" name="submit_upload" style="padding:10px 20px; background:#007bff; color:white; border:none; border-radius:4px; cursor:pointer;">Upload Now</button>
        </form>
    </div>

    
    <div class="display-box">
        <?php if (isset($_SESSION['last_file'])): ?>
            <h3>Recently Uploaded: <?php echo $_SESSION['last_title']; ?></h3>
            <?php 
                $filePath = $_SESSION['last_file'];
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                
                if (in_array($extension, ['jpg', 'jpeg', 'png'])): ?>
                    <img src="<?php echo $filePath; ?>" alt="Uploaded Image">
                <?php elseif ($extension == 'pdf'): ?>
                    <embed src="<?php echo $filePath; ?>" type="application/pdf" width="100%" height="500px">
                <?php endif; ?>
        <?php endif; ?>
    </div>

</body>
</html>
