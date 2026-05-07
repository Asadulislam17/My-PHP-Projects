<?php include('db_config.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manufacturer Form</title>
</head>
<body>
    <h2>Add Manufacturer</h2>
    
    <?php
    if (isset($_POST['submit'])) {
        $name = $_POST['name'];
        $address = $_POST['address'];
        $contact = $_POST['contact'];

        $sql="CALL new_Manufacturer('$name','$address','$contact')";
        // $sql = "INSERT INTO Manufacturer (name, address, contact_no) VALUES ('$name', '$address', '$contact')";
        
        if (mysqli_query($conn, $sql)) {
            echo "<p style='color:green;'>সফলভাবে সেভ হয়েছে!</p>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
    ?>

    <form method="post" action="">
        Name: <br>
        <input type="text" name="name" required><br><br>
        
        Address: <br>
        <textarea name="address" required></textarea><br><br>
        
        Contact No: <br>
        <input type="text" name="contact" required><br><br>
        
        <input type="submit" name="submit" value="Submit">
    </form>
</body>
</html>
