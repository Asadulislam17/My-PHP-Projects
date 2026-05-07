<?php 
include('db_config.php'); 
$sql = "SELECT * FROM product_list_view";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product List</title>
</head>
<body>
    <h2>All Products with Manufacturer Info</h2>
    
    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Price</th>
            <th>Manufacturer</th>
        </tr>

        <?php
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['p_name'] . "</td>";
                echo "<td>" . $row['price'] . "</td>";
                echo "<td>" . $row['m_name'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>কোনো তথ্য পাওয়া যায়নি।</td></tr>";
        }
        ?>
    </table>
</body>
</html>
