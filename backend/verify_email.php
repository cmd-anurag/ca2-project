<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Method: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    die("Invalid Request");
}

require './database/connectDB.php';
if (!isset($conn)) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "DB Error"]);
    die();
}

if(!isset($_POST["email"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "No email provided."]);
    die();
}

try {
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        echo json_encode(["success" => false, "message" => "Email already exists"]);
    } else {
        echo json_encode(["success" => true, "message" => "Email is available"]);
    }
}
catch(Exception $error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $error->getMessage()]);
}

