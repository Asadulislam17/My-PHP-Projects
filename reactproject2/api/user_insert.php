<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
include 'db_config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data)) {
    $username = $data['username'];
    $email    = $data['email'];
    $city     = $data['city'];
    $gender   = $data['gender'];
    $address  = $data['address'];
    $agree    = $data['agree'] ? 1 : 0; 

    $conn->query("INSERT INTO users_form_data (username, email, city, gender, address, agree) 
            VALUES ('$username', '$email', '$city', '$gender', '$address', '$agree')");

    if ($conn->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => "ডেটা সফলভাবে সেভ হয়েছে!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "ভুল হয়েছে: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "কোনো ডেটা পাওয়া যায়নি!"]);
}

$conn->close();
?>
