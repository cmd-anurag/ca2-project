<?php
session_start();

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require '../backend/includes/is_loggedin.php';
$userEmail = $_SESSION['user']['email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <title>SwiftHealth - Patient Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chicle&family=Poppins:wght@300;400;500;600;700&family=Sour+Gummy:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .logo-font {
            font-family: 'Chicle', cursive;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
    </style>

    <script>
        //  replace the existing fetch code with async/await
        async function fetchUserProfile() {
            const apiEndpoint = "../backend/get_user.php";
            
 
            const userEmail = "<?php echo $userEmail; ?>";
            // console.log( userEmail);
            
            
            const formData = new FormData();
            formData.append('email', userEmail);

            try {
                // Fetch data from the API
                const response = await fetch(apiEndpoint, {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                
                const result = await response.json();
                // console.log("API Response:", result); 
                
                if (!result.success) {
                    throw new Error(result.message || "Failed to fetch user data");
                }
                

                if (!result.data) {
                    console.error("No user data returned from API.");
                    document.getElementById("name").innerHTML = "User Not Found";
                    return;
                }
                

                const data = result.data;
                
                // Populate user information fields
                document.getElementById("name").innerHTML = data.name || "";
                document.getElementById("email").innerHTML = userEmail;
                document.getElementById("phone").innerHTML = data.phone || "";
                document.getElementById("address").innerHTML = data.address || "";
                document.getElementById("address-short").innerHTML = data.address ? data.address.split(',').slice(-2).join(', ') : "";

                document.getElementById("dob").innerHTML = data.date_of_birth || "";
                document.getElementById("gender").innerHTML = data.gender || "";
                document.getElementById("emergencyContact").innerHTML = data.emergency_contact || "";
                document.getElementById("bloodType").innerHTML = data.blood_type || "";
                document.getElementById("height").innerHTML = data.height ? data.height + " cm" : "";
                document.getElementById("weight").innerHTML = data.weight ? data.weight + " kg" : "";
                

                const historyList = document.getElementById("medical-history-list");
                if (historyList) {
                    historyList.innerHTML = ''; // Clear existing content
                    
                    if (data.medical_history && data.medical_history.trim() !== '') {
                        //  a single entry for the plain text medical history
                        const li = document.createElement("li");
                        li.className = "mb-2 pb-2 border-b border-gray-200";
                        li.innerHTML = `
                            <div class="flex justify-between">
                                <span class="font-medium">Medical History</span>
                                <span class="text-sm">Current</span>
                            </div>
                            <span class="text-sm text-gray-700">${data.medical_history}</span>
                        `;
                        historyList.appendChild(li);
                    }
                }

                // Calculate BMI
                let height = parseFloat(data.height) || 165;
                let weight = parseFloat(data.weight) || 65;
                
                let calcBMI = (height, weight) => {
                    let BMI = Math.round(weight / Math.pow(height / 100, 2) * 10) / 10;
                    return BMI;
                }
                
                let BMI = calcBMI(height, weight);
                const bmiElement = document.getElementById("BMI");
                if (bmiElement) {
                    bmiElement.innerHTML = BMI;
                }
                
                // Update BMI category label
                const bmiLabel = document.querySelector(".text-green-800");
                if (bmiLabel) {
                    if (BMI < 18.5) {
                        bmiLabel.textContent = "Underweight";
                        bmiLabel.className = "ml-2 text-sm bg-blue-100 text-blue-800 py-1 px-2 rounded-full";
                    } else if (BMI < 25) {
                        bmiLabel.textContent = "Normal";
                        bmiLabel.className = "ml-2 text-sm bg-green-100 text-green-800 py-1 px-2 rounded-full";
                    } else if (BMI < 30) {
                        bmiLabel.textContent = "Overweight";
                        bmiLabel.className = "ml-2 text-sm bg-yellow-100 text-yellow-800 py-1 px-2 rounded-full";
                    } else {
                        bmiLabel.textContent = "Obese";
                        bmiLabel.className = "ml-2 text-sm bg-orange-100 text-orange-800 py-1 px-2 rounded-full";
                    }
                }
                
                let calcBMIPercentage = (BMI) => {
                    if (BMI < 18.5) return (BMI / 18.5) * 25;
                    else if (BMI < 25) return 25 + ((BMI - 18.5) / 6.5) * 25;
                    else if (BMI < 30) return 50 + ((BMI - 25) / 5) * 25;
                    return 75 + ((BMI - 30) / 10) * 25;
                };
                
                const barElement = document.getElementById("bar");
                if (barElement) {
                    barElement.style.width = `${calcBMIPercentage(BMI)}%`;
                }
                
            } catch (error) {
                console.error("Error fetching user data:", error);
                document.getElementById("name").innerHTML = "Error Loading Profile";
            }
        }

        // Call the function when the page loads
        document.addEventListener('DOMContentLoaded', fetchUserProfile);
    </script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col relative">
        <!-- Navigation Bar Starts -->
    <nav id="navbar" class="flex items-center justify-between px-10 lg:px-16 py-6 w-full bg-white fixed z-10">
        <div id="logo" class="lg:text-4xl text-3xl font-bold text-black cursor-pointer font-[Chicle]">
            <a href="home.html">SwiftHealth</a>
        </div>


        <!-- Desktop Navigation Links -->
        <div class="hidden md:flex lg:flex text-black md:text-[17px] lg:text-[18px] lg:gap-x-7 md:gap-1">
            <div class="hover:text-blue-500 duration-200 cursor-pointer p-1"><a href="home.html">Home</a></div>
            <div class="hover:text-blue-500 duration-200 cursor-pointer p-1"><a href="dashboard.php">My Dashboard</a></div>
            <div class="hover:text-blue-500 duration-200 cursor-pointer p-1"><a href="aboutus.html">About Us</a></div>
            <div class="hover:text-blue-500 duration-200 cursor-pointer p-1"><a href="services.html">Services</a></div>
            <div class="hover:text-blue-500 duration-200 cursor-pointer p-1"><a href="ContactUs.html">Contact Us</a></div>
        </div>

        <div class="hidden md:block">
            <button class="bg-blue-500 text-white hover:bg-black py-3 px-5 rounded-full font-bold duration-300">
                <a href="bookappointment.php">Book Appointment</a>
            </button>
        </div>

        <!-- Mobile Menu Button -->
        <button id="menu-btn" class="md:hidden text-3xl">&#9776;</button>
    </nav>

    <!-- Mobile Menu -->
    <div id="mobile-menu"
        class="fixed inset-0 bg-white text-black flex flex-col items-center justify-center text-xl space-y-6 transform -translate-y-full transition-transform duration-500 ease-in-out">
        <button id="close-btn" class="absolute top-5 right-6 text-3xl">&#10006;</button>
        <a href="home.html" class="hover:text-blue-500">Home</a>
        <a href="hospitals.html" class="hover:text-blue-500">Hospitals</a>
        <a href="aboutus.html" class="hover:text-blue-500">About Us</a>
        <a href="services.html" class="hover:text-blue-500">Services</a>
        <a href="ContactUs.html" class="hover:text-blue-500">Contact Us</a>
        <a href="login.html" class="hover:text-blue-500">Sign Up / Log In</a>
        <div class="bg-blue-500 text-white hover:bg-blue-950 cursor-pointer px-4 py-3 text-sm font-bold rounded-full">
            <a href="bookappointment.html">Book Appointment</a>
        </div>
    </div>

    <!-- Navigation Ends-->

        <!-- Page Header -->
        <div class="bg-blue-50 py-6 px-4 sm:px-6 lg:px-16">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-2xl sm:text-3xl font-bold text-blue-800">My Health Dashboard</h1>
                <p class="text-gray-600 mt-1">Manage your health profile and monitor your medical information</p>
            </div>
        </div>

        <div class="container mx-auto py-6 px-4 sm:px-6 lg:px-16 flex-grow">
            <div class="profile flex flex-col lg:flex-row gap-6">
                <!-- Left Column -->
                <div class="left w-full lg:w-1/3 flex flex-col gap-6">
                    <!-- Profile Card -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden  ">
                        <div class="gradient-bg p-6 relative">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-semibold text-white">Personal Profile</h2>
                                <a href="editProfile.php" id="edit-profile-link" class="text-white bg-white/20 hover:bg-white/30 p-2 rounded-full">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                            <div class="flex items-center mt-4">
                                <div class="h-20 w-20 rounded-full bg-white flex items-center justify-center shadow-lg mr-4">
                                    <img src="./assets/patient.svg" class="h-16 w-16">
                                </div>
                                <div class="text-white">
                                    <h3 class="text-xl font-medium" id="name"></h3>
                                    <p class="text-blue-100 flex items-center">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        <span id="address-short">Bangalore, India</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-4">
                                <div class="flex items-start">
                                    <div class="text-blue-500 mr-3">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 text-sm">Phone Number</p>
                                        <p class="font-medium" id="phone"></p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="text-blue-500 mr-3">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 text-sm">Email Address</p>
                                        <p class="font-medium" id="email"></p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="text-blue-500 mr-3">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 text-sm">Date of Birth</p>
                                        <p class="font-medium" id="dob"></p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="text-blue-500 mr-3">
                                        <i class="fas fa-venus-mars"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 text-sm">Gender</p>
                                        <p class="font-medium" id="gender"></p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="text-blue-500 mr-3">
                                        <i class="fas fa-phone-alt"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 text-sm">Emergency Contact</p>
                                        <p class="font-medium" id="emergencyContact"></p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="text-blue-500 mr-3">
                                        <i class="fas fa-map-marked-alt"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 text-sm">Complete Address</p>
                                        <p class="font-medium" id="address"></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Add Edit Profile button -->
                            <div class="mt-6">
                                <a href="editProfile.php" id="edit-profile-button" class="block w-full text-center bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition-colors">
                                    <i class="fas fa-user-edit mr-2"></i>Edit Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div class="right w-full lg:w-2/3 flex flex-col gap-6">
                    <!-- Health Metrics -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden ">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-xl font-semibold text-gray-800">Health Metrics</h2>
                                <span class="text-gray-500 text-sm">Last updated: 02 Apr 2025</span>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="bg-blue-50 p-4 rounded-xl flex flex-col items-center transition-all duration-300 card-hover">
                                    <div class="h-14 w-14 rounded-full bg-blue-100 flex items-center justify-center mb-3">
                                        <img src="./assets/height.svg" alt="" class="h-8">
                                    </div>
                                    <span class="text-gray-500 text-sm">HEIGHT</span>
                                    <span id="height" class="text-2xl font-medium text-gray-800"></span>
                                </div>
                                
                                <div class="bg-blue-50 p-4 rounded-xl flex flex-col items-center transition-all duration-300 card-hover">
                                    <div class="h-14 w-14 rounded-full bg-blue-100 flex items-center justify-center mb-3">
                                        <img src="./assets/weight.svg" alt="" class="h-8 ">
                                    </div>
                                    <span class="text-gray-500 text-sm">WEIGHT</span>
                                    <span id="weight" class="text-2xl font-medium text-gray-800"></span>
                                </div>
                                
                                <div class="bg-blue-50 p-4 rounded-xl flex flex-col items-center transition-all duration-300 card-hover">
                                    <div class="h-14 w-14 rounded-full bg-blue-100 flex items-center justify-center mb-3">
                                        <img src="./assets/blood_group.svg" alt="" class="h-8">
                                    </div>
                                    <span class="text-gray-500 text-sm">BLOOD GROUP</span>
                                    <span id="bloodType" class="text-2xl font-medium text-gray-800"></span>
                                </div>
                            </div>
                            
                            <div class="mt-6">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <span class="text-gray-800 font-medium">Body Mass Index (BMI)</span>
                                        <span class="ml-2 text-sm bg-green-100 text-green-800 py-1 px-2 rounded-full">Normal</span>
                                    </div>
                                    <span id="BMI" class="font-medium text-gray-800">22.4</span>
                                </div>
                                <div class="h-2 bg-gray-200 rounded-full">
                                    <div class="h-2 rounded-full gradient-bg" style="width: 0" id="bar"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500 mt-1">
                                    <span>Underweight</span>
                                    <span>Normal</span>
                                    <span>Overweight</span>
                                    <span>Obese</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Medical History -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden ">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-xl font-semibold text-gray-800">Medical History</h2>
                            </div>
                            
                            <ul id="medical-history-list" class="mb-4">
                                <!-- Medical history will be populated by JavaScript -->
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-8 px-4 sm:px-6 lg:px-16 mt-8">
            <div class="container mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-6 md:mb-0">
                        <h2 class="text-2xl font-bold mb-2 logo-font text-blue-400">SwiftHealth</h2>
                        <p class="text-gray-400 max-w-md">Your trusted partner in healthcare. We provide expert medical services with a focus on patient comfort and well-being.</p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-8">
                        <div>
                            <h3 class="text-lg font-medium mb-3">Quick Links</h3>
                            <ul class="space-y-2">
                                <li><a href="./home.html" class="text-gray-400 hover:text-white">Home</a></li>
                                <li><a href="./ContactUs.html" class="text-gray-400 hover:text-white">Contact Us</a></li>
                            </ul>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium mb-3">Connect with Us</h3>
                            <div class="flex space-x-4">
                                <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-facebook"></i></a>
                                <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-instagram"></i></a>
                                <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-linkedin"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-gray-700 mt-8 pt-6 text-center text-gray-400 text-sm">
                    <p>&copy; 2025 SwiftHealth. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // Mobile menu toggle functionality
        document.getElementById('menu-btn').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.style.transform = 'translateY(0)';
        });
        
        document.getElementById('close-btn').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.style.transform = 'translateY(-100%)';
        });
    </script>
</body>
</html>