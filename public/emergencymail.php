<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

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
    die(json_encode(["success" => false, "message" => "Invalid request method."]));
}

$sender = $_ENV["SENDER_MAIL"];
$sender_pass = $_ENV["SENDER_PASSWORD"];
$db_host = $_ENV["DB_HOST"];
$db_user = $_ENV["DB_USER"];
$db_pass = $_ENV["DB_PASSWORD"];
$db_name = $_ENV["DB_NAME"];

if (!$sender || !$sender_pass || !$db_host || !$db_user || !$db_name) {
    die(json_encode(["success" => false, "message" => "Missing environment variables."]));
}


$input = json_decode(file_get_contents('php://input'), true);


$patient_name = filter_var($input["patient_name"], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$patient_id = filter_var($input["patient_id"], FILTER_SANITIZE_NUMBER_INT);

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed."]));
}


$stmt = $conn->prepare("SELECT emergency_contact FROM patients WHERE id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$stmt->bind_result($emergency_contact);
$stmt->fetch();
$stmt->close();
$conn->close();



if (!$emergency_contact || !filter_var($emergency_contact, FILTER_VALIDATE_EMAIL)) {
    die(json_encode(["success" => false, "message" => "Invalid or missing emergency contact email."]));
}

$mail = new PHPMailer(true);


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
        <p><strong>Patient ID:</strong> $patient_id</p>
        <p>This patient has triggered an emergency alert. Please respond immediately.</p>
    ";

    $mail->send();
    echo json_encode(["success" => true, "message" => "Emergency email sent successfully."]);

}catch(Exception $e){
    echo json_encode(["success" => false, "message" => "Mailer error: " . $mail->ErrorInfo]);
}


?>