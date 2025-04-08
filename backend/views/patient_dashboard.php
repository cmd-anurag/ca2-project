<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// this function is there to reduce boilerplate of htmlspecialchars() and improve readibility
function e($string)
{
    return htmlspecialchars($string);
}

// function to render html for an appointment.
function renderAppointment($appointment)
{
    $statusColors = [
        'approved' => 'text-green-500 border-green-500 bg-green-50',
        'pending' => 'text-yellow-500 border-yellow-500 bg-yellow-50',
        'rejected' => 'text-red-500 border-red-500 bg-red-50',
        'completed' => 'text-blue-500 border-blue-500 bg-blue-50',
        'cancelled' => 'text-gray-500 border-gray-500 bg-gray-50'
    ];

    $statusIcons = [
        'approved' => '<i class="fas fa-check-circle mr-1"></i>',
        'pending' => '<i class="fas fa-clock mr-1"></i>',
        'rejected' => '<i class="fas fa-times-circle mr-1"></i>',
        'completed' => '<i class="fas fa-check-double mr-1"></i>',
        'cancelled' => '<i class="fas fa-ban mr-1"></i>'
    ];

    $status = e($appointment['status']);
    $doctorName = e($appointment['doctor_name']);
    $specialization = e($appointment['specialization']);
    $hospital = e($appointment['hospital_name']);
    $time = date("F j, Y - g:i A", strtotime($appointment['appointment_time']));
    $remarks = !empty($appointment['remarks']) ? e($appointment['remarks']) : "No remarks provided.";

    $borderClass = $statusColors[$status] ?? 'border-gray-500 bg-gray-50';
    $statusIcon = $statusIcons[$status] ?? '';

    return <<<HTML
    <div class="bg-white shadow-lg rounded-lg p-5 border-l-4 $borderClass hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex justify-between items-center mb-3">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-user-md text-blue-500 mr-2"></i>
                <span class="text-blue-600">$doctorName</span>
            </h3>
            <p class="text-md px-3 py-1 rounded-full $borderClass flex items-center">
                $statusIcon $status
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-3">
            <p class="text-gray-700 text-md flex items-center">
                <i class="fas fa-hospital mr-2 text-blue-400"></i>
                <strong>Hospital:</strong> 
                <span class="ml-1">$hospital</span>
            </p>

            <p class="text-gray-700 text-md flex items-center">
                <i class="fas fa-calendar-alt mr-2 text-blue-400"></i>
                <strong>Time:</strong> 
                <span class="ml-1">$time</span>
            </p>
        </div>

        <p class="text-gray-700 text-md flex items-center mb-3">
            <i class="fas fa-stethoscope mr-2 text-blue-400"></i>
            <strong>Specialization:</strong> 
            <span class="font-medium ml-1 bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm">$specialization</span>
        </p>

        <div class="mt-3 text-gray-600 text-md bg-gray-50 p-3 rounded-lg border-l-2 border-blue-300">
            <p class="flex items-start">
                <i class="fas fa-comment-medical mt-1 mr-2 text-blue-400"></i>
                <span>
                    <strong>Remarks:</strong> 
                    <span class="italic">$remarks</span>
                </span>
            </p>
        </div>
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

<!-- main container  -->
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50 p-4 md:p-6 rounded-sm">
    <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-sm p-5 md:p-8">
        <!-- Dashboard header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 pb-4 border-b border-gray-200">
            <div class="mb-4 md:mb-0">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-heartbeat text-red-500 mr-3"></i>
                    <span>Patient Dashboard</span>
                </h1>
                <p class="text-gray-600 mt-1">Welcome back to SwiftHealth</p>
            </div>
            
            <div class="flex items-center space-x-3">
                <div class="hidden md:block text-right">
                    <p class="text-gray-600"><?= date("F j, Y") ?></p>
                    <p class="text-blue-600 font-medium">Manage your health efficiently</p>
                </div>
                <div class="h-10 w-10 bg-blue-500 rounded-full flex items-center justify-center text-white">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>

        <!-- Quick stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white shadow-md">
                <div class="flex items-center">
                    <div class="rounded-full bg-white/20 p-3 mr-3">
                        <i class="fas fa-calendar-check text-xl"></i>
                    </div>
                    <div>
                        <p class="text-white/80 text-sm">Upcoming</p>
                        <h3 class="text-2xl font-bold"><?= count($appointments) ?> Appointments</h3>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white shadow-md">
                <div class="flex items-center">
                    <div class="rounded-full bg-white/20 p-3 mr-3">
                        <i class="fas fa-history text-xl"></i>
                    </div>
                    <div>
                        <p class="text-white/80 text-sm">Past</p>
                        <h3 class="text-2xl font-bold"><?= count($past_appointments) ?> Appointments</h3>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white shadow-md">
                <div class="flex items-center">
                    <div class="rounded-full bg-white/20 p-3 mr-3">
                        <i class="fas fa-heartbeat text-xl"></i>
                    </div>
                    <div>
                        <p class="text-white/80 text-sm">Health Status</p>
                        <h3 class="text-2xl font-bold">Good</h3>
                    </div>
                </div>
            </div>
        </div>

        <section class="mb-12 bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-6 border-b-2 border-blue-400 pb-3">
                <h2 class="lg:text-2xl text-xl font-bold flex items-center text-gray-800">
                    <i class="fas fa-calendar-day text-blue-500 mr-2"></i>
                    Upcoming Appointments
                </h2>
                <a href="bookappointment.php" class="bg-blue-600 text-white hover:bg-blue-700 transform hover:scale-105 duration-300 cursor-pointer py-2 px-4 rounded-lg font-bold text-sm lg:text-[16px] flex items-center shadow-md">
                    <i class="fas fa-plus-circle mr-2"></i> Book Appointment
                </a>
            </div>
            <ul class="space-y-4">
                <!-- Appointments -->
                <?php
                if(empty($appointments)) {
                    echo '<div class="text-center py-8 bg-blue-50 rounded-lg">
                        <i class="fas fa-calendar-times text-blue-300 text-4xl mb-4"></i>
                        <h3 class="lg:text-lg text-md font-medium text-gray-600">No upcoming appointments</h3>
                        <p class="text-gray-500 mt-1">Schedule your next appointment to see it here</p>
                    </div>';
                }
                else {
                    foreach ($appointments as $appointment) {
                        echo renderAppointment($appointment);
                    }
                }
                ?>
            </ul>
        </section>

        <!-- past appointments section -->
        <section class="mb-12 bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-6 border-b-2 border-gray-300 pb-3">
                <h2 class="lg:text-2xl text-xl font-bold flex items-center text-gray-800">
                    <i class="fas fa-history text-gray-500 mr-2"></i>
                    Past / Completed Appointments
                </h2>
                <button class="text-blue-600 hover:text-blue-800 hover:underline text-sm flex items-center">
                    <span>View All History</span>
                    <i class="fas fa-chevron-right ml-1"></i>
                </button>
            </div>
            <ul class="space-y-4">
                <?php
                if(empty($past_appointments)) {
                    echo '<div class="text-center py-8 bg-gray-50 rounded-lg">
                        <i class="fas fa-folder-open text-gray-300 text-4xl mb-4"></i>
                        <h3 class="lg:text-lg text-md font-medium text-gray-600">No past appointments</h3>
                        <p class="text-gray-500 mt-1">Your appointment history will appear here</p>
                    </div>';
                }
                else {
                    foreach ($past_appointments as $appointment) {
                        echo renderAppointment($appointment);
                    }
                }
                ?>
            </ul>
        </section>

        <!-- Emergency Button & Health Tips -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="col-span-1">
                <div class="bg-white shadow-sm rounded-xl p-6 border border-gray-100">
                    <h3 class="text-xl font-bold mb-4 text-gray-800 flex items-center">
                        <i class="fas fa-ambulance text-red-500 mr-2"></i> Emergency Access
                    </h3>
                    <p class="text-gray-600 mb-4">Need immediate medical attention? Click the emergency button below.</p>
                    <button id="emergency-btn" class="w-full bg-gradient-to-r from-red-600 to-red-700 text-white py-4 rounded-lg font-bold hover:from-red-700 hover:to-red-800 transition-all duration-300 cursor-pointer shadow-md flex items-center justify-center">
                        <i class="fas fa-exclamation-circle mr-2 text-xl"></i>
                        Emergency
                    </button>
                    <!-- Added alert status message container -->
                    <div id="emergency-status" class="mt-3 text-center hidden"></div>
                </div>
            </div>
            
            <div class="col-span-2">
                <div class="bg-white shadow-sm rounded-xl p-6 border border-gray-100">
                    <h3 class="text-xl font-bold mb-4 text-gray-800 flex items-center">
                        <i class="fas fa-notes-medical text-blue-500 mr-2"></i> Health Tips
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <div class="bg-blue-100 text-blue-700 p-2 rounded-full mr-3">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div>
                                <h4 class="font-medium">Regular Check-ups</h4>
                                <p class="text-gray-600 text-sm">Schedule a check-up at least once a year to maintain good health.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-green-100 text-green-700 p-2 rounded-full mr-3">
                                <i class="fas fa-apple-alt"></i>
                            </div>
                            <div>
                                <h4 class="font-medium">Balanced Diet</h4>
                                <p class="text-gray-600 text-sm">Maintain a balanced diet rich in fruits, vegetables, and whole grains.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-purple-100 text-purple-700 p-2 rounded-full mr-3">
                                <i class="fas fa-running"></i>
                            </div>
                            <div>
                                <h4 class="font-medium">Physical Activity</h4>
                                <p class="text-gray-600 text-sm">Aim for at least 30 minutes of moderate exercise daily.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Emergency confirmation modal -->
<div id="emergency-modal" class="fixed inset-0 backdrop-blur-sm bg-opacity-50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-xl shadow-2xl p-6 max-w-md w-full mx-4 transform transition-all">
        <div class="text-center mb-4">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 text-red-600 mb-4">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Emergency Alert Confirmation</h3>
        </div>
        
        <p class="text-gray-700 mb-6 text-center">
            Are you sure you want to send an emergency alert? This will immediately notify your emergency contact.
        </p>
        
        <div class="flex justify-center space-x-3">
            <button id="cancel-emergency" class="px-5 py-3 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                Cancel
            </button>
            <button id="confirm-emergency" class="px-5 py-3 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors flex items-center">
                <i class="fas fa-bell mr-2"></i>
                Send Alert
            </button>
        </div>
    </div>
</div>

<!-- Emergency status modal -->
<div id="emergency-status-modal" class="fixed inset-0 backdrop-blur-sm bg-opacity-50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-xl shadow-2xl p-6 max-w-md w-full mx-4">
        <div id="status-content" class="text-center">
            <!-- Status content will be inserted here by JavaScript -->
        </div>
        <div class="mt-6 text-center">
            <button id="close-status" class="px-5 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Chat Button -->
<button id="chat-toggle-btn" class="fixed bottom-8 right-8 bg-gradient-to-r from-blue-600 to-blue-700 text-white w-16 h-16 rounded-full shadow-lg hover:shadow-xl transform hover:scale-105 focus:outline-none cursor-pointer flex items-center justify-center z-50 transition-all duration-300">
    <i class="fa-regular fa-message text-2xl"></i>
</button>

<!-- Chat Popup -->
<div id="chat-popup" class="fixed bottom-8 right-8 bg-white border border-gray-200 rounded-xl shadow-2xl w-80 md:w-96 h-[650px] hidden flex flex-col overflow-hidden transition-all duration-300 ease-in-out z-40">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-4 flex justify-between items-center">
        <span class="font-semibold flex items-center">
            <span class="flex items-center justify-center bg-white text-blue-600 rounded-full h-8 w-8 mr-3 shadow-inner">
                <i class="fas fa-robot"></i>
            </span>
            SwiftHealth Assistant
        </span>
        <button id="chat-close-btn" class="text-white hover:text-gray-200 focus:outline-none cursor-pointer bg-white/10 rounded-full w-8 h-8 flex items-center justify-center hover:bg-white/20 transition-colors">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <!-- Chat Messages -->
    <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">
        <!-- Welcome message from AI -->
        <div class="flex items-start mb-4">
            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-2 flex-shrink-0 shadow-sm">
                <i class="fas fa-robot text-blue-600 text-sm"></i>
            </div>
            <div class="bg-white rounded-lg rounded-tl-none py-3 px-4 max-w-[80%] shadow-sm border border-gray-100">
                <p class="text-sm text-gray-800">Hello! I'm your SwiftHealth Assistant. How can I help you today?</p>
            </div>
        </div>
        <!-- sent messages will be placed here -->
    </div>
    
    <!-- Typing indicator (initially hidden) -->
    <div id="typing-indicator" class="hidden px-4 py-2">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-2 shadow-sm">
                <i class="fas fa-robot text-blue-600 text-sm"></i>
            </div>
            <div class="bg-gray-200 rounded-full py-2 px-4">
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
        <div class="flex items-center bg-gray-100 rounded-full pr-1 shadow-inner">
            <input type="text" id="chat-input-field" placeholder="Type your health question..." 
                class="flex-1 border-none bg-transparent rounded-full py-3 px-4 focus:outline-none text-gray-700">
            <button id="chat-send-btn" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-2 rounded-full hover:from-blue-600 hover:to-blue-700 focus:outline-none flex items-center justify-center shadow">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>



<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
