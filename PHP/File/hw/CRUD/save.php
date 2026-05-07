<?php
include('db_config.php');

if (isset($_POST['submit'])) {
    
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);

   
    $sql = "INSERT INTO students (name, address, contact) VALUES ('$name', '$address', '$contact')";
    
    if (mysqli_query($conn, $sql)) {
        
        header("Location: view.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
   
    header("Location: add_std_info.php");
    exit();
}
?>
