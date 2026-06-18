<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include 'db_config.php';

$sql = "SELECT * FROM users_form_data ORDER BY id DESC";
$result = $conn->query($sql);

$rawData = array();

while($row = $result->fetch_assoc()) {
    $rawData[] = $row;
}

echo json_encode($rawData, JSON_UNESCAPED_UNICODE);

exit(); 

$conn->close();
?>
