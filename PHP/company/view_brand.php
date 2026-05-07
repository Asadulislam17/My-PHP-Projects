<?php
$conn = new mysqli("localhost", "root", "", "company");

// ডিলিট লজিক
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $res = $conn->query("SELECT Product_image FROM products WHERE id=$id");
    $imgData = $res->fetch_assoc();
    if($imgData['Product_image'] && file_exists("uploads/".$imgData['Product_image'])){
        unlink("uploads/".$imgData['Product_image']);
    }
    $conn->query("DELETE FROM products WHERE id=$id");
    header("location: view_product.php"); // এখানে view_product.php হবে
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header-area { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-add { background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #007bff; color: white; }
        .btn-view { color: #17a2b8; text-decoration: none; font-weight: bold; cursor: pointer; }
        .btn-edit { color: #007bff; text-decoration: none; font-weight: bold; }
        .btn-delete { color: #dc3545; text-decoration: none; font-weight: bold; }
        
        /* Modal Style */
        .modal { display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); }
        .modal-content { background-color: #fff; margin: 10% auto; padding: 20px; border-radius: 8px; width: 350px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .close { float: right; font-size: 24px; cursor: pointer; color: #aaa; }
        .close:hover { color: black; }
        #m_img { width: 100%; max-height: 250px; object-fit: contain; margin-bottom: 15px; border-radius: 5px; border: 1px solid #eee; }
        .modal-info { text-align: left; margin-top: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-area">
        <h2>Product List</h2>
        <a href="insert_product.php" class="btn-add">+ Add New Product</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Product Name</th>
                <th>Price</th>
                <th>Brand</th>
                <th style="text-align: center;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT products.*, brand.name as bname, brand.Contact as bcontact FROM products 
                    LEFT JOIN brand ON products.Brand_id = brand.id 
                    ORDER BY products.id DESC";
            $result = $conn->query($sql);
            
            while($row = $result->fetch_assoc()){
                ?>
                <tr>
                    <td><img src="uploads/<?php echo $row['Product_image']; ?>" width="60" height="50"></td>
                    <td><?php echo $row['Name']; ?></td>
                    <td><?php echo number_format($row['Price'], 2); ?> TK</td>
                    <td><?php echo $row['bname'] ? $row['bname'] : 'N/A'; ?></td>
                    <td style="text-align: center;">
                        <!-- View বাটন এখন ইমেজও পাঠাবে -->
                        <span class="btn-view" onclick="showDetails('<?php echo $row['bname']; ?>', '<?php echo $row['bcontact']; ?>', 'uploads/<?php echo $row['Product_image']; ?>')">View</span> | 
                        
                        <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a> | 
                        <a href="view_product.php?delete=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3 style="margin-top: 0;">Product & Brand Info</h3>
        <hr>
        
        <!-- ইমেজ দেখানোর জায়গা -->
        <img id="m_img" src="" alt="Product Image">

        <div class="modal-info">
            <p><strong>Brand Name:</strong> <span id="m_name"></span></p>
            <p><strong>Brand Contact:</strong> <span id="m_contact"></span></p>
        </div>
        <button onclick="closeModal()" style="margin-top: 15px; padding: 8px 20px; cursor: pointer; border-radius: 4px; border: 1px solid #ccc;">Close</button>
    </div>
</div>

<script>
    function showDetails(name, contact, imgPath) {
        document.getElementById('m_name').innerText = name ? name : "No Brand";
        document.getElementById('m_contact').innerText = contact ? contact : "No Contact Info";
        document.getElementById('m_img').src = imgPath; // ইমেজ পাথ সেট করা
        document.getElementById('detailsModal').style.display = "block";
    }

    function closeModal() {
        document.getElementById('detailsModal').style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == document.getElementById('detailsModal')) {
            closeModal();
        }
    }
</script>

</body>
</html>
