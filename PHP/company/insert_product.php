<?php
// ডাটাবেস কানেকশন
$conn = new mysqli("localhost", "root", "", "company");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// প্রোডাক্ট সেভ করার লজিক
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $brand_id = $_POST['brand_id'];
    
    // ইমেজ প্রসেসিং
    $image = time() . '_' . $_FILES['image']['name']; // একই নামে একাধিক ছবি এড়াতে টাইমস্ট্যাম্প যোগ করা ভালো
    $target = "uploads/" . $image;

    $sql = "INSERT INTO products (Name, Price, Brand_id, Product_image) VALUES ('$name', '$price', '$brand_id', '$image')";
    
    if ($conn->query($sql) === TRUE) {
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        
        echo "<script>
                alert('Product saved successfully!');
                window.location.href='view_product.php';
              </script>";
              header("location: view_brand.php");
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
    <title>Add New Product</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 50px; }
        .form-container { background: #fff; padding: 30px; max-width: 500px; margin: auto; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        input, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #218838; }
        .back-link { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #007bff; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Add New Product</h2>
    
    <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <label>Product Name</label>
        <input type="text" name="name" placeholder="Enter product name" required>

        <label>Price</label>
        <input type="number" name="price" placeholder="Enter price" required>

        <label>Select Brand</label>
        <select name="brand_id" required>
            <option value="">-- Choose Brand --</option>
            <?php
            $result = $conn->query("SELECT id, name FROM brand");
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            ?>
        </select>

        <label>Product Image</label>
        <input type="file" name="image" accept="image/*" required>

        <button type="submit" name="submit">Save Product</button>
    </form>
    
    <a href="view_brand.php" class="back-link">← Back to Product List</a>
</div>

</body>
</html>
