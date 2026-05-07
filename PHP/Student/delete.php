<?php
include 'db_config.php';
$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM students WHERE id=$id");
header("Location: index.php");
?>
