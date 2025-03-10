<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Chicle&family=Sour+Gummy:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>

<body>
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



  session_start();

  // check if the user is not logged in
  if (!isset($_SESSION['user'])) {
    echo "<p class='text-center text-2xl'>You are not logged in. You will be redirected to the login page in a few seconds.</p>";
    echo "<script>
              setTimeout(function() {
                  window.location.href = 'login.html';
              }, 4000);
            </script>";
    exit();
  }

  // connect to DB and fetch user details.
  $userEmail = $_SESSION['user']['email'];
  require __DIR__ . "/../backend/database/connectDB.php";
  $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
  if (!$stmt) {
    echo json_encode([
      "success" => false,
      "message" => "DB Error: " . $conn->error
    ]);
    exit();
  }
  $stmt->bind_param("s", $userEmail);
  if (!$stmt->execute()) {
    echo json_encode([
      "success" => false,
      "message" => "Error fetching details: " . $stmt->error
    ]);
    exit();
  }
  $result = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!isset($result['name'])) {
    echo "<p>Unable to fetch user details.</p>";
    die();
  }
  $user_name = $result['name'];

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
    WHERE u_patient.email = ?;";

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
  ?>


  <!-- Top Bar -->
  <header class="text-black">
    <div class="container mx-auto flex justify-between items-center py-4 px-6">
      <div class="flex items-center">
        <span class="lg:text-4xl text-2xl font-bold font-[Chicle]">SwiftHealth</span>
      </div>
      <nav>
        <ul class="flex space-x-4 hidden lg:flex">
          <li><a href="dashboard.php" class="hover:underline">Dashboard</a></li>
          <li><a href="bookappointment.html" class="hover:underline">Appointments</a></li>
          <li><a href="profile.html" class="hover:underline">Profile</a></li>
        </ul>
      </nav>
      <div class="flex items-center text-sm lg:text-[16px]">
        <i class="fa-solid fa-user"></i><span id="user_name" class="mr-4 ml-2"><?= e($user_name); ?></span>
        <button id="logout-btn" class="bg-transparent text-white-400 px-3 py-1 rounded cursor-pointer border-2 border-red-700 text-red-700">Logout</button>
      </div>
    </div>
  </header>

  <!-- Main Content Area -->
  <main class="container mx-auto px-6 py-8">
    <!-- Upcoming Appointments Section -->
    <section class="mb-8">
      <div class="flex justify-between items-center mb-4 border-b-2 border-blue-400 pb-2">
        <h2 class="lg:text-2xl text-lg font-bold">Upcoming Appointments</h2>
        <a href="bookappointment.html" class="bg-blue-500 text-white hover:bg-blue-800 duration-250 cursor-pointer py-1 px-3 rounded-lg font-bold text-sm lg:text-[16px]">Book Appointment</a>
      </div>
      <ul class="space-y-4">
        <!-- Appointments -->
        <?php
        foreach ($appointments as $appointment) {
          echo renderAppointment($appointment);
        }
        ?>
      </ul>
    </section>

    <!-- Emergency Button -->
    <section class="mb-8">
      <button id="emergency-btn" class="w-full lg:w-1/4 bg-red-600 text-white py-3 rounded font-bold hover:bg-red-700 cursor-pointer">
        Emergency
      </button>
      <button id="chat-toggle-btn" class="fixed bottom-25 right-25 bg-blue-600 text-white w-20 h-20 rounded-full shadow-lg hover:bg-blue-700 focus:outline-none">
        Chat
      </button>
    </section>

    <!-- Chat Popup -->
    <div id="chat-popup" class="fixed bottom-20 right-5 bg-white border border-gray-300 rounded-lg shadow-lg w-80 h-106 hidden flex flex-col">
      <!-- Header -->
      <div class="bg-blue-600 text-white px-4 py-2 rounded-t-lg flex justify-between items-center">
        <span class="font-semibold"><i class="fa-solid fa-robot mr-3"></i>SwiftHealth AI Assistant</span>
        <button id="chat-close-btn" class="text-2xl leading-none focus:outline-none">&times;</button>
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


  </main>

  <!-- Footer -->
  <footer class="bg-gray-100 text-center py-4 mt-8 border-t">
    <p class="text-sm text-gray-600">&copy; 2025 Your Company Name. All rights reserved.</p>
  </footer>


  <script src="./js/dashboard.js"></script>

</body>

</html>