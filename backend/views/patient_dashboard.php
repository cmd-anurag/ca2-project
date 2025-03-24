<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// this function is there to reduce boilerplate of htmlspecialchars() and improve readibility
function e($string)
{
    return htmlspecialchars($string);
}

// function to render html for an appointment.
function renderAppointment($appointment)
{
    $statusColors = [
        'approved' => 'text-green-500 border-green-500',
        'pending' => 'text-yellow-500 border-yellow-500',
        'rejected' => 'text-red-500 border-red-500',
        'completed' => 'text-blue-500 border-blue-500',
        'cancelled' => 'text-gray-500 border-gray-500'
    ];

    $status = e($appointment['status']);
    $doctorName = e($appointment['doctor_name']);
    $specialization = e($appointment['specialization']);
    $hospital = e($appointment['hospital_name']);
    $time = date("F j, Y - g:i A", strtotime($appointment['appointment_time']));
    $remarks = !empty($appointment['remarks']) ? e($appointment['remarks']) : "No remarks provided.";

    $borderClass = $statusColors[$status] ?? 'border-gray-500';

    return <<<HTML
    <div class="bg-white shadow-md rounded-lg p-4 border-l-4 $borderClass">
        <div class="flex justify-between items-center mb-2">
            <h3 class="text-lg font-semibold text-gray-800">
                <span class="text-blue-500">$doctorName</span>
            </h3>
            <p class="text-md text-gray-600 px-2 py-1 rounded border $borderClass">
                $status
            </p>
        </div>

        <p class="text-gray-700 text-md">
            <strong>Hospital:</strong> $hospital
        </p>

        <p class="text-gray-700 text-md">
            <strong>Time:</strong> $time
        </p>

        <p class="text-gray-700 text-md">
            <strong>Specialization:</strong> 
            <span class="font-medium">$specialization</span>
        </p>

        <p class="mt-2 text-gray-600 text-md italic">
            <strong>Remarks:</strong> $remarks
        </p>
    </div>
    HTML;
}



// fetch the list of upcoming appointments.
$app_query = "SELECT 
    a.id, 
    a.remarks, 
    a.appointment_time, 
    a.status, 
    d.specialization, 
    d.hospital_name, 
    u_patient.name AS patient_name, 
    u_doctor.name AS doctor_name
    FROM appointments AS a
    JOIN users AS u_patient ON a.patient_id = u_patient.id
    LEFT JOIN doctors AS d ON a.doctor_id = d.id
    LEFT JOIN users AS u_doctor ON d.id = u_doctor.id
    WHERE u_patient.email = ? AND a.apppointment_time > NOW() AND a.status != 'completed'
    ORDER BY a.appointment_time ASC
    ;";

$stmt2 = $conn->prepare($app_query);

if (!$stmt2) {
    echo '<h1 class="text-center text-3xl"> Error fetching details, please try again later </h1>';
    exit();
}
$stmt2->bind_param("s", $userEmail);
if (!$stmt2->execute()) {
    echo '<h1 class="text-center text-3xl"> Error executing query, please try again later </h1>';
    exit();
}

$appointments = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt2->close();

$past_query = "SELECT
    a.id,
    a.remarks,
    a.appointment_time,
    a.status,
    d.specialization,
    d.hospital_name,
    u_doctor.name as doctor_name
    FROM appointments as a
    JOIN users as u_patient on a.patient_id = u_patient.id
    LEFT JOIN doctors as d on a.doctor_id = d.id
    LEFT JOIN users as u_doctor

"


?>

<section class="mb-8">
    <div class="flex justify-between items-center mb-4 border-b-2 border-blue-400 pb-2">
        <h2 class="lg:text-2xl text-lg font-bold">Upcoming Appointments</h2>
        <a href="bookappointment.php" class="bg-blue-500 text-white hover:bg-blue-800 duration-250 cursor-pointer py-1 px-3 rounded-lg font-bold text-sm lg:text-[16px]">Book Appointment</a>
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

<!-- Emergency Button -->
<section class="mb-8">
    <button id="emergency-btn" class="w-full lg:w-1/4 bg-red-600 text-white py-3 rounded font-bold hover:bg-red-700 cursor-pointer">
        Emergency
    </button>
    <button id="chat-toggle-btn" class="fixed bottom-25 right-25 bg-blue-600 text-white w-20 h-20 rounded-full shadow-2xl hover:bg-blue-700 focus:outline-none cursor-pointer flex items-center justify-center">
        <i class="fa-regular fa-message text-3xl"></i>
    </button>
</section>

<!-- Chat Popup -->
<div id="chat-popup" class="fixed bottom-20 right-5 bg-white border border-gray-300 rounded-lg shadow-lg w-80 h-106 hidden flex flex-col">
    <!-- Header -->
    <div class="bg-blue-600 text-white px-4 py-2 rounded-t-lg flex justify-between items-center">
        <span class="font-semibold"><i class="fa-solid fa-robot mr-3"></i>SwiftHealth AI Assistant</span>
        <button id="chat-close-btn" class="text-2xl leading-none focus:outline-none cursor-pointer">&times;</button>
    </div>
    <!-- Chat Messages -->
    <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-2 text-sm text-gray-800">
        <!-- Chat history messages -->
    </div>
    <!-- Input Area -->
    <div class="px-4 py-2 border-t border-gray-200 flex">
        <input type="text" id="chat-input-field" placeholder="Type your message..." class="flex-1 border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
        <button id="chat-send-btn" class="ml-2 bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700 focus:outline-none">Send</button>
    </div>
</div>