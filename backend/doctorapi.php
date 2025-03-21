<?php 

$id = $_POST["id"];
$specialization = $_POST["specialization"];

$experience_years = $_POST["experience_years"];
$hospital_name = $_POST["hospital_name"];

require "../backend/database/connectDB.php";

$query = "UPDATE doctors SET specialization = ?, experience_years = ?, hospital_name = ? WHERE id = $id";

$statement = $conn->prepare($query);

if (!$statement) {
    echo json_encode(["success" => false, "message" => "Error updating profile"]);
    die();
}

$statement->bind_param("sis", $specialization,  $experience_years, $hospital_name);


if ($statement->execute()) {
    echo json_encode(["success" =>true, "message" => "Profile Updated Successfully"]); 
} else {
    echo json_encode(["success" => false, "message" => "Error updating profile"]);
}
$statement->close();

?>