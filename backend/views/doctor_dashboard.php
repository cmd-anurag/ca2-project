<?php
// this function is there to reduce boilerplate of htmlspecialchars() and improve readibility
function e($string)
{
    return htmlspecialchars($string);
}

function renderAppointment($appointment) {
    $statusColors = [
        'approved' => 'text-green-500 border-green-500',
        'pending' => 'text-yellow-500 border-yellow-500',
        'rejected' => 'text-red-500 border-red-500',
        'completed' => 'text-blue-500 border-blue-500',
        'cancelled' => 'text-gray-500 border-gray-500'
    ];

    $status = e($appointment['status']);
    $patientName = e($appointment['patient_name']);
    
    $time = date("F j, Y - g:i A", strtotime($appointment['appointment_time']));
    $remarks = !empty($appointment['remarks']) ? e($appointment['remarks']) : "No remarks provided.";

    $borderClass = $statusColors[$status] ?? 'border-gray-500';

    if($status == "approved") {
        $completed = "";
    }
    else {
        $completed = "disabled";
    }

    if($status != "pending") {
        $actionbuttons = "disabled";
    }
    else {
        $actionbuttons = "";
    }
    

    

    return <<<HTML
    <div class="bg-white shadow-md rounded-lg p-4 border-l-4 $borderClass">
        <div class="flex justify-between items-center mb-2">
            <h3 class="text-lg font-semibold text-gray-800">
                <span>Patient Name: </span>
                <span class="text-blue-500">$patientName</span>
            </h3>
            <p class="text-md text-gray-600 px-2 py-1 rounded border $borderClass">
                $status
            </p>
        </div>

        <p class="text-gray-700 text-md">
            <strong>Time:</strong> $time
        </p>

        <p class="mt-2 text-gray-600 text-md italic">
            <strong>Remarks:</strong> $remarks
        </p>
        <div class="actions space-x-4 mt-4">
            <button class="px-3 py-2 bg-green-600 text-white rounded-lg cursor-pointer disabled:cursor-not-allowed disabled:opacity-75 disabled:bg-gray-400" $actionbuttons>
                Approve
            </button>
            <button class="px-3 py-2 bg-red-600 text-white rounded-lg cursor-pointer disabled:cursor-not-allowed disabled:opacity-75 disabled:bg-gray-400" $actionbuttons>
                Reject
            </button>
            <button class="px-3 py-2 bg-blue-600 text-white rounded-lg cursor-pointer disabled:cursor-not-allowed disabled:opacity-75 disabled:bg-gray-400 mt-4 lg:mt-0" $completed>
                Mark as Completed
            </button>
        </div>
    </div>
    HTML;
}
// error_reporting(E_ALL);
// ini_set('display_errors', 1);



$app_query = "SELECT a.id, a.remarks, a.appointment_time, a.status, 
        p.name AS patient_name, p.email AS patient_email, 
        u.name AS doctor_name, u.email AS doctor_email
        FROM appointments AS a
        JOIN users AS u ON u.id = a.doctor_id
        JOIN users AS p ON p.id = a.patient_id
        WHERE u.email = ? AND a.appointment_time > NOW() AND a.status != 'completed'
        ORDER BY a.appointment_time ASC
";

$doc_stmt = $conn->prepare($app_query);
if (!$doc_stmt) {
    echo '<h1 class="text-2xl p-10 text-center"> Error Fetching Details. Try again later.</h1>';
    die();
}

$doc_stmt->bind_param('s', $userEmail);
if (!$doc_stmt->execute()) {
    echo '<h1 class="text-2xl p-10 text-center"> Error Fetching Details. Try again later.</h1>';
    die();
}

$appointments = $doc_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$doc_stmt->close();

$past_query = "SELECT a.id, a.remarks, a.appointment_time, a.status, 
        p.name AS patient_name, p.email AS patient_email, 
        u.name AS doctor_name, u.email AS doctor_email
        FROM appointments AS a
        JOIN users AS u ON u.id = a.doctor_id
        JOIN users AS p ON p.id = a.patient_id
        WHERE u.email = ? AND (a.appointment_time <= NOW() OR a.status = 'completed')
        ORDER BY a.appointment_time DESC
        LIMIT 10;";
$doc_stmt2 = $conn->prepare($past_query);
if (!$doc_stmt2) {
    echo '<h1 class="text-2xl p-10 text-center"> Error Fetching Details. Try again later.</h1>';
    die();
}
$doc_stmt2->bind_param('s', $userEmail);

if (!$doc_stmt2->execute()) {
    echo '<h1 class="text-2xl p-10 text-center"> Error Fetching Details. Try again later.</h1>';
    die();
}

$past_appointments = $doc_stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$doc_stmt2->close();

?>

<section class="mb-8 min-h-[40vh]">
    <div class="flex justify-between items-center mb-4 border-b-2 border-blue-400 pb-2">
        <h2 class="lg:text-2xl text-lg font-bold">Upcoming Appointments</h2>
    </div>
    <ul class="space-y-4">
        <!-- Appointments -->
        <?php
        if(empty($appointments)) {
            echo '<h1 class="lg:text-lg text-md"> No appointments to show </h1>';
        }
        else {
            foreach ($appointments as $appointment) {
                echo renderAppointment($appointment);
            }
        }
        ?>
    </ul>
</section>

<!-- past apoointments section -->
<section class="mb-8 mt-12">
    <div class="flex justify-between items-center mb-4 border-b-2 border-gray-400 pb-2">
        <h2 class="lg:text-2xl text-lg font-bold">Past / Completed Appointments</h2>
        <button class="text-blue-500 hover:underline text-sm">View All History</button>
    </div>
    <ul class="space-y-4">
        <?php
        if(empty($past_appointments)) {
            echo '<h1 class="lg:text-lg text-md"> No past appointments to show </h1>';
        }
        else {
            foreach ($past_appointments as $appointment) {
                echo renderAppointment($appointment);
            }
        }
        ?>
    </ul>
</section>

