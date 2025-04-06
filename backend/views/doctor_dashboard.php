<?php
// this function is there to reduce boilerplate of htmlspecialchars() and improve readibility
function e($string)
{
    return htmlspecialchars($string);
}

function renderAppointment($appointment) {
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

    $appointmentID = e($appointment['id']);
    $status = e($appointment['status']);
    $patientName = e($appointment['patient_name']);
    $patientEmail = e($appointment['patient_email']);
    
    $time = date("F j, Y - g:i A", strtotime($appointment['appointment_time']));
    $remarks = !empty($appointment['remarks']) ? e($appointment['remarks']) : "No remarks provided.";

    $borderClass = $statusColors[$status] ?? 'border-gray-500 bg-gray-50';
    $statusIcon = $statusIcons[$status] ?? '';

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
    <div class="bg-white shadow-lg rounded-lg p-5 border-l-4 $borderClass hover:shadow-xl transition-all duration-300">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-3">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center mb-2 md:mb-0">
                <i class="fas fa-user text-blue-500 mr-2"></i>
                <span>Patient: </span>
                <span class="text-blue-600 ml-1">$patientName</span>
            </h3>
            <p class="text-md px-3 py-1 rounded-full $borderClass flex items-center">
                $statusIcon $status
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-3">
            <p class="text-gray-700 text-md flex items-center">
                <i class="fas fa-envelope mr-2 text-blue-400"></i>
                <strong>Email:</strong> 
                <span class="ml-1">$patientEmail</span>
            </p>

            <p class="text-gray-700 text-md flex items-center">
                <i class="fas fa-calendar-alt mr-2 text-blue-400"></i>
                <strong>Time:</strong> 
                <span class="ml-1">$time</span>
            </p>
        </div>

        <div class="mt-3 text-gray-600 text-md bg-gray-50 p-3 rounded-lg border-l-2 border-blue-300">
            <p class="flex items-start">
                <i class="fas fa-comment-medical mt-1 mr-2 text-blue-400"></i>
                <span>
                    <strong>Remarks:</strong> 
                    <span class="italic">$remarks</span>
                </span>
            </p>
        </div>

        <div class="actions flex flex-wrap gap-2 mt-4">
            <button class="approve-btn px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg shadow hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 disabled:cursor-not-allowed disabled:opacity-75 disabled:bg-gray-400 disabled:transform-none flex items-center" $actionbuttons data-appointment-id="$appointmentID">
                <i class="fas fa-check-circle mr-2"></i> Approve
            </button>
            <button class="reject-btn px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg shadow hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 disabled:cursor-not-allowed disabled:opacity-75 disabled:bg-gray-400 disabled:transform-none flex items-center" $actionbuttons data-appointment-id="$appointmentID">
                <i class="fas fa-times-circle mr-2"></i> Reject
            </button>
            <button class="complete-btn px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg shadow hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 disabled:cursor-not-allowed disabled:opacity-75 disabled:bg-gray-400 disabled:transform-none flex items-center" $completed data-appointment-id="$appointmentID">
                <i class="fas fa-check-double mr-2"></i> Mark as Completed
            </button>
        </div>
    </div>
    HTML;
}

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

// Count appointments by status
$pending_count = 0;
$approved_count = 0;
$completed_count = 0;

foreach($appointments as $appointment) {
    if($appointment['status'] == 'pending') $pending_count++;
    if($appointment['status'] == 'approved') $approved_count++;
}

foreach($past_appointments as $appointment) {
    if($appointment['status'] == 'completed') $completed_count++;
}

?>

<!-- Main container with gradient background -->
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50 p-4 md:p-6">
    <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-sm p-5 md:p-8">
        <!-- Dashboard header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 pb-4 border-b border-gray-200">
            <div class="mb-4 md:mb-0">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-user-md text-blue-500 mr-3"></i>
                    <span>Doctor Dashboard</span>
                </h1>
                <p class="text-gray-600 mt-1">Welcome back <?= e($user_name) ?> </p>
            </div>
            
            <div class="flex items-center space-x-3">
                <div class="hidden md:block text-right">
                    <p class="text-gray-600"><?= date("F j, Y") ?></p>
                    <p class="text-blue-600 font-medium">Manage your appointments efficiently</p>
                </div>
                <div class="h-10 w-10 bg-blue-500 rounded-full flex items-center justify-center text-white">
                    <i class="fas fa-stethoscope"></i>
                </div>
            </div>
        </div>

        <!-- Quick stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg p-4 text-white shadow-md">
                <div class="flex items-center">
                    <div class="rounded-full bg-white/20 p-3 mr-3">
                        <i class="fas fa-hourglass-half text-xl"></i>
                    </div>
                    <div>
                        <p class="text-white/80 text-sm">Pending</p>
                        <h3 class="text-2xl font-bold"><?= $pending_count ?> Appointments</h3>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white shadow-md">
                <div class="flex items-center">
                    <div class="rounded-full bg-white/20 p-3 mr-3">
                        <i class="fas fa-calendar-check text-xl"></i>
                    </div>
                    <div>
                        <p class="text-white/80 text-sm">Approved</p>
                        <h3 class="text-2xl font-bold"><?= $approved_count ?> Appointments</h3>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white shadow-md">
                <div class="flex items-center">
                    <div class="rounded-full bg-white/20 p-3 mr-3">
                        <i class="fas fa-check-double text-xl"></i>
                    </div>
                    <div>
                        <p class="text-white/80 text-sm">Completed</p>
                        <h3 class="text-2xl font-bold"><?= $completed_count ?> Appointments</h3>
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
            </div>
            <ul class="space-y-4">
                <!-- Appointments -->
                <?php
                if(empty($appointments)) {
                    echo '<div class="text-center py-8 bg-blue-50 rounded-lg">
                        <i class="fas fa-calendar-times text-blue-300 text-4xl mb-4"></i>
                        <h3 class="lg:text-lg text-md font-medium text-gray-600">No upcoming appointments</h3>
                        <p class="text-gray-500 mt-1">You currently have no upcoming appointments</p>
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
    </div>
</div>

<div id="loader-element" class="loader fixed inset-0 flex items-center justify-center z-50 backdrop-blur-sm bg-white/30 flex-col gap-5 hidden">
    <div class="flex flex-col items-center gap-5 p-8 bg-white rounded-2xl shadow-xl">
        <!-- Logo -->
        <div class="text-3xl font-bold text-blue-600 font-[Chicle]">SwiftHealth</div>
        
        <!-- Elegant loading animation -->
        <div class="relative w-24 h-24 flex items-center justify-center">
            
            <!-- Three dots with different animations -->
            <div class="flex space-x-4">
                <div class="w-4 h-4 rounded-full bg-blue-600 animate-pulse"></div>
                <div class="w-4 h-4 rounded-full bg-blue-500 animate-bounce" style="animation-delay: 0.3s"></div>
                <div class="w-4 h-4 rounded-full bg-blue-400 animate-pulse" style="animation-delay: 0.6s"></div>
            </div>
        </div>
        
        <!-- Progress bar with animation -->
        <div class="w-48 bg-gray-100 rounded-full h-1.5 mt-2">
            <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-1.5 rounded-full animate-progress"></div>
        </div>
        
        <p class="text-gray-600 font-medium text-sm">Please Wait...</p>
    </div>
</div>

<style>
    @keyframes progress {
        0% { width: 0%; }
        50% { width: 70%; }
        100% { width: 100%; }
    }
    .animate-progress {
        animation: progress 2s ease-in-out infinite;
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

