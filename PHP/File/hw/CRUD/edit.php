<?php 
include('db_config.php'); 


if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $res = mysqli_query($conn, "SELECT * FROM students WHERE id=$id");
    $data = mysqli_fetch_assoc($res);
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];

    $sql = "UPDATE students SET name='$name', address='$address', contact='$contact' WHERE id=$id";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: view.php");
        exit();
    } else {
        $error = "Error updating record: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .form-card { border: none; border-radius: 15px; }
        .gradient-custom { background: linear-gradient(45deg, #4e73df, #224abe); color: white; border-radius: 15px 15px 0 0; }
        .btn-update { background: #4e73df; border: none; color: white; border-radius: 10px; padding: 10px; transition: 0.3s; }
        .btn-update:hover { background: #224abe; }
        .form-label { font-weight: 600; color: #5a5c69; }
    </style>
</head>
<body>
<?php include('nav.php'); ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card form-card shadow">
                <div class="card-header gradient-custom py-3 text-center">
                    <h4 class="mb-0">Edit Student Info</h4>
                </div>
                <div class="card-body p-4">
                    
                    <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

                    <form method="post" action="">
                       
                        <input type="hidden" name="id" value="<?php echo $data['id']; ?>">

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo $data['name']; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3" required><?php echo $data['address']; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Contact No</label>
                            <input type="text" name="contact" class="form-control" value="<?php echo $data['contact']; ?>" required>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" name="update" class="btn btn-update shadow-sm">Update Information</button>
                            <a href="view.php" class="btn btn-outline-secondary border-0">Cancel & Go Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://jsdelivr.net"></script>
</body>
</html>
