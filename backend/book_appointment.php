<?php
session_start();

// UNCOMMENT this in dev environment
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . "/includes/is_loggedin.php";
$userid = $_SESSION['user']['id'];

if (!isset($_POST["date"]) || !isset($_POST["specialization"]) || !isset($_POST["remarks"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit();
}

$date = $_POST['date'];
$specialization = $_POST['specialization'];
$remarks = $_POST['remarks'];

$day = strtolower(date('l', strtotime($date)));        // e.g., "Monday"
$formatted_date = date("Y-m-d", strtotime($date)); // e.g., "2025-03-19"

// -------------------- TRANSACTION BEGINS -------------------------
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
$pdo->exec("SET session wait_timeout=600");
$pdo->exec("SET session interactive_timeout=600");
$pdo->exec("SET SESSION net_read_timeout=600");
$pdo->beginTransaction();

try {
    // this is a basic doctor finding algorithm on the basis of availabiltiy, it won't be inefficient if the slots per day are less.
    // i'll maybe optimize it later by normalizing the scheduling process and mainting a seperate table for booking times.
    
    // Query1: get all doctors with the given specialization and with available slots on $day
    $query1 = "SELECT id, specialization, available_slots FROM doctors  
           WHERE specialization = ?  
           AND JSON_CONTAINS_PATH(available_slots, 'one', CONCAT('$.', ?))
           ORDER BY id";
    $stmt1 = $pdo->prepare($query1);
    $stmt1->execute([$specialization, $day]);
    $results = $stmt1->fetchAll();
    $stmt1 = null;
    if (count($results) === 0) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "No doctors available on that date"]);
        exit();
    }
    $doctor_found = false;
    $doctor_id = null;
    $free_time = null;
    foreach ($results as $doctor) {
        $available_slots_json = $doctor['available_slots'];
        $available_slots = json_decode($available_slots_json, true);
        if (!isset($available_slots[$day]) || empty($available_slots[$day])) {
            continue;
        }
        $potential_slots = $available_slots[$day];
        $query_appts = "SELECT appointment_time FROM appointments 
                        WHERE doctor_id = ? AND DATE(appointment_time) = ?";
        $stmt_appts = $pdo->prepare($query_appts);
        $stmt_appts->execute([$doctor['id'], $formatted_date]);
        $booked_result = $stmt_appts->fetchAll();
        $stmt_appts = null;
        $booked_slots = [];
        foreach ($booked_result as $row) {
            $booked_slots[] = date("H:i", strtotime($row['appointment_time']));
        }
        $free_slots = array_diff($potential_slots, $booked_slots);
        if (!empty($free_slots)) {
            $doctor_found = true;
            $doctor_id = $doctor['id'];
            $free_slots = array_values($free_slots);
            $free_time = $free_slots[0];
            break;
        }
    }
    if (!$doctor_found) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "No free slots available for any doctor with that specialization on that date"]);
        exit();
    }
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Internal Server Error. Failed to find available doctor: " . $e->getMessage()]);
    exit();
}

try {
    $appointment_time = $formatted_date . ' ' . $free_time;
    $query3 = "INSERT INTO appointments (patient_id, doctor_id, appointment_time, remarks) VALUES (?, ?, ?, ?)";
    $stmt3 = $pdo->prepare($query3);
    if (!$stmt3->execute([$userid, $doctor_id, $appointment_time, $remarks])) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to create appointment."]);
        exit();
    }
    $stmt3 = null;
    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Appointment booked successfully.", "appointment_time" => $appointment_time]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Internal Server Error. Failed to create an appointment: " . $e->getMessage()]);
    exit();
}
// --------------------- TRANSACTION ENDS --------------------------
?>
