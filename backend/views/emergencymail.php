<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://localhost/");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");
    http_response_code(200);
    exit();
}

header("Access-Control-Allow-Origin: http://localhost/");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die(json_encode(["success" => false, "message" => "Invalid request method"]));
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$sender = $_ENV["SENDER_MAIL"];
$sender_pass = $_ENV["ltyw gged ntrv jemm"];
$doctor_email = $_ENV["DOCTOR_EMAIL"];


if (!$sender || !$sender_pass || !$doctor_email) {
    die(json_encode(["success" => false, "message" => "Environment variables for email not set."]));
}

$mail = new PHPMailer(true);

// patient details
$patient_name = filter_var($_POST["patient_name"], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$patient_id = filter_var($_POST["patient_id"], FILTER_SANITIZE_NUMBER_INT);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $sender;
    $mail->Password   = $sender_pass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom($sender, 'SwiftHealth');
    $mail->addAddress($doctor_email);
    $mail->isHTML(true);

    $mail->Subject = 'Emergency Alert - Immediate Attention Needed';
    
    $message = "
        <h3>Emergency Alert</h3>
        <p><strong>Patient Name:</strong> $patient_name</p>
        <p><strong>Patient ID:</strong> $patient_id</p>
        <p>The patient has triggered an emergency alert. Immediate medical attention is required.</p>
        <p>Please take necessary action.</p>
    ";
    
    $mail->Body = $message;
    
    $mail->send();
    echo json_encode(["success" => true, "message" => "Emergency Email Sent Successfully"]);

}catch (Exception $e){
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>