<?php
session_set_cookie_params([
    'lifetime' => 86400,  // 1 day
    'path' => '/',
    'domain' => 'localhost',  // CHANGE this to match your backend domain when deployed
    'secure' => false,  // Set to true if using HTTPS in production
    'httponly' => true,
    'samesite' => 'None'  // Must be 'None' for cross-origin cookies to work
]);

session_start();

header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

error_reporting(E_ALL);
ini_set('display_errors', 1);



$received_otp = 0;

for ($i=0; $i < 6; $i++) { 
    $received_otp = $_POST[$i] * 10**(6-$i) + $received_otp;
}
$received_otp /= 10;

if(!isset($_SESSION['otp'])) {
    echo json_encode(["success" => false, "message" => "OTP was not sent successfully. Please try again later."]);
    die();
}

if($_SESSION['otp']['code'] != $received_otp) {
    echo json_encode(["success" => false, "message" => "Incorrect OTP."]);
    die();
}

?>