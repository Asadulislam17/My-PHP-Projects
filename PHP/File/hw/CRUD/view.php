<?php 
include('db_config.php'); 

if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM students WHERE id=$id");
    header("Location: view.php"); 
    exit();
}

$sql = "SELECT * FROM students";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .custom-card { border: none; border-radius: 15px; overflow: hidden; }
        .gradient-header { background: linear-gradient(45deg, #4e73df, #224abe); color: white; border: none; }
        .table { margin-bottom: 0; }
        .table thead th { border: none; font-weight: 600; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px; }
        .btn-action { border-radius: 8px; padding: 5px 12px; font-weight: 500; transition: 0.3s; }
        .btn-add { background-color: #1cc88a; border: none; border-radius: 10px; color: white; padding: 10px 20px; }
        .btn-add:hover { background-color: #17a673; color: white; }
    </style>
</head>
<body>
<?php include('nav.php'); ?>
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-secondary">Dashboard <small class="text-muted fs-6">/ Student List</small></h2>
        <a href="add_std_info.php" class="btn btn-add shadow-sm">+ Add New Student</a>
    </div>

    <div class="card custom-card shadow-sm">
        <div class="card-header gradient-header py-3">
            <h5 class="mb-0">Current Enrolled Students</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr class="text-secondary">
                            <th class="ps-4"># ID</th>
                            <th>Student Name</th>
                            <th>Address</th>
                            <th>Contact</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td class='ps-4 fw-bold'>#" . $row['id'] . "</td>";
                                echo "<td><div class='fw-bold text-dark'>" . $row['name'] . "</div></td>";
                                echo "<td><span class='badge bg-light text-dark p-2'>" . $row['address'] . "</span></td>";
                                echo "<td>" . $row['contact'] . "</td>"; 
                                echo "<td class='text-center'>
                                        <a href='edit.php?id=" . $row['id'] . "' class='btn btn-outline-primary btn-sm btn-action me-2'>Edit</a>
                                        <a href='?delete_id=" . $row['id'] . "' class='btn btn-outline-danger btn-sm btn-action' onclick=\"return confirm('Are you sure?')\">Delete</a>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center py-5 text-muted'>No student records found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net"></script>
</body>
</html>


