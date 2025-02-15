<?php
session_start();

header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
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