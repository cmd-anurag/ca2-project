<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$id = $_POST["id"];
$dob = $_POST["date_of_birth"];
$gender = $_POST["gender"];
$medical_history = $_POST["medical_history"];
$blood_type = $_POST["blood_type"];
$phone = $_POST["phone"];
$email = $_POST["email"]; // collect but dont update this
$address = $_POST["address"];
$height = $_POST["height"];
$weight = $_POST["weight"];
$emergency_contact = $_POST["emergency_contact"];

require __DIR__ . "/database/connectDB.php";

if (empty($id) || empty($dob) || empty($gender) || empty($medical_history) || empty($blood_type) ||
    empty($phone) || empty($address) || empty($height) || empty($weight) || empty($emergency_contact)) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit();
}

// Validation
if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $dob)) {
    echo json_encode(["success" => false, "message" => "Invalid date of birth format. Please use YYYY-MM-DD."]);
    exit();
}

if (!preg_match("/^\d{10}$/", $phone)) {
    echo json_encode(["success" => false, "message" => "Invalid phone number. Please use a 10-digit number."]);
    exit();
}

if (!is_numeric($height)) {
    echo json_encode(["success" => false, "message" => "Height must be numeric value."]);
    exit();
}

if (!is_numeric($weight)) {
    echo json_encode(["success" => false, "message" => "Weight must be numeric value."]);
    exit();
}


$checkQuery = "SELECT id FROM patients WHERE id = ?";
$checkStmt = $conn->prepare($checkQuery);
if (!$checkStmt) {
    echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    exit();
}

$checkStmt->bind_param("i", $id);
$checkStmt->execute();
$result = $checkStmt->get_result();
$patientExists = $result->num_rows > 0;
$checkStmt->close();


if (!$patientExists) {
    echo json_encode(["success" => false, "message" => "Patient record not found. Please contact support."]);
    exit();
}

// phone and address in users table
$usersQuery = "UPDATE users SET phone = ?, address = ? WHERE id = ?";
$usersStmt = $conn->prepare($usersQuery);

if (!$usersStmt) {
    echo json_encode(["success" => false, "message" => "Error updating user profile: " . $conn->error]);
    exit();
}

$usersStmt->bind_param("ssi", $phone, $address, $id);
$usersSuccess = $usersStmt->execute();
$usersStmt->close();

if (!$usersSuccess) {
    echo json_encode(["success" => false, "message" => "Error updating contact information"]);
    exit();
}

$patientsQuery = "UPDATE patients SET 
                 date_of_birth = ?, 
                 gender = ?, 
                 medical_history = ?, 
                 blood_type = ?, 
                 height = ?, 
                 weight = ?, 
                 emergency_contact = ? 
                 WHERE id = ?";

$patientsStmt = $conn->prepare($patientsQuery);
if (!$patientsStmt) {
    echo json_encode(["success" => false, "message" => "Error updating medical information: " . $conn->error]);
    exit();
}

$patientsStmt->bind_param("sssssssi", $dob, $gender, $medical_history, $blood_type, $height, $weight, $emergency_contact, $id);
$patientsSuccess = $patientsStmt->execute();
$patientsStmt->close();

if ($patientsSuccess) {
    echo json_encode(["success" => true, "message" => "Profile Updated Successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Error updating medical information: " . $conn->error]);
}

?>