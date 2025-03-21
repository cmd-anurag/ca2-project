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

    require __DIR__ . "/database/connectDB.php";

    $name     = $_SESSION['creds']['name'];
    $email    = $_SESSION['creds']['email'];
    $password = $_SESSION['creds']['password'];

    // -------------------------------------------- TRANSACTION BEGINS ---------------------------------------------------   
    $conn->begin_transaction();

    $insertQuery = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);

    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Error preparing user statement: " . $conn->error
        ]);
        $conn->rollback();
        die();
    }

    $stmt->bind_param("sss", $name, $email, $password);

    if (!$stmt->execute()) {
        echo json_encode([
            "success" => false,
            "message" => "Error creating user: " . $stmt->error
        ]);
        $stmt->close();
        $conn->rollback();
        die();
    }

    $user_id = $conn->insert_id;
    $stmt->close();


    $stmt2 = $conn->prepare("INSERT INTO patients (id) VALUES (?)");
    if (!$stmt2) {
        echo json_encode([
            "success" => false,
            "message" => "Error preparing patient statement: " . $conn->error
        ]);
        $conn->rollback();
        die();
    }

    $stmt2->bind_param("i", $user_id);
    if (!$stmt2->execute()) {
        echo json_encode([
            "success" => false,
            "message" => "Error creating patient: " . $stmt2->error
        ]);
        $stmt2->close();
        $conn->rollback();
        die();
    }
    $stmt2->close();


    unset($_SESSION['creds']);
    $_SESSION['user'] = ["email" => $email];
    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Successfully registered the user."
    ]);
    $conn->commit();
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
