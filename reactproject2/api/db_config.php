<?php
// ১. ব্রাউজারের সিকিউরিটি লক খোলার কোড (CORS সমাধান) - এটি সবার উপরে থাকবে
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");


$host = "localhost";
$user = "root";
$password = "";
$dbname = "react_crud"; 

$conn = new mysqli($host, $user, $password, $dbname);

?>
