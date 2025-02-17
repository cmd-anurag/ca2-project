<?php
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

if(!isset($_SESSION['creds'])) {
    echo json_encode(["success" => false, "message" => "OTP was not sent successfully. Please try again later."]);
    die();
}

if($_SESSION['creds']['code'] != $received_otp) {
    echo json_encode(["success" => false, "message" => "Incorrect OTP."]);
    die();
}

include __DIR__ . "/database/connectDB.php";

$name = $_SESSION['creds']['name'];
$email = $_SESSION['creds']['email'];
$password = $_SESSION['creds']['password'];

$query = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', 'patient')";

if(mysqli_query($conn, $query)) {
    
    $user_id = mysqli_insert_id($conn);

    $patient_query = "INSERT INTO patients (id) values ('$user_id')";
 
    if(mysqli_query($conn, $patient_query)) {
        echo json_encode(["success" => true, "message" => "Succesfully registered the user."]);
    }
    else {
        echo json_encode(["success" => false, "message" => "Error creating patient"]);
    }
    
}
else {
    echo json_encode(["success" => false, "message" => "Error creating user."]);
    die();
}
?>