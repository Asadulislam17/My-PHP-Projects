<?php
// ডাটাবেস কানেকশন
$conn = new mysqli("localhost", "root", "", "company");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if (isset($_POST['add_brand'])) {
    $brand_name = $_POST['brand_name'];
    $contact = $_POST['contact'];

    $sql = "INSERT INTO brand (name, Contact) VALUES ('$brand_name', '$contact')";

    if ($conn->query($sql) === TRUE) {
        
        echo "<script>
                alert('Brand added successfully!');
                window.location.href='view_brand.php';
              </script>";
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Brand</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 50px; }
        .form-container { background: #fff; padding: 30px; max-width: 400px; margin: auto; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #0056b3; }
        .links { text-align: center; margin-top: 15px; }
        .links a { text-decoration: none; color: #666; font-size: 14px; margin: 0 5px; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Add New Brand</h2>
    
    <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form action="" method="POST">
        <label>Brand Name</label>
        <input type="text" name="brand_name" placeholder="e.g. Samsung" required>

        <label>Contact Number</label>
        <input type="text" name="contact" placeholder="e.g. 01700000000" required>

        <button type="submit" name="add_brand">Save Brand</button>
    </form>

    <div class="links">
        <a href="view_brand.php">View Brands</a> | 
        <a href="insert_product.php">Add Product</a>
    </div>
</div>

</body>
</html>
