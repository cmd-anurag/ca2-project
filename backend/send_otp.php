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
$_SESSION['creds'] = [
    "name" => $name,
    "email" => $email,
    "code" => $otp,
    "password" => $hashed_password
];

$emailBody = "<html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f7fc;
                    color: #333;
                    padding: 20px;
                }
                .container {
                    background-color: #ffffff;
                    border-radius: 8px;
                    padding: 30px;
                    max-width: 600px;
                    margin: 0 auto;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }
                h2 {
                    color: #3a8dff;
                    font-size: 24px;
                    text-align: center;
                    margin-bottom: 20px;
                }
                p {
                    font-size: 16px;
                    line-height: 1.5;
                    color: #555;
                }
                .otp-code {
                    font-size: 28px;
                    font-weight: bold;
                    color: #4CAF50;
                    text-align: center;
                    margin: 20px 0;
                    padding: 10px;
                    background-color: #e8f5e9;
                    border-radius: 6px;
                    letter-spacing: 2px;
                }
                .footer {
                    text-align: center;
                    font-size: 12px;
                    color: #888;
                    margin-top: 30px;
                }
                .footer a {
                    color: #3a8dff;
                    text-decoration: none;
                }
            </style>
        </head>
        <body>
            <div class=\"container\">
                <h2>Your OTP Code for SwiftHealth</h2>
                <p>Dear User,</p>
                            <p>Thank you for signing up with SwiftHealth. To complete your registration and activate your account, please use the One-Time Password (OTP) below:</p>
                <div class=\"otp-code\">$otp</div>
                <p>This OTP is confidential. Please do not share it with anyone to protect your account.</p>
                <p>If you did not request this, please ignore this message.</p>
                <div class=\"footer\">
                    <p>Thank you for using SwiftHealth!</p>
                    <p>For any queries, <a href=\"mailto:support@swifthealth.com\">contact support</a>.</p>
                </div>
            </div>
        </body>
        </html>";

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
    $mail->Body    = $emailBody;

    $mail->send();
    echo json_encode(["success" => true, "message" => "OTP Sent Successfully", "redirect" => "verifyotp.html"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage(), "redirect" => false]);
    unset($_SESSION['otp']);
}
