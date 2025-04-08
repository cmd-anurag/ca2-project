<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


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

// if ($_SERVER["REQUEST_METHOD"] !== "POST") {
//     die(json_encode(["success" => false, "message" => "Invalid request method."]));
// }


require '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$sender = $_ENV["ALERT_SENDER_MAIL"];
$sender_pass = $_ENV["ALERT_SENDER_PASSWORD"];


require "../backend/database/connectDB.php";

// echo $sender . $sender_pass;

if (!$sender || !$sender_pass) {
    die(json_encode(["success" => false, "message" => "Missing environment variables."]));
}

// Check if user is logged in
if (!isset($_SESSION['user']['email'])) {
    die(json_encode(["success" => false, "message" => "User not logged in."]));
}

$user_id = $_SESSION['user']['id'];

$input = json_decode(file_get_contents('php://input'), true);


$stmt = $conn->prepare("SELECT emergency_contact FROM patients WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    die(json_encode(["success" => false, "message" => "Patient not found with this email."]));
}

$patient = $result->fetch_assoc();

$patient_name = $_SESSION['user']['name'];
$emergency_contact = $patient['emergency_contact'];

$stmt->close();

if (!$emergency_contact || !filter_var($emergency_contact, FILTER_VALIDATE_EMAIL)) {
    die(json_encode(["success" => false, "message" => "Invalid or missing emergency contact email."]));
}

$mail = new PHPMailer(true);
// $mail->SMTPDebug = 2; // Enable verbose debug output
// $mail->Debugoutput = 'echo'; // Output to browser

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $sender;
    $mail->Password   = $sender_pass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    

    $mail->setFrom($sender, 'SwiftHealth Emergency Alert');
    $mail->addAddress($emergency_contact);
    $mail->isHTML(true);

    $mail->Subject = 'Emergency Alert - Immediate Attention Required';

    $mail->Body = "
        <h2>Emergency Alert</h2>
        <p><strong>Patient Name:</strong> $patient_name</p>
        <p>This patient has triggered an emergency alert. Please respond immediately.</p>
    ";

    $mail->send();
    echo json_encode(["success" => true, "message" => "Emergency email sent successfully."]);

}catch(Exception $e){
    echo json_encode(["success" => false, "message" => "Mailer error: " . $mail->ErrorInfo]);
}


?>
