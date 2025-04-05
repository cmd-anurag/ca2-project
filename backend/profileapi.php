<?php

$id = $_POST["id"];
$dob = $_POST["date_of_birth"];
$gender = $_POST["gender"];
$medical_history = $_POST["medical_history"];
$blood_type = $_POST["blood_type"];
$phone = $_POST["phone"];
$email = $_POST["email"];
$address = $_POST["address"];
$height = $_POST["height"];
$weight = $_POST["weight"];
$emergency_contact = $_POST["emergency_contact"];

require "../backend/database/connectDB.php";

if (empty($id) || empty($dob) || empty($gender) || empty($medical_history) || empty($blood_type) ||
    empty($phone) || empty($email) || empty($address) || empty($height) || empty($weight) || empty($emergency_contact)) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit();
}


if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $dob)) {
    echo json_encode(["success" => false, "message" => "Invalid date of birth format. Please use YYYY-MM-DD."]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid email format."]);
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

if (empty($emergency_contact)) {
    echo json_encode(["success" => false, "message" => "Emergency contact is required."]);
    exit();
}


$query = "UPDATE patients SET date_of_birth = ?, gender = ?, medical_history = ?, blood_type = ?, phone = ?, email = ?, address = ?, height = ?, weight = ?,  emergency_contact = ? where id = $id";

$statement = $conn->prepare($query);

if (!$statement) {
    echo json_encode(["success" => false, "message" => "Error updating profile"]);
    die();
}

$statement->bind_param("ssssssssss", $dob, $gender, $medical_history, $blood_type, $phone, $email,  $address, $height, $weight, $emergency_contact);


if ($statement->execute()) {
    echo json_encode(["success" =>true, "message" => "Profile Updated Successfully"]); 
} else {
    echo json_encode(["success" => false, "message" => "Error updating profile"]);
}
$statement->close();
?>