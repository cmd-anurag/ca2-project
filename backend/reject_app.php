<?php 

require "../.backend/database/connectDB.php";
$appointment_id = $_POST['appointment_id'];

$query = "UPDATE appointments SET STATUS ='reject' WHERE id='?'";
$stmt = $conn->prepare($query);

$stmt->bind_param("i", $appointment_id);


if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Error in appointment rejection"]);
    die();
}

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Appointment rejected"]);
} else {
    echo json_encode(["success" => false, "message" => "Error in appointment rejection"]);
}
$stmt->close();

?>