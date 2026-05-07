<?php
// ডাটাবেস কানেকশন
$conn = new mysqli("localhost", "root", "", "company");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// URL থেকে আইডি নেওয়া
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $res = $conn->query("SELECT * FROM products WHERE id = $id");
    $product = $res->fetch_assoc();
    
    // যদি আইডি না পাওয়া যায় তবে ব্যাক করবে
    if (!$product) {
        header("Location: view_brand.php");
        exit();
    }
}

// আপডেট করার লজিক
if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $brand_id = $_POST['brand_id'];
    
    // ছবি আপলোড লজিক
    if ($_FILES['image']['name'] != "") {
        $image = time() . '_' . $_FILES['image']['name']; // ইউনিক নাম
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $image);
        
        // আগের ছবি ডিলিট করা (অপশনাল কিন্তু ভালো প্র্যাকটিস)
        if(file_exists("uploads/" . $_POST['old_image'])){
            unlink("uploads/" . $_POST['old_image']);
        }
    } else {
        $image = $_POST['old_image'];
    }

    $sql = "UPDATE products SET Name='$name', Price='$price', Brand_id='$brand_id', Product_image='$image' WHERE id=$id";
    
    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('Product updated successfully!');
                window.location='view_product.php';
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
    <title>Edit Product</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 40px; }
        .form-container { background: #fff; padding: 30px; max-width: 500px; margin: auto; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        label { font-weight: bold; color: #555; }
        input, select { width: 100%; padding: 10px; margin: 10px 0 20px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .current-img { margin-bottom: 10px; border: 1px solid #ddd; padding: 5px; border-radius: 5px; }
        button { width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background-color: #0056b3; }
        .cancel-link { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Product</h2>

    <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <label>Product Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($product['Name']); ?>" required>

        <label>Price (TK)</label>
        <input type="number" name="price" value="<?php echo $product['Price']; ?>" required>

        <label>Select Brand</label>
        <select name="brand_id" required>
            <?php
            $brands = $conn->query("SELECT * FROM brand");
            while ($b = $brands->fetch_assoc()) {
                $selected = ($b['id'] == $product['Brand_id']) ? "selected" : "";
                echo "<option value='{$b['id']}' $selected>{$b['name']}</option>";
            }
            ?>
        </select>

        <label>Current Image</label><br>
        <div class="current-img">
            <img src="uploads/<?php echo $product['Product_image']; ?>" width="100">
        </div>
        <input type="hidden" name="old_image" value="<?php echo $product['Product_image']; ?>">
        
        <label>Change Image (Leave blank to keep current)</label>
        <input type="file" name="image" accept="image/*">

        <button type="submit" name="update">Update Product</button>
        <a href="view_brand.php" class="cancel-link">Cancel / Back</a>
    </form>
</div>

</body>
</html>
