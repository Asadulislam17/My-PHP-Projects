<?php include('db_config.php'); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Product</title>
</head>
<body>
    <h2>Add Product</h2>

    <?php
    if (isset($_POST['add_product'])) {
        $p_name = $_POST['p_name'];
        $price = $_POST['price'];
        $m_id = $_POST['m_id'];

        $sql = "CALL new_Product('$p_name', '$price', '$m_id')";
        
        if (mysqli_query($conn, $sql)) {
            echo "<p style='color:green;'>প্রোডাক্ট যুক্ত হয়েছে!</p>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
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
        Product Name: <br>
        <input type="text" name="p_name" required><br><br>

        Price: <br>
        <input type="number" name="price" required><br><br>

        Manufacturer: <br>
        <select name="m_id">
            <?php
            
            $res = mysqli_query($conn, "SELECT id, name FROM Manufacturer");
            while ($row = mysqli_fetch_assoc($res)) {
                echo "<option value='".$row['id']."'>".$row['name']."</option>";
            }
            ?>
        </select><br><br>

        <input type="submit" name="add_product" value="Save Product">
    </form>

    <form method="post">
        Manufacturer: <br>
        <select name="m_id1">
            <?php
            
            $res = mysqli_query($conn, "SELECT id, name FROM Manufacturer");
            while ($row = mysqli_fetch_assoc($res)) {
                echo "<option value='".$row['id']."'>".$row['name']."</option>";
            }
            ?>
        </select><br><br>

        <input type="submit" name="delete" value="Delete">
    </form>
</body>
</html>
