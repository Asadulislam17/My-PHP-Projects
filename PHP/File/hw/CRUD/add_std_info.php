

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .form-card { border: none; border-radius: 15px; }
        .gradient-custom { background: linear-gradient(45deg, #4e73df, #224abe); color: white; border-radius: 15px 15px 0 0; }
        .btn-submit { background: #1cc88a; border: none; color: white; border-radius: 10px; padding: 10px; transition: 0.3s; }
        .btn-submit:hover { background: #17a673; }
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
                    <h4 class="mb-0">Add New Student</h4>
                </div>
                <div class="card-body p-4">
                    
                    <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

                    <form method="post" action="save.php">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3" placeholder="Enter address" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Contact No</label>
                            <input type="text" name="contact" class="form-control" placeholder="017XXXXXXXX" required>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" name="submit" class="btn btn-submit">Save Student Info</button>
                            <a href="view.php" class="btn btn-outline-secondary border-0">Back to List</a>
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
