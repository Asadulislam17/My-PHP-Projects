<?php 
include 'db_config.php'; 
if(isset($_POST['submit'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $sql = "INSERT INTO students (id, name, email, contact, address) VALUES ('$id', '$name', '$email', '$contact', '$address')";
    if(mysqli_query($conn, $sql)) {
        header("Location: index.php");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .form-card { border: none; border-radius: 15px; }
        .gradient-custom { background: linear-gradient(45deg, #4e73df, #224abe); color: white; border-radius: 15px 15px 0 0; }
        .btn-submit { background: #1cc88a; border: none; color: white; border-radius: 10px; padding: 12px; transition: 0.3s; font-weight: bold; }
        .btn-submit:hover { background: #17a673; transform: translateY(-2px); }
        .form-label { font-weight: 600; color: #5a5c69; margin-bottom: 5px; }
        .form-control:focus { border-color: #4e73df; box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25); }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card form-card shadow">
                <!-- Header -->
                <div class="card-header gradient-custom py-3 text-center">
                    <h4 class="mb-0">Add New Student</h4>
                </div>
                
                <div class="card-body p-4">
                    <!-- Form Start -->
                    <form method="post" action="#">
                        
                        <!-- Student ID (Integer) -->
                        <div class="mb-3">
                            <label class="form-label">Student ID</label>
                            <input type="number" name="id" class="form-control" placeholder="e.g. 101" required>
                        </div>

                        <!-- Full Name (Varchar) -->
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter full name" required>
                        </div>

                        <!-- Email (Varchar) -->
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="example@mail.com" required>
                        </div>
                        
                        <!-- Contact No (Varchar) -->
                        <div class="mb-3">
                            <label class="form-label">Contact No</label>
                            <input type="text" name="contact" class="form-control" placeholder="017XXXXXXXX" required>
                        </div>

                        <!-- Address (Text) -->
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3" placeholder="Enter full address" required></textarea>
                        </div>
                        
                        <!-- Buttons -->
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" name="submit" class="btn btn-submit shadow-sm">Save Student Info</button>
                            <a href="index.php" class="btn btn-outline-secondary border-0">Back to List</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://jsdelivr.net"></script>
</body>
</html>
