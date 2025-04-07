<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function getUserData($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ["success" => false, "message" => "Invalid email format"];
    }

    require __DIR__ . "/database/connectDB.php";

    $query = "SELECT u.name, u.phone, u.address, p.date_of_birth, p.gender, p.medical_history, p.height, p.weight, p.blood_type, p.emergency_contact
    FROM users u
    LEFT JOIN patients p ON p.id = u.id
    WHERE u.email = ?";

    $stmt = $conn->prepare($query);

    if(!$stmt) {
        return ["success" => false, "message" => "Internal Server Error: " . $conn->error];
    }
    
    $stmt->bind_param('s', $email);

    if(!$stmt->execute()) {
        return ["success" => false, "message" => "Internal Server Error: " . $stmt->error];
    }

    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return ["success" => true, "data" => $result];
}

// Handle direct API calls via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["email"])) {
    $email = $_POST["email"];
    $result = getUserData($email);
    
    if (!$result["success"]) {
        http_response_code(500);
    }
    
    echo json_encode($result);
}
?>