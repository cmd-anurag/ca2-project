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

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://localhost");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");
    http_response_code(200);
    exit();
}

header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("invalid"); 
}


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$sender = $_ENV["SENDER_MAIL"];
$sender_pass = $_ENV["SENDER_PASSWORD"];


if (!$sender || !$sender_pass) {
    die("Environment variables for email not set.");
}

$mail = new PHPMailer(true);

$email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
$name = filter_var($_POST["name"], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$password = filter_var($_POST["password"], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// hash the password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

$otp = random_int(100000, 999999);
$_SESSION['otp'] = [
    "name" => $name,
    "email" => $email,
    "code" => $otp,
    "password" => $hashed_password
];

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $sender;
    $mail->Password   = $sender_pass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom($sender, 'SwiftHealth');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Your OTP is here.';
    $mail->Body    = "<p>Thank you for registering.<br>Your OTP is: <strong>$otp</strong></p>";

    $mail->send();
    echo json_encode(["success" => true, "message" => "OTP Sent Successfully", "redirect" => "verifyotp.html"]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage(), "redirect" => false]);
    unset($_SESSION['otp']);
}
?>