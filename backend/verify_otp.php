<?php
session_start();
// $logfile = __DIR__ . "/logs/sessionlog.log";
// try{
//     error_log(print_r($_SESSION), 3, $logfile);
// }
// catch(Exception $e) {
//     echo "$e";
// }

try {
    

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

    include __DIR__ . "/database/connectDB.php";

    $name     = $_SESSION['creds']['name'];
    $email    = $_SESSION['creds']['email'];
    $password = $_SESSION['creds']['password'];


    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'patient')");
    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Error preparing user statement: " . $conn->error
        ]);
        die();
    }

    $stmt->bind_param("sss", $name, $email, $password);
    if (!$stmt->execute()) {
        echo json_encode([
            "success" => false,
            "message" => "Error creating user: " . $stmt->error
        ]);
        $stmt->close();
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
        die();
    }

    $stmt2->bind_param("i", $user_id);
    if (!$stmt2->execute()) {
        echo json_encode([
            "success" => false,
            "message" => "Error creating patient: " . $stmt2->error
        ]);
        $stmt2->close();
        die();
    }
    $stmt2->close();


    unset($_SESSION['creds']);
    $_SESSION['user'] = ["email" => $email];
    echo json_encode([
        "success" => true,
        "message" => "Successfully registered the user."
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Internal Server Error"
    ]);
}
?>