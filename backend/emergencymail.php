<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- CORS Headers ---
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
header("Content-Type: application/json"); 

error_reporting(E_ALL);
ini_set('display_errors', 1); 

require '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$sender = $_ENV["ALERT_SENDER_MAIL"];
$sender_pass = $_ENV["ALERT_SENDER_PASSWORD"];

require "./database/connectDB.php"; 

if (!$sender || !$sender_pass) {
    http_response_code(500); 
    die(json_encode(["success" => false, "message" => "Server configuration error (missing email credentials)."]));
}

// Check if user is logged in
if (!isset($_SESSION['user']['id']) || !isset($_SESSION['user']['name'])) {
     http_response_code(401); 
    die(json_encode(["success" => false, "message" => "User not logged in or session expired."]));
}

$user_id = $_SESSION['user']['id'];
$patient_name = $_SESSION['user']['name'];


$input = json_decode(file_get_contents('php://input'), true);
$latitude = isset($input['latitude']) ? filter_var($input['latitude'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
$longitude = isset($input['longitude']) ? filter_var($input['longitude'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
$location_info = "";

if ($latitude !== null && $longitude !== null) {
    $googleMapsLink = "https://www.google.com/maps?q=" . urlencode($latitude . "," . $longitude);
    $location_info = "<p><strong>Patient's Last Known Location:</strong></p>"
                   . "<p>Latitude: $latitude<br>Longitude: $longitude</p>"
                   . "<p><a href='$googleMapsLink' target='_blank' style='color: #3a8dff; text-decoration: underline;'>View on Google Maps</a></p>";
} else {
    $location_info = "<p><strong>Patient's Location:</strong> Not available.</p>";
}




$stmt = $conn->prepare("SELECT emergency_contact FROM patients WHERE id = ?");
if (!$stmt) {
     http_response_code(500);
     die(json_encode(["success" => false, "message" => "Database prepare statement failed: " . $conn->error]));
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    http_response_code(404); 
    die(json_encode(["success" => false, "message" => "Patient record not found."]));
}

$patient = $result->fetch_assoc();
$emergency_contact = $patient['emergency_contact'];
$stmt->close();



if (!$emergency_contact || !filter_var($emergency_contact, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); 
    die(json_encode(["success" => false, "message" => "Invalid or missing emergency contact email in patient profile."]));
}

$mail = new PHPMailer(true);


$emailBody = "
    <html>
    <head>
        <style>
             body { font-family: Arial, sans-serif; background-color: #f4f7fc; color: #333; padding: 20px; }
            .container { background-color: #ffffff; border-radius: 8px; padding: 30px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
            h2 { color: #d32f2f; font-size: 24px; text-align: center; margin-bottom: 20px; }
            p { font-size: 16px; line-height: 1.5; color: #555; margin: 10px 0; }
            .info { background-color: #fff3e0; border-left: 6px solid #d32f2f; padding: 15px; margin: 20px 0; font-size: 16px; }
            .info strong { color: #d32f2f; }
            .location { background-color: #e3f2fd; border-left: 6px solid #3a8dff; padding: 15px; margin: 20px 0; font-size: 16px; }
            .location strong { color: #3a8dff; }
            .footer { text-align: center; font-size: 12px; color: #888; margin-top: 30px; }
            .footer a { color: #3a8dff; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Emergency Alert</h2>
            <p><strong>Urgent: Immediate Attention Required!</strong></p>

            <div class='info'>
                <p><strong>Patient Name:</strong> $patient_name</p>
                <p>This patient has triggered an emergency alert using the SwiftHealth system. Please respond immediately.</p>
            </div>

            <div class='location'>
                $location_info <!-- Location info inserted here -->
            </div>

            <p>Time is critical, and your prompt response could be life-saving. Please take necessary action right away.</p>

            <div class='footer'>
                <p>This is an automated alert from SwiftHealth.</p>
                <p>For technical issues, <a href='mailto:support@swifthealth.com'>contact support</a>.</p>
            </div>
        </div>
    </body>
    </html>
";


try {
    // --- PHPMailer Setup ---
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
    $mail->Subject = 'Emergency Alert - Immediate Attention Required for ' . $patient_name;
    $mail->Body    = $emailBody;
    // this was optional but i added it anyway
    $mail->AltBody = "Emergency Alert!\n\nPatient Name: $patient_name\nThis patient has triggered an emergency alert. Please respond immediately.\n\n" . strip_tags(str_replace('<br>', "\n", $location_info)) . "\n\nTime is critical.";


    $mail->send();
    echo json_encode(["success" => true, "message" => "Emergency email sent successfully to $emergency_contact."]);

} catch (Exception $e) {
    error_log("Mailer Error: " . $mail->ErrorInfo); 
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to send email. Please try again later or contact support."]);
}
?>
