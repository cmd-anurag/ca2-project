<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$email = $_POST["email"];
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid email format"]);
    exit;
}

require __DIR__ . "/database/connectDB.php";

$query = "SELECT u.name, u.phone, u.address, p.date_of_birth, p.gender, p.medical_history 
FROM users u
JOIN patients p ON p.id = u.id
WHERE u.email = ?";

$stmt = $conn->prepare($query);

if(!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Internal Server Error"]);
    die();
}
$stmt->bind_param('s', $email);

if(!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Internal Server Error"]);
    die();
}

$result = $stmt->get_result()->fetch_assoc();

echo json_encode($result);
?>