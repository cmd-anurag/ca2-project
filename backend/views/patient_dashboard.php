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
    WHERE u_patient.email = ? AND a.appointment_time > NOW() AND a.status != 'completed'
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
    LEFT JOIN users as u_doctor on d.id = u_doctor.id
    WHERE u_patient.email = ? AND (a.appointment_time <= NOW() OR a.status = 'completed')
    ORDER BY a.appointment_time DESC LIMIT 10;
";

$past_stmt = $conn->prepare($past_query);

if(!$past_stmt) {
    echo '<h1 class="text-center text-3xl"> Error fetching details, please try again later </h1>';
    exit();
}

$past_stmt->bind_param('s', $userEmail);

if(!$past_stmt->execute()) {
    echo '<h1 class="text-center text-3xl"> Error executing query, please try again later </h1>';
    exit();
}

$past_appointments = $past_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$past_stmt->close();
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

<!-- Emergency Button -->
<section class="mb-8">
        <!-- <button id="emergency-btn" class="w-full lg:w-1/4 bg-red-600 text-white py-3 rounded font-bold hover:bg-red-700 cursor-pointer" onclick="windows.location.href='emergencymail.php'"> -->
        <button id="emergency-btn" class="w-full lg:w-1/4 bg-red-600 text-white py-3 rounded font-bold hover:bg-red-700 cursor-pointer" onclick="handleEmergencyClick()">
            Emergency
        </button>
    <button id="chat-toggle-btn" class="fixed bottom-25 right-25 bg-blue-600 text-white w-20 h-20 rounded-full shadow-2xl hover:bg-blue-700 focus:outline-none cursor-pointer flex items-center justify-center">
        <i class="fa-regular fa-message text-3xl"></i>
    </button>
</section>

<!-- Chat Popup -->
<div id="chat-popup" class="fixed bottom-5 right-5 bg-white border border-gray-200 rounded-xl shadow-2xl w-80 md:w-96 h-[650px] hidden flex flex-col overflow-hidden transition-all duration-300 ease-in-out">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-500 text-white px-4 py-3 flex justify-between items-center">
        <span class="font-semibold flex items-center">
            <span class="flex items-center justify-center bg-white text-blue-600 rounded-full h-8 w-8 mr-3">
                <i class="fa-solid fa-robot"></i>
            </span>
            SwiftHealth Assistant
        </span>
        <button id="chat-close-btn" class="text-white hover:text-gray-200 focus:outline-none cursor-pointer">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    
    <!-- Chat Messages -->
    <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">
        <!-- Welcome message from AI -->
        <div class="flex items-start mb-4">
            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-2 flex-shrink-0">
                <i class="fa-solid fa-robot text-blue-600 text-sm"></i>
            </div>
            <div class="bg-white rounded-lg rounded-tl-none py-2 px-3 max-w-[80%] shadow-sm">
                <p class="text-sm text-gray-800">Hello! I'm your SwiftHealth Assistant. How can I help you today?</p>
            </div>
        </div>
        <!-- sent messages will be placed here -->
        

    </div>
    
    <!-- Typing indicator (initially hidden) -->
    <div id="typing-indicator" class="hidden px-4 py-2">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-2">
                <i class="fa-solid fa-robot text-blue-600 text-sm"></i>
            </div>
            <div class="bg-gray-200 rounded-full py-1 px-4">
                <div class="flex space-x-1">
                    <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Input Area -->
    <div class="px-4 py-3 border-t border-gray-200 bg-white">
        <div class="flex items-center bg-gray-100 rounded-full pr-1">
            <input type="text" id="chat-input-field" placeholder="Type your health question..." 
                class="flex-1 border-none bg-transparent rounded-full py-2 px-4 focus:outline-none text-gray-700">
            <button id="chat-send-btn" class="bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700 focus:outline-none flex items-center justify-center">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>


<script>

    const patientName = <?= json_encode($patientName); ?>;
    const patientId = <?= json_encode($patientId); ?>;

    async function handleEmergencyClick(){

        try{
            const res = await fetch("http://localhost/backend/emergencymail.php", {
                method: "POST",
                headers : {
                    "Content-Type": "application/json"
                },

                credentials: "include",
                body: JSON.stringify({
                    patient_name: patientName,
                    patient_id: patientId

                })
            });

            const result = await res.json();
            const message = result.success ? "Emergency alert sent successfully!" : "Failed to send alert: " +result.message;

            alert(message);
            
        }catch (err){
            console.error("Network or server error:", err);
            alert("Something went wrong. Try again later.");

        }
    }


</script>