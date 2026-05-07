<?php 
include 'db_config.php';

// ইউআরএল থেকে আইডি নেওয়া
$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';
if(!$id) { header("Location: index.php"); exit(); }

// ওই নির্দিষ্ট স্টুডেন্টের তথ্য তুলে আনা
$result = mysqli_query($conn, "SELECT * FROM students WHERE id=$id");
$row = mysqli_fetch_assoc($result);

// আপডেট লজিক
if(isset($_POST['update'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $update_sql = "UPDATE students SET name='$name', email='$email', contact='$contact', address='$address' WHERE id=$id";
    if(mysqli_query($conn, $update_sql)) {
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student | Center Card</title>
    <!-- Bootstrap 5 CSS (সঠিক লিঙ্ক দেওয়া হয়েছে) -->
    <link href="https://jsdelivr.net" rel="stylesheet">
    <style>
        body { 
            background-color: #f8f9fa; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; /* লম্বালম্বিভাবে মাঝখানে আনবে */
            justify-content: center; /* আড়াআড়িভাবে মাঝখানে আনবে */
        }
        .form-container { 
            max-width: 450px; 
            width: 100%; 
            background: white; 
            padding: 30px; 
            border-radius: 12px; 
            border: 1px solid #dee2e6; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .edit-header { border-bottom: 1px solid #eee; margin-bottom: 25px; padding-bottom: 15px; }
        .form-label { font-weight: 600; color: #495057; font-size: 14px; }
        .form-control { border-radius: 6px; padding: 10px; border: 1px solid #ced4da; }
        .btn-update { 
            background-color: #0d6efd; 
            color: white; 
            border: none; 
            padding: 12px; 
            border-radius: 6px; 
            width: 100%; 
            font-weight: bold; 
            margin-top: 10px; 
            transition: 0.3s;
        }
        .btn-update:hover { background-color: #0b5ed7; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #6c757d; text-decoration: none; font-size: 14px; }
        .back-link:hover { text-decoration: underline; color: #212529; }
    </style>
</head>
<body>

<div class="form-container">
    <div class="edit-header text-center">
        <h4 class="mb-1 fw-bold">Edit Student Info</h4>
        <p class="text-muted small mb-0">ID: #<?php echo $row['id']; ?></p>
    </div>

    <form method="POST">
        <!-- Name -->
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo $row['name']; ?>" required>
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" value="<?php echo $row['email']; ?>" required>
        </div>

        <!-- Contact -->
        <div class="mb-3">
            <label class="form-label">Contact Number</label>
            <input type="text" name="contact" class="form-control" value="<?php echo $row['contact']; ?>" required>
        </div>

        <!-- Address -->
        <div class="mb-4">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="3" required><?php echo $row['address']; ?></textarea>
        </div>

        <!-- Buttons -->
        <button type="submit" name="update" class="btn btn-update shadow-sm">Save Changes</button>
        <a href="index.php" class="back-link">Cancel and return</a>
    </form>
</div>

</body>
</html>
