<?php

try {
    session_start();
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Bad Request Method"]);
        die();
    }

    if (!isset($_POST["email"]) || !isset($_POST["password"])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Missing credentials"]);
        die();
    }

    require "./database/connectDB.php";

    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
        die();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Database query failed"]);
        die();
    }

    if ($result->num_rows === 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "User does not exist."]);
        die();
    }

    $row = $result->fetch_assoc();
    $entered_password = $_POST["password"];
    $hashed_password = $row["password"];

    if (password_verify($entered_password, $hashed_password)) {
        $_SESSION["user"] = ["email" => $email];
        echo json_encode(["success" => true, "message" => "Login Successful"]);
    } else {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Incorrect Password"]);
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
