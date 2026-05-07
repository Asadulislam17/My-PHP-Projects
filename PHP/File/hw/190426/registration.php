<?php
include('db.php');

if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];

    $conn->query("CALL new_users('$name','$email','$address','$contact')");
    
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management System</title>

    <!-- Google Fonts (Poppins) -->
    <link href="https://googleapis.com" rel="stylesheet">
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
        }
        .card {
            border: none;
            border-radius: 15px;
        }
        .card-header {
            border-radius: 15px 15px 0 0 !important;
            font-weight: 600;
            letter-spacing: 1px;
        }
        .btn-custom {
            border-radius: 8px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .table thead {
            background-color: #4e73df;
            color: white;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #ddd;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25 row rgba(78, 115, 223, 0.25);
            border-color: #4e73df;
        }
        .icon-box {
            margin-right: 10px;
        }
    </style>
</head>

<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <!-- Form Section -->
        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary"><i class="fas fa-user-plus icon-box"></i>Create New User</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold">FULL NAME</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter full name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold">EMAIL ADDRESS</label>
                            <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold">ADDRESS</label>
                            <input type="text" name="address" class="form-control" placeholder="Street, City, Country" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold">CONTACT NUMBER</label>
                            <input type="text" name="contact" class="form-control" placeholder="+880 1XXX XXXXXX" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="submit" class="btn btn-primary btn-custom">
                                <i class="fas fa-paper-plane me-2"></i>Register Now
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark"><i class="fas fa-users icon-box text-primary"></i>Registered User List</h5>
                    <span class="badge bg-soft-primary text-primary">Total Users: <?php echo $conn->query("SELECT id FROM users")->num_rows; ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">#ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                    <th>Contact</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = $conn->query("SELECT * FROM users ORDER BY id DESC");

                                if($result->num_rows > 0){
                                    while($row = $result->fetch_assoc()){
                                        echo "<tr>
                                            <td class='ps-4 fw-bold text-secondary'>#{$row['id']}</td>
                                            <td class='fw-bold'>{$row['name']}</td>
                                            <td class='text-muted'>{$row['email']}</td>
                                            <td>{$row['address']}</td>
                                            <td><span class='badge bg-light text-dark border'>{$row['contact']}</span></td>
                                            <td class='text-center'>
                                                <button class='btn btn-sm btn-outline-info me-1'><i class='fas fa-edit'></i></button>
                                                <button class='btn btn-sm btn-outline-danger'><i class='fas fa-trash'></i></button>
                                            </td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center py-5 text-muted'>
                                            <i class='fas fa-folder-open fa-3x mb-3 d-block'></i>
                                            No user records found.
                                          </td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
