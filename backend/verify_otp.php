<?php
session_start();

try {

    // error_reporting(E_ALL);
    // ini_set('display_errors', 1);

    header("Access-Control-Allow-Origin: http://localhost");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");


    $received_otp = 0;
    for ($i = 0; $i < 6; $i++) {
        $received_otp = $_POST[$i] * 10 ** (6 - $i) + $received_otp;
    }
    $received_otp /= 10;

    if (!isset($_SESSION['creds'])) {
        echo json_encode([
            "success" => false,
            "message" => "OTP was not sent successfully. Please try again later."
        ]);
        die();
    }

    if ($_SESSION['creds']['code'] != $received_otp) {
        echo json_encode([
            "success" => false,
            "message" => "Incorrect OTP."
        ]);
        die();
    }


    $config = require __DIR__ . '/../config/db_config.php';
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $config['DB_HOST'],
        $config['DB_PORT'],
        $config['DB_NAME']
    );
    $pdo = new PDO($dsn, $config['DB_USERNAME'], $config['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $name     = $_SESSION['creds']['name'];
    $email    = $_SESSION['creds']['email'];
    $password = $_SESSION['creds']['password'];

    // -------------------------------------------- TRANSACTION BEGINS ---------------------------------------------------   
    $pdo->beginTransaction();

    $insertQuery = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($insertQuery);
    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Error preparing user statement."
        ]);
        $pdo->rollBack();
        die();
    }
    if (!$stmt->execute([$name, $email, $password])) {
        echo json_encode([
            "success" => false,
            "message" => "Error creating user."
        ]);
        $pdo->rollBack();
        die();
    }
    $user_id = $pdo->lastInsertId();

    $stmt2 = $pdo->prepare("INSERT INTO patients (id) VALUES (?)");
    if (!$stmt2) {
        echo json_encode([
            "success" => false,
            "message" => "Error preparing patient statement."
        ]);
        $pdo->rollBack();
        die();
    }
    if (!$stmt2->execute([$user_id])) {
        echo json_encode([
            "success" => false,
            "message" => "Error creating patient."
        ]);
        $pdo->rollBack();
        die();
    }

    unset($_SESSION['creds']);
    $_SESSION['user'] = ["email" => $email];
    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Successfully registered the user."
    ]);
    $pdo->commit();
    // -------------------------------------- TRANSACTION ENDS -----------------------------------------------------------

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage(),
        // "file" => $e->getFile(),
        // "line" => $e->getLine(),
        // "trace" => $e->getTraceAsString()
        // debug 
    ]);
}
