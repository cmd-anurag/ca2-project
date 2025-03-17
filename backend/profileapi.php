<?php
// fetch all values from post super global variable. done
// connect to DB done
// write the query done
// prepare the query
// bind the parameters
// execute the query
// ensure proper error handling
// return the result

$id = $_POST["id"];
$dob = $_POST["date_of_birth"];
$gender = $_POST["gender"];
$medical_history = $_POST["medical_history"];
$blood_type = $_POST["blood_type"];
$emergency_contact = $_POST["emergency_contact"];

require "../backend/database/connectDB.php";

$query = "UPDATE patients SET date_of_birth = ?, gender = ?, medical_history = ?, blood_type = ?, emergency_contact = ? where id = $id";

$statement = $conn->prepare($query);

if (!$statement) {
    echo json_encode(["success" => false, "message" => "Error updating profile"]);
    die();
}


$statement->bind_param("sssss", $dob, $gender, $medical_history, $blood_type, $emergency_contact);

if ($statement->execute()) {
    echo json_encode(["success" =>true, "message" => "Profile Updated Successfully"]); 
} else {
    echo json_encode(["success" => false, "message" => "Error updating profile"]);
}
$statement->close();
?>