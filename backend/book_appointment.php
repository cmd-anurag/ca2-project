<?php
session_start();


// UNCOMMENT this in dev environment
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require __DIR__ . "/includes/is_loggedin.php";
$userid = $_SESSION['user']['id'];

// $userid = 9; for testing purposes only

$date = $_POST["date"];
$day = strtolower(date('l', strtotime($date)));        // e.g., "Monday"
$formatted_date = date("Y-m-d", strtotime($date)); // e.g., "2025-03-19"

$specialization = $_POST["specialization"];
$remarks = $_POST["remarks"];

// -------------------- TRANSACTION BEGINS -------------------------
require __DIR__ . "/database/connectDB.php";
$conn->begin_transaction();

try {
    // this is a basic doctor finding algorithm on the basis of availabiltiy, it won't be inefficient if the slots per day are less.
    // i'll maybe optimize it later by normalizing the scheduling process and mainting a seperate table for booking times.
    
    

    // Query1: get all doctors with the given specialization and with available slots on $day
    $query1 = "SELECT id, specialization, available_slots FROM doctors  
           WHERE specialization = ?  
           AND JSON_CONTAINS_PATH(available_slots, 'one', CONCAT('$.', ?))
           ORDER BY id";
    
    $stmt1 = $conn->prepare($query1);
    $stmt1->bind_param("ss", $specialization, $day);
    $stmt1->execute();
    $results = $stmt1->get_result();
    $stmt1->close();
    
    if ($results->num_rows === 0) {
        $conn->rollback();
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "No doctors available on that date"]);
        exit();
    }
    
    
    $doctor_found = false;
    $doctor_id = null;
    $free_time = null;
    
    // check each doctor until finding one with actual free slots
    while ($doctor = $results->fetch_assoc()) {
        $available_slots_json = $doctor['available_slots'];
        $available_slots = json_decode($available_slots_json, true);
        
        // skip doctors with no slots for the requested day
        if (!isset($available_slots[$day]) || empty($available_slots[$day])) {
            continue;
        }
        
        $potential_slots = $available_slots[$day]; // e.g., ["10:00", "12:00", "14:00"]
        
        // get this doctor's booked appointments for the requested date
        $query_appts = "SELECT appointment_time FROM appointments 
                        WHERE doctor_id = ? AND DATE(appointment_time) = ?";
        $stmt_appts = $conn->prepare($query_appts);
        $stmt_appts->bind_param("is", $doctor['id'], $formatted_date);
        $stmt_appts->execute();
        $booked_result = $stmt_appts->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_appts->close();
        
        // build array of booked times
        $booked_slots = [];
        foreach ($booked_result as $row) {
            $booked_slots[] = date("H:i", strtotime($row['appointment_time']));
        }
        
        // find available slots
        $free_slots = array_diff($potential_slots, $booked_slots);
        
        if (!empty($free_slots)) {
            // found a doctor with available slots
            $doctor_found = true;
            $doctor_id = $doctor['id'];
            $free_slots = array_values($free_slots); // Reindex
            $free_time = $free_slots[0];
            break;
        }
    }
    
    if (!$doctor_found) {
        $conn->rollback();
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "No free slots available for any doctor with that specialization on that date"]);
        exit();
    }
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Internal Server Error. Failed to find available doctor: " . $e->getMessage()]);
    exit();
}



try {
    // date + time.
    $appointment_time = $formatted_date . ' ' . $free_time;
    
    // Query3: insert new appointment with status 'pending'
    $query3 = "INSERT INTO appointments (patient_id, doctor_id, appointment_time, status, remarks) VALUES (?, ?, ?, 'pending', ?)";
    $stmt3 = $conn->prepare($query3);
    $stmt3->bind_param("iiss", $userid, $doctor_id, $appointment_time, $remarks);
    
    if (!$stmt3->execute()) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to create appointment."]);
        exit();
    }


    $stmt3->close();
    $conn->commit();
    echo json_encode(["success" => true, "message" => "Appointment booked successfully.", "appointment_time" => $appointment_time]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Internal Server Error. Failed to create an appointment: " . $e->getMessage()]);
    exit();
}
// --------------------- TRANSACTION ENDS --------------------------
?>
