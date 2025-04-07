<?php
session_start();
// Check if user is logged in
require __DIR__ . "/../backend/includes/is_loggedin.php";

// Get user email from session
$userEmail = $_SESSION['user']['email'];
$userId = $_SESSION['user']['id'];
$userName = $_SESSION['user']['name'];
$userRole = $_SESSION['user']['role'];

// defaults
$userData = [
    'id' => $userId,
    'name' => $userName,
    'email' => $userEmail,
    'date_of_birth' => '',
    'gender' => '',
    'medical_history' => '',
    'blood_type' => '',
    'phone' => '',
    'address' => '',
    'height' => '',
    'weight' => '',
    'emergency_contact' => ''
];

// Fetch froim get_user.php
require __DIR__ . "/../backend/get_user.php";
$response = getUserData($userEmail);

if ($response["success"] && isset($response["data"]) && is_array($response["data"])) {
    $userData = array_merge($userData, $response["data"]);
}

// Get initials for avatar
$initials = strtoupper(substr($userName, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - SwiftHealth</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chicle&family=Sour+Gummy:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" type="image/png" href="./assets/logo.png">
</head>
<body class="bg-gray-100 min-h-screen">
    
    <header class="border-b border-gray-200 text-gray-800">
        <div class="container mx-auto flex justify-between items-center py-4 px-6">
            <div class="flex items-center">
                <span class="lg:text-3xl text-2xl font-bold font-[Chicle]">SwiftHealth</span>
            </div>
            <div class="flex items-center gap-5 text-sm lg:text-[16px]">
                <a href="dashboard.php" class="text-gray-700 hover:text-blue-600 transition duration-200 flex items-center">
                    <i class="fa-solid fa-gauge-high mr-2"></i> Dashboard
                </a>
                <div class="flex items-center border-l border-r border-gray-200 px-5 py-1">
                    <i class="fa-solid fa-user text-blue-600 mr-2"></i>
                    <span id="user_name" class="font-medium"><?= htmlspecialchars($userName) ?></span>
                </div>
                <button id="logout-btn" class="text-red-600 hover:text-red-800 hover:bg-red-50 px-4 py-2 rounded-md transition duration-200 flex items-center border border-red-200">
                    <i class="fa-solid fa-sign-out-alt mr-2"></i> Logout
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Profile Header -->
        <div class="max-w-4xl mx-auto mb-6 flex items-center">
            <div class="bg-gradient-to-r from-blue-500 to-blue-400 w-16 h-16 rounded-full flex items-center justify-center text-white text-2xl font-bold mr-4 shadow-md">
                <?= $initials ?>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($userName) ?>'s Profile</h1>
                <p class="text-gray-500"><?= htmlspecialchars($userEmail) ?></p>
            </div>
        </div>
        
        <!-- Status Messages -->
        <div id="statusMessage" class="hidden max-w-4xl mx-auto mb-6 p-4 rounded-lg shadow-sm border-l-4"></div>
        
        <!-- Profile Form -->
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md overflow-hidden">
            <form id="profileForm" class="divide-y divide-gray-200">
                <input type="hidden" name="id" value="<?= $userId ?>">
                
                <!-- Personal Information Section -->
                <div class="p-8 space-y-6">
                    <div class="flex items-center mb-4">
                        <i class="fa-solid fa-user-circle text-blue-600 text-xl mr-3"></i>
                        <h2 class="text-xl font-bold text-gray-800">Personal Information</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-user text-gray-400"></i>
                                </div>
                                <input type="text" id="name" value="<?= htmlspecialchars($userData['name']) ?>" 
                                    class="pl-10 block w-full rounded-md border-gray-300 bg-gray-100 focus:ring-blue-500 focus:border-blue-500 shadow-sm py-2 px-4" disabled>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($userData['email']) ?>" 
                                    class="pl-10 block w-full rounded-md border-gray-300 bg-gray-100 focus:ring-blue-500 focus:border-blue-500 shadow-sm py-2 px-4" readonly>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-calendar text-gray-400"></i>
                                </div>
                                <input type="date" id="date_of_birth" name="date_of_birth" value="<?= htmlspecialchars($userData['date_of_birth'] ?? '') ?>" 
                                    class="pl-10 block w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 shadow-sm py-2 px-4" required>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-venus-mars text-gray-400"></i>
                                </div>
                                <select id="gender" name="gender" 
                                    class="pl-10 block w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 shadow-sm py-2 px-4" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?= ($userData['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= ($userData['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                                    <option value="Other" <?= ($userData['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-phone text-gray-400"></i>
                                </div>
                                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($userData['phone'] ?? '') ?>" 
                                    class="pl-10 block w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 shadow-sm py-2 px-4" 
                                    placeholder="10-digit number" required>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-home text-gray-400"></i>
                                </div>
                                <input type="text" id="address" name="address" value="<?= htmlspecialchars($userData['address'] ?? '') ?>" 
                                    class="pl-10 block w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 shadow-sm py-2 px-4" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Medical Information  -->
                <div class="p-8 space-y-6">
                    <div class="flex items-center mb-4">
                        <i class="fa-solid fa-heartbeat text-red-500 text-xl mr-3"></i>
                        <h2 class="text-xl font-bold text-gray-800">Medical Information</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="blood_type" class="block text-sm font-medium text-gray-700">Blood Type</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-droplet text-red-400"></i>
                                </div>
                                <select id="blood_type" name="blood_type" 
                                    class="pl-10 block w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 shadow-sm py-2 px-4" required>
                                    <option value="">Select Blood Type</option>
                                    <option value="A+" <?= ($userData['blood_type'] ?? '') === 'A+' ? 'selected' : '' ?>>A+</option>
                                    <option value="A-" <?= ($userData['blood_type'] ?? '') === 'A-' ? 'selected' : '' ?>>A-</option>
                                    <option value="B+" <?= ($userData['blood_type'] ?? '') === 'B+' ? 'selected' : '' ?>>B+</option>
                                    <option value="B-" <?= ($userData['blood_type'] ?? '') === 'B-' ? 'selected' : '' ?>>B-</option>
                                    <option value="AB+" <?= ($userData['blood_type'] ?? '') === 'AB+' ? 'selected' : '' ?>>AB+</option>
                                    <option value="AB-" <?= ($userData['blood_type'] ?? '') === 'AB-' ? 'selected' : '' ?>>AB-</option>
                                    <option value="O+" <?= ($userData['blood_type'] ?? '') === 'O+' ? 'selected' : '' ?>>O+</option>
                                    <option value="O-" <?= ($userData['blood_type'] ?? '') === 'O-' ? 'selected' : '' ?>>O-</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label for="height" class="block text-sm font-medium text-gray-700">Height (cm)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-ruler-vertical text-gray-400"></i>
                                </div>
                                <input type="number" id="height" name="height" value="<?= htmlspecialchars($userData['height'] ?? '') ?>" 
                                    class="pl-10 block w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 shadow-sm py-2 px-4" required>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label for="weight" class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-weight-scale text-gray-400"></i>
                                </div>
                                <input type="number" id="weight" name="weight" value="<?= htmlspecialchars($userData['weight'] ?? '') ?>" 
                                    class="pl-10 block w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 shadow-sm py-2 px-4" required>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label for="emergency_contact" class="block text-sm font-medium text-gray-700">Emergency Contact</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-phone-volume text-gray-400"></i>
                                </div>
                                <input type="text" id="emergency_contact" name="emergency_contact" value="<?= htmlspecialchars($userData['emergency_contact'] ?? '') ?>" 
                                    class="pl-10 block w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 shadow-sm py-2 px-4" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="medical_history" class="block text-sm font-medium text-gray-700">Medical History</label>
                        <div class="relative">
                            <div class="absolute top-3 left-3 flex items-start pointer-events-none">
                                <i class="fa-solid fa-notes-medical text-gray-400"></i>
                            </div>
                            <textarea id="medical_history" name="medical_history" rows="4" 
                                class="pl-10 block w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 shadow-sm py-2 px-4" required><?= htmlspecialchars($userData['medical_history'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button  -->
                <div class="px-8 py-4 bg-gray-50 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                        <i class="fa-solid fa-save mr-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script src="./js/editProfile.js">
    </script>
</body>
</html>