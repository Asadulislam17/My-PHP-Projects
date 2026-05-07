<?php include('db_config.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Product Management</title>
    <style>
        /* একদম সিম্পল সিএসএস */
        body { font-family: sans-serif; max-width: 800px; margin: auto; padding: 20px; line-height: 1.5; }
        input, select, textarea { width: 100%; padding: 8px; margin: 5px 0 15px; border: 1px solid #ccc; }
        input[type="submit"] { width: auto; padding: 10px 20px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
    </style>
</head>
<body>

    <h2>Add Manufacturer</h2>
    <?php
    if (isset($_POST['submit'])) {
        $name = $_POST['name']; $address = $_POST['address']; $contact = $_POST['contact'];
        $sql="CALL new_Manufacturer('$name','$address','$contact')";
        if (mysqli_query($conn, $sql)) echo "<p style='color:green;'>সেভ হয়েছে!</p>";
    }
    ?>
    <form method="post">
        Name: <input type="text" name="name" required>
        Address: <textarea name="address" required></textarea>
        Contact: <input type="text" name="contact" required>
        <input type="submit" name="submit" value="Add Manufacturer">
    </form>

    <hr>

    <h2>Add Product</h2>
    <?php
    if (isset($_POST['add_product'])) {
        $p_name = $_POST['p_name']; $price = $_POST['price']; $m_id = $_POST['m_id'];
        $sql = "CALL new_Product('$p_name', '$price', '$m_id')";
        if (mysqli_query($conn, $sql)) echo "<p style='color:green;'>প্রোডাক্ট যুক্ত হয়েছে!</p>";
    }
    ?>
    <form method="post">
        Product Name: <input type="text" name="p_name" required>
        Price: <input type="number" name="price" required>
        Manufacturer: 
        <select name="m_id">
            <?php
            $res = mysqli_query($conn, "SELECT id, name FROM Manufacturer");
            while ($row = mysqli_fetch_assoc($res)) echo "<option value='".$row['id']."'>".$row['name']."</option>";
            ?>
        </select>
        <input type="submit" name="add_product" value="Save Product">
    </form>

    <hr>

    <h2>Delete Triger</h2>
     <?php
        if (isset($_POST['delete'])) {
            $m_id1 = $_POST['m_id1'];

            if ($conn->query("DELETE FROM Manufacturer WHERE id='$m_id1'")) {
                echo "<p style='color:red;'>ম্যানুফ্যাকচারার এবং তার সকল প্রোডাক্ট ডিলিট হয়েছে!</p>";
            } else {
                echo "Error: " . $conn->error;
            }
        }

    ?>
    <form method="post">
        <select name="m_id1">
            <?php
            $res = mysqli_query($conn, "SELECT id, name FROM manufacturer");
            while ($row = mysqli_fetch_assoc($res)) echo "<option value='".$row['id']."'>".$row['name']."</option>";
            ?>
        </select>
        <input type="submit" name="delete" value="Delete" onclick="return confirm('নিশ্চিত তো?')">
    </form>

    <hr>

    <h2>Product List</h2>
    <?php 
        // mysqli_query($conn, "CREATE OR REPLACE VIEW product_list_view AS SELECT p.id, p.name AS p_name, p.price, m.name AS m_name FROM product p LEFT JOIN manufacturer m ON p.manufacturer_id = m.id");
        $sql = "SELECT * FROM product_list_view WHERE price > 5000";
        $result = mysqli_query($conn, $sql);
    ?>
    <table>
        <tr><th>ID</th><th>Product</th><th>Price</th><th>Manufacturer</th></tr>
        <?php
        while($row = mysqli_fetch_assoc($result)) {
            echo "<tr><td>{$row['id']}</td><td>{$row['p_name']}</td><td>{$row['price']}</td><td>{$row['m_name']}</td></tr>";
        }
        ?>
    </table>

</body>
</html>
